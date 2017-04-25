<?php
namespace dostoevskiy\processor\src\classes;

use dostoevskiy\processor\src\interfaces\TaskProcessorInterface;
use yii\base\InvalidConfigException;
use yii\base\Object;

abstract class AbstractTask extends Object implements TaskProcessorInterface
{
    public $threads          = 1;
    public $type;
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