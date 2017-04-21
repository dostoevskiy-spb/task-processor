<?php
namespace dostoevskiy\processor\src\storage\adapters;

use AMQPChannel;
use dostoevskiy\processor\src\interfaces\StorageAdapterInterface;
use dostoevskiy\processor\src\interfaces\StorageInterface;
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
        $message = new AMQPMessage($data);
        $result  = self::$channel->basic_publish($message, '', 'processor');

        return $result;
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
        self::$channel->basic_qos(0, 1, true);

        return self::$connection->isConnected();
    }

    public function pull()
    {
        self::$channel->basic_publish($message, '', 'processor');
    }

    public function configurateContextForAdapter()
    {
        // TODO: Implement configurateContextForAdapter() method.
    }
}