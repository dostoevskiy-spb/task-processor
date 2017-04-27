<?php
namespace dostoevskiy\processor\src\storage\adapters;

use dostoevskiy\processor\src\interfaces\StorageAdapterInterface;
use dostoevskiy\processor\src\interfaces\StorageInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use yii\base\Object;
use yii\helpers\ArrayHelper;

class Amqp extends Object implements StorageAdapterInterface, StorageInterface
{
    public $host, $port, $user, $password, $vhost;
    /** @var  $channel AMQPChannel */
    protected static $connection, $channel, $exchange, $queue;

    protected $defaultContextConfig = [
        'queue'      => 'processor',
        'durable'    => true,
        'persistent' => true
    ];

    protected static $queueDeclared = [];
    protected static $contexts      = [];

    public function push($taskName, $data)
    {
        $options = [];
        $context = ArrayHelper::getValue(self::$contexts, $taskName);
        if ($context['durable']) {
            $options['delivery_mode'] = 2;
        }
        $message = new AMQPMessage(json_encode($data), $options);
        self::$channel->basic_publish($message, '', $context['queue']);

        return true;
    }

    public function configureContext($taskName, $config)
    {
        $config                    = ArrayHelper::merge($this->defaultContextConfig, $config);
        self::$contexts[$taskName] = $config;

        return self::$channel->queue_declare($config['queue'], false, $config['durable'], false, false);
    }

    public function configureConnection()
    {
        self::$connection = new AMQPStreamConnection($this->host,
                                                     $this->port,
                                                     $this->user,
                                                     $this->password,
                                                     $this->vhost
        );
        echo "configurate" . PHP_EOL;
        self::$channel = self::$connection->channel();
        self::$channel->basic_qos(0, 100, false);

        return self::$connection->isConnected();
    }

    public function pull($callback, $taskName)
    {
        $config = self::$contexts[$taskName];
        self::$channel->basic_consume($config['queue'], '', false, false, false, false, function ($msg) use ($callback) {
            /** @var  AMQPMessage $msg */
            $class  = $callback[0];
            $method = $callback[1];
            $result = $class->$method($msg->getBody());
            if ($result) {
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            }
        });

    }

    public function loop($callback, $taskName)
    {
        $this->pull($callback, $taskName);
        while (count(self::$channel->callbacks)) {
            self::$channel->wait();
        }
    }
}