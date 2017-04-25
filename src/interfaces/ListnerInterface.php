<?php
namespace dostoevskiy\processor\src\interfaces;

interface ListnerInterface
{
    const LISTEN_TYPE_HTTP      = 'http';
    const LISTEN_TYPE_WEBSOCKET = 'websocket';
    const LISTEN_TYPE_TCP       = 'tcp';

    function listen();
}