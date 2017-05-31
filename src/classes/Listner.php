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

    protected $isHAProxy = false;

    public function init()
    {
        if ($this->mode == self::MODE_LISTEN && (empty($this->type) || empty($this->host) || empty($this->port) || empty($this->threads))) {
            throw new InvalidConfigException('Invalid configuration. Specify type, host, port and count props');
        }
        if (empty(self::$worker)) {
            if ($this->mode == self::MODE_LISTEN) {
                if (is_array($this->port)) {
                    $this->isHAProxy = true;
                    self::$worker    = [];
                    foreach ($this->port as $port) {
                        $dsn            = $this->type . "://" . $this->host . ':' . $port;
                        self::$worker[] = new Worker($dsn);
                    }
                } else {
                    $dsn          = $this->type . "://" . $this->host . ':' . $this->port;
                    self::$worker = new Worker($dsn);
                }
            } else {
                self::$worker = new Worker();
            }
        }
    }

    public function listen()
    {
        if ($this->isHAProxy == true) {
            foreach (self::$worker as $worker) {
                $this->configureWorker($worker);
            }
        }
        $this->configureWorker(self::$worker);
        Worker::runAll();
    }

    /**
     * @param $worker Worker
     */
    protected function configureWorker($worker)
    {
        $worker->name = $this->name;
        $worker->setServicesToReload($this->servicesToReload);
        $worker->count = $this->threads;
        if (is_callable($this->onMessage)) {
            $worker->onMessage = $this->onMessage;
        }
        if (is_callable($this->onConnect)) {
            $worker->onConnect = $this->onConnect;
        }
        if (is_callable($this->onClose)) {
            $worker->onClose = $this->onClose;
        }
        if (is_callable($this->onWorkerStart)) {
            $worker->onWorkerStart = $this->onWorkerStart;
        }
    }


}