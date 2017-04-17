<?php

namespace dostoevskiy\tools\classes;

use yii\base\InvalidConfigException;
use yii\base\Object;

class Listner extends Object
{
    protected static $worker;
    public           $type, $host, $port, $count, $servicesToReload;
    public           $onConnect, $onMessage, $onClose;

    public function init()
    {
        if (empty($this->type) || empty($this->host) || empty($this->port) || empty($this->count)) {
            throw new InvalidConfigException('Invalid configuration. Specify type, host, port and count props');
        }
        if (empty(self::$worker)) {
            $dsn                 = $this->type . "://" . $this->host . ':' . $this->port;
            self::$worker        = new Worker($dsn);
            self::$worker->servicesToReload = $this->servicesToReload;
            self::$worker->count = $this->count;
        }
    }

    public function run()
    {
        Worker::runAll();
    }


}