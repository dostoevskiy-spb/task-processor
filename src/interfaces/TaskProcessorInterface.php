<?php
namespace dostoevskiy\tools\interfaces;

interface TaskProcessorInterface
{
    function process($data);
    function prepare($data);
}