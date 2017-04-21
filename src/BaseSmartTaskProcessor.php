<?php

namespace dostoevskiy\processor\src;

use dostoevskiy\processor\src\classes\Listner;
use dostoevskiy\processor\src\classes\ProcessManager;
use dostoevskiy\processor\src\classes\Worker;
use dostoevskiy\processor\src\interfaces\GateProcessorInterface;
use dostoevskiy\processor\src\interfaces\StorageInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 *
 * @property array $availableStorageTypes
 * @property array $availableTypes
 */
class BaseSmartTaskProcessor extends Object implements GateProcessorInterface
{
    public $type, $storageType, $storageOptions, $taskProcessorConfig, $listenOptions;

    protected $mode;

    const TYPE_LIVE     = 'live';
    const TYPE_DEFERRED = 'deferred';

    const STORAGE_TYPE_NATS     = 'nats';
    const STORAGE_TYPE_MONGO    = 'mongo';
    const STORAGE_TYPE_SOCKET   = 'socket';
    const STORAGE_TYPE_RABBITMQ = 'rabbit';

    const LISTEN_TYPE_HTTP      = 'http';
    const LISTEN_TYPE_WEBSOCKET = 'websocket';
    const LISTEN_TYPE_TCP       = 'tcp';

    protected $defaultType           = 'live';
    protected $defaultStorageType    = 'socket';
    protected $defaultStorageOptions = ['host' => '127.0.0.1', 'port' => '1488'];
    protected $defaultListenOptions  = [
        'class'            => 'dostoevskiy\processor\src\classes\Listner',
        'host'             => '127.0.0.1',
        'port'             => '1488',
        'count'            => 4,
        'type'             => 'tcp',
        'servicesToReload' => ['db']
    ];

    /** @var  Listner */
    protected static $listner;
    protected static $taskProcessor;
    /** @var  StorageInterface */
    protected static $storage;

    public function listen()
    {
        self::$listner = Yii::createObject($this->listenOptions);

        $isLive = $this->isLive();
        if (!$isLive) {
            self::$listner->onWorkerStart = function () {
                self::$storage->configurateContextForAdapter();
                foreach (Worker::$servicesToReload as $service) {
                    Yii::$app->$service->close();
                    Yii::$app->$service->open();
                }
            };
        }
        self::$listner->onMessage = $isLive ? function ($connection, $data) {
            /** @var $connection \Workerman\Connection\ConnectionInterface */
            self::$taskProcessor->process($data);
            $connection->send("HTTP/1.1 200 OK\r\nConnection: keep-alive\r\nServer: workerman\r\nContent-Length: 5\r\n\r\nhello");
            $connection->close();

            return;
        }
            : function ($connection, $data) {
                /** @var $connection \Workerman\Connection\ConnectionInterface */
                self::$storage->push($data);
                $resp   = json_encode(['status' => 'success']);
                $length = strlen($resp);
                $connection->send("HTTP/1.1 200 OK\r\nConnection: close\r\nContent-Length: $length\r\nContent-Type: application/json\r\n\r\n" . $resp);
            };
        self::$listner->onConnect = function ($connection) {
//            echo "New Connection\n";
        };
        self::$listner->run();
    }


    public function process()
    {
        if (!self::$taskProcessor) {
            self::$taskProcessor = Yii::createObject($this->taskProcessorConfig);
        }
        while(true) {
            $data = self::$storage->pull();

            self::$taskProcessor->process($data);
            usleep(100000);
        }
    }

    public function runProcessManager() {
        $processManager = new ProcessManager();
        $processManager->manage();
    }

    public function init()
    {
        if (empty($this->type)) {
            $this->type = $this->defaultType;
        }
        if (!in_array($this->type, array_keys($this->getAvailableTypes()))) {
            throw new InvalidConfigException('Only "live" or "deferred" types are available. Us it.');
        }
        if (empty($this->taskProcessorConfig)) {
            throw new InvalidConfigException('You must config task processor as simple Yii2 component.');
        }
        if (empty($this->listenOptions)) {
            $this->listenOptions = $this->defaultListenOptions;
        }

        if ($this->isLive()) {
            self::$taskProcessor = Yii::createObject($this->taskProcessorConfig);
        } else {
            if (!empty($this->storageType) && empty($this->storageOptions)) {
                throw new InvalidConfigException('You must config storage options as connection credentials, transport options etc.');
            }
            if (empty($this->storageType)) {
                $this->type           = $this->defaultStorageType;
                $this->storageOptions = $this->defaultStorageOptions;
            }
            if (!in_array($this->storageType, array_keys($this->getAvailableStorageTypes()))) {
                throw new InvalidConfigException('Only "nats", "mongo", "socket" or "rabbit" types are available. Us it.');
            }
            self::$storage = Yii::createObject([
                                                   'class'          => 'dostoevskiy\processor\src\storage\Storage',
                                                   'storageOptions' => $this->storageOptions,
                                                   'type'           => $this->storageType
                                               ]);
        }


    }

    /**
     * @return array
     */
    protected function getAvailableTypes()
    {
        return [
            self::TYPE_DEFERRED => 'Отложенное выполнение',
            self::TYPE_LIVE     => 'В реальном времени'
        ];
    }

    protected function getAvailableStorageTypes()
    {
        return [
            self::STORAGE_TYPE_MONGO    => 'MongoDB',
            self::STORAGE_TYPE_NATS     => 'NATS',
            self::STORAGE_TYPE_SOCKET   => 'Native socket',
            self::STORAGE_TYPE_RABBITMQ => 'RabbitMQ',
        ];
    }

    public function isLive()
    {
        return $this->type == self::TYPE_LIVE;
    }
}