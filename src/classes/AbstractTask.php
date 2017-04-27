<?php
namespace dostoevskiy\processor\src\classes;

use dostoevskiy\processor\SmartTaskProcessor;
use dostoevskiy\processor\src\interfaces\TaskProcessorInterface;
use dostoevskiy\processor\src\storage\Storage;
use yii\base\InvalidConfigException;
use yii\base\Object;
use yii\helpers\ArrayHelper;

abstract class AbstractTask extends Object implements TaskProcessorInterface
{
    public $threads          = 1;
    public $type;
    /** @var  string|Storage */
    public $storage;
    public $transactional;
    public $storageOptions   = [];

    protected $defaultType = 'live';

    const TYPE_LIVE     = 'live';
    const TYPE_DEFERRED = 'deferred';

    final public function init()
    {
        if (empty($this->type)) {
            $this->type = $this->defaultType;
        }
        if (!in_array($this->type, array_keys($this->getAvailableTypes()))) {
            throw new InvalidConfigException('Only "live" or "deferred" types are available. Us it.');
        }
        $storage       = ArrayHelper::getValue(SmartTaskProcessor::$storages, $this->storage, false);
        if(!$storage) {
            throw new InvalidConfigException("Storage $this->storage missing in storage config");
        }
        $this->storage = $storage;
    }

    /**
     * @return array
     */
    public function getAvailableTypes()
    {
        return [
            self::TYPE_DEFERRED => 'Отложенное выполнение',
            self::TYPE_LIVE     => 'В реальном времени'
        ];
    }

    public function isLive()
    {
        return $this->type == self::TYPE_LIVE;
    }

    public function isTransactional() {
        return (bool) $this->transactional;
    }
}