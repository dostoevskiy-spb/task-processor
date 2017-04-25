<?php
namespace dostoevskiy\processor\src\storage;

use dostoevskiy\processor\src\BaseSmartTaskProcessor;
use dostoevskiy\processor\src\interfaces\StorageAdapterInterface;
use dostoevskiy\processor\src\interfaces\StorageInterface;
use yii\base\InvalidConfigException;
use yii\base\Object;
use yii\helpers\ArrayHelper;

class Storage extends Object implements StorageInterface
{
    public $credentials, $storageOptions, $type;
    /** @var  StorageAdapterInterface|StorageInterface */
    protected $_adapter;
    protected $defaultType        = 'rabbit';
    protected $defaultCredentials = [
        'host'     => 'localhost',
        'port'     => 5672,
        'user'     => 'guest',
        'password' => 'guest',
        'vhost'    => '/',
    ];

    public function init()
    {
        if (empty($this->type)) {
            $this->type           = $this->defaultType;
        }
        if (empty($this->credentials)) {
            throw new InvalidConfigException('You must config storage options as connection credentials, transport options etc.');
        }
        switch ($this->type) {
            case self::STORAGE_TYPE_RABBITMQ:
                $class = 'dostoevskiy\processor\src\storage\adapters\Amqp';
                break;
            default:
                throw new InvalidConfigException('Empty or not existed storage adapter. Only "nats", "mongo", "socket" or "rabbit" types are available. Us it.');
        }

        $this->_adapter = \Yii::createObject(ArrayHelper::merge(['class' => $class], $this->credentials));
    }

    public function push($taskName, $data)
    {
        return $this->_adapter->push($taskName, $data) ? 'success' : 'fail';
    }

    public function pull($callback)
    {
        return $this->_adapter->pull($callback);
    }

    public function configureContext($task, $config)
    {
        return $this->_adapter->configureContext($task, $config);
    }

    public function configureConnection()
    {
        return $this->_adapter->configureConnection();
    }


    public function loop($callback)
    {
        return $this->_adapter->loop($callback);
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
}