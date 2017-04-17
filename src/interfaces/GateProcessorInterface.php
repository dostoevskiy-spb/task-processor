<?php
namespace dostoevskiy\tools\interfaces;

interface GateProcessorInterface
{
    public function listen();

    public function process();
}