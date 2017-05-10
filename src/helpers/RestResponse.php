<?php

namespace dostoevskiy\processor\src\helpers;
class RestResponse {

	public $response = [];

	public function __construct($data = []) {
		if ($data) {
			$this->response = $data;
		}
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