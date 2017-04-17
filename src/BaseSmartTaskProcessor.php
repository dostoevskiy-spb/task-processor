<?php

namespace dostoevskiy\processor\src;

use dostoevskiy\processor\src\classes\Listner;
use dostoevskiy\processor\src\interfaces\GateProcessorInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Object;

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
    protected static $storage;

    public function listen()
    {
        self::$listner = Yii::createObject($this->listenOptions);

        $isLive                   = $this->isLive();
        self::$listner->onConnect = $isLive ? function ($connection, $data) use ($isLive) {
            /** @var $connection \Workerman\Connection\ConnectionInterface */
            $connection->send(self::$taskProcessor->process($data));
        } : function ($connection, $data) {
            /** @var $connection \Workerman\Connection\ConnectionInterface */
            $connection->send(self::$storage->push($data));
        };
        self::$listner->run();
    }

    public function process()
    {
        if (!self::$taskProcessor) {
            self::$taskProcessor = Yii::createObject($this->taskProcessorConfig);
        }
        $data = self::$storage->pull();

        return self::$taskProcessor->process($data);
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
            self::$storage = Yii::createObject($this->storageOptions);
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