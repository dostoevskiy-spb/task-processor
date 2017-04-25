<?php
namespace dostoevskiy\processor\src\interfaces;

interface StorageInterface
{
    public function push($data);
    public function pull($callback);
    public function configurateContext();
    public function loop($callback);
}