<?php

namespace dostoevskiy\processor\src\classes;

use dostoevskiy\processor\src\interfaces\ListnerInterface;
use yii\base\InvalidConfigException;
use yii\base\Object;

class Listner extends Object implements ListnerInterface
{
    /** @var  Worker */
    protected static $worker;
    public           $type, $host, $port, $threads, $servicesToReload;
    public           $onConnect, $onMessage, $onClose, $onWorkerStart;

    public function init()
    {
        if (empty($this->type) || empty($this->host) || empty($this->port) || empty($this->threads)) {
            throw new InvalidConfigException('Invalid configuration. Specify type, host, port and count props');
        }
        if (empty(self::$worker)) {
            $dsn          = $this->type . "://" . $this->host . ':' . $this->port;
            self::$worker = new Worker($dsn);
            self::$worker->setServicesToReload($this->servicesToReload);
            self::$worker->count = $this->threads;
        }
    }

    public function listen()
    {
        if (is_callable($this->onMessage)) {
            self::$worker->onMessage = $this->onMessage;
        }
        if (is_callable($this->onConnect)) {
            self::$worker->onConnect = $this->onConnect;
        }
        if (is_callable($this->onClose)) {
            self::$worker->onClose = $this->onClose;
        }
        if (is_callable($this->onWorkerStart)) {
            self::$worker->onWorkerStart = $this->onWorkerStart;
        }
        Worker::runAll();
    }


}