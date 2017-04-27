<?php
namespace dostoevskiy\processor\src\interfaces;

interface StorageInterface
{
    const STORAGE_TYPE_NATS     = 'nats';
    const STORAGE_TYPE_MONGO    = 'mongo';
    const STORAGE_TYPE_SOCKET   = 'socket';
    const STORAGE_TYPE_RABBITMQ = 'rabbit';

    public function push($taskName, $data);
    public function pull($callback, $taskName);
    public function configureConnection();
    public function configureContext($task, $config);
    public function loop($callback, $taskName);
}