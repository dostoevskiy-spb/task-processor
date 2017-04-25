<?php
namespace dostoevskiy\processor\src\interfaces;

interface StorageAdapterInterface
{
    public function configureContext($taskName, $config);
}