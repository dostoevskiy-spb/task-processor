<?php
namespace dostoevskiy\tools\interfaces;

interface StorageInterface
{
    public function push($data);
    public function pull();
}