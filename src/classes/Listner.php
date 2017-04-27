<?php

namespace dostoevskiy\processor\src\classes;

use dostoevskiy\processor\src\interfaces\ListnerInterface;
use yii\base\InvalidConfigException;
use yii\base\Object;

class Listner extends Object implements ListnerInterface
{
    const MODE_LISTEN  = 'listen';
    const MODE_PROCESS = 'process';
    /** @var  Worker */
    protected static $worker;
    public           $type, $host, $port, $threads, $servicesToReload;
    public           $mode = self::MODE_LISTEN;
    public           $name;
    public           $onConnect, $onMessage, $onClose, $onWorkerStart;

    public function init()
    {
        if ($this->mode == self::MODE_LISTEN && (empty($this->type) || empty($this->host) || empty($this->port) || empty($this->threads))) {
            throw new InvalidConfigException('Invalid configuration. Specify type, host, port and count props');
        }
        if (empty(self::$worker)) {
            if ($this->mode == self::MODE_LISTEN) {
                $dsn          = $this->type . "://" . $this->host . ':' . $this->port;
                self::$worker = new Worker($dsn);
            } else {
                self::$worker = new Worker();
            }
        }
    }

    public function listen()
    {
        self::$worker->name = $this->name;
        self::$worker->setServicesToReload($this->servicesToReload);
        self::$worker->count = $this->threads;
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