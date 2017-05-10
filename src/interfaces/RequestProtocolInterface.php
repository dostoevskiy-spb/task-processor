<?php
namespace dostoevskiy\processor\src\interfaces;

interface RequestProtocolInterface {
	public function getProcessRequestCallback($tasks);
}