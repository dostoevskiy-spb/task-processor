<?php
namespace dostoevskiy\processor\src\storage;

use dostoevskiy\processor\src\interfaces\StorageInterface;
use yii\base\Object;

class Storage extends Object implements StorageInterface
{
    public $storageOptions, $type;
    protected $_adapter;
    public function init() {
        switch($this->type) {

        }
    }
    public function push($data) {}

    public function pull() {}

    public function configurateContextForAdapter()
    {
        return $this->_adapter->configurateContext();
    }
}