<?php
namespace dostoevskiy\processor\src\interfaces;

interface GateProcessorInterface
{
    public function listen();

    public function process();
}