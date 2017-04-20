<?php
namespace dostoevskiy\processor\src\storage\adpaters;

use AMQPChannel;
use AMQPConnection;
use AMQPExchange;
use dostoevskiy\processor\src\classes\Worker;
use dostoevskiy\processor\src\interfaces\StorageAdapterInterface;
use dostoevskiy\processor\src\interfaces\StorageInterface;
use yii\base\Object;

class Amqp extends Object implements StorageAdapterInterface, StorageInterface
{
    public $host, $port, $user, $password, $vhost;

    public function push($data) {
        global $producer;
        $producer->publish($data, 'processor');
    }

    public function configurateContext()
    {
        return function() {// Connect to an AMQP broker
            global $producer;
            $cnn = new AMQPConnection();
            $cnn->connect();

// Create a channel
            $ch = new AMQPChannel($cnn);

// Declare a new exchange
            $ex = new AMQPExchange($ch);
            $ex->setName('exchange1');
            $ex->declareExchange();

// Create an event loop
            $loop = Worker::getEventLoop();

// Create a producer that will send any waiting messages every half a second.
            $producer = new \Gos\Component\ReactAMQP\Producer($ex, $loop, 0.5);

// Add a callback that's called every time a message is successfully sent.
            $producer->on('produce', function (array $message) {
                // $message is an array containing keys 'message', 'routingKey', 'flags' and 'attributes'
            });

            $producer->on('error', function (AMQPExchangeException $e) {
                // Handle any exceptions here.
            });

            $i = 0;

            $loop->addPeriodicTimer(1, function () use (&$i, $producer) {
                $i++;
                echo "Sending $i\n";

            });

            $loop->run();
        };
    }

    public function pull()
    {
        // TODO: Implement pull() method.
    }

    public function configurateContextForAdapter()
    {
        // TODO: Implement configurateContextForAdapter() method.
    }
}