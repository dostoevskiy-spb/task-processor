<?php
namespace dostoevskiy\processor\src\storage\adapters;

use dostoevskiy\processor\src\interfaces\StorageAdapterInterface;
use dostoevskiy\processor\src\interfaces\StorageInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use yii\base\Object;

class Amqp extends Object implements StorageAdapterInterface, StorageInterface
{
    public $host, $port, $user, $password, $vhost;
    /** @var  $channel AMQPChannel */
    protected static $connection, $channel, $exchange, $queue;

    public function push($data)
    {
        $message = new AMQPMessage($data, ['delivery_mode' => 2]);
        self::$channel->basic_publish($message, '', 'processor');

        return true;
    }

    public function configurateContext()
    {
        self::$connection = new AMQPStreamConnection($this->host,
                                                     $this->port,
                                                     $this->user,
                                                     $this->password,
                                                     $this->vhost
        );
        echo "configurate" . PHP_EOL;
        self::$channel = self::$connection->channel();
        self::$channel->queue_declare('processor', false, true, false, false);
        self::$channel->basic_qos(0, 100, false);

        return self::$connection->isConnected();
    }

    public function pull($callback)
    {
        self::$channel->basic_consume('processor', '', false, false, false, false, function($msg) use ($callback) {
            /** @var  AMQPMessage $msg */
            $class = $callback[0];
            $method = $callback[1];
            $result = $class->$method($msg->getBody());
            if($result) {
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            }
        });

    }

    public function loop($callback) {
        while (count(self::$channel->callbacks)) {
            self::$channel->wait();
        }
    }
}