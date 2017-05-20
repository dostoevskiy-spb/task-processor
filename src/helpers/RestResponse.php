<?php

namespace dostoevskiy\processor\src\helpers;

use yii\helpers\ArrayHelper;

class RestResponse {

	const STATUS_ERROR = 'error';
	const STATUS_SUCCESS = 'success';

	public $response = [];


	public function __construct($data = []) {
		if ($data) {
			$this->response = $data;
		}
	}

	public static function error($error, $code) {
		return new self(['status' => self::STATUS_ERROR, 'error' => $error, 'code' => $code]);
	}

	public static function success($data = []) {
		return new self(ArrayHelper::merge(['status' => self::STATUS_SUCCESS], $data));
	}

	public function asJson() {
		return json_encode($this->response);
	}

	public function asRaw() {
		$resp   = $this->asJson();
		$length = strlen($resp);

		return "HTTP/1.1 200 OK\r\nServer: workerman\r\nContent-Length: $length\r\nContent-Type: application/json\r\n\r\n" . $resp;
	}
}