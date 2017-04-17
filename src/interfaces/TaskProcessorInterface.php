<?php
namespace dostoevskiy\processor\src\interfaces;

interface TaskProcessorInterface
{
    function process($data);
    function prepare($data);
}