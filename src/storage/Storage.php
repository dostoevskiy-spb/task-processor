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
    public $storageOptions, $type;
    /** @var  StorageAdapterInterface|StorageInterface */
    protected $_adapter;

    public function init()
    {
        switch ($this->type) {
            case BaseSmartTaskProcessor::STORAGE_TYPE_RABBITMQ:
                $class = 'dostoevskiy\processor\src\storage\adapters\Amqp';
                break;
            default:
                throw new InvalidConfigException('empty or not existed storage adapter');
        }
        $this->_adapter = \Yii::createObject(ArrayHelper::merge(['class' => $class], $this->storageOptions));
    }

    public function push($data)
    {
        return $this->_adapter->push($data) ? 'success' : 'fail';
    }

    public function pull()
    {
        return $this->_adapter->pull();
    }

    public function configurateContextForAdapter()
    {
        return $this->_adapter->configurateContext();
    }
}