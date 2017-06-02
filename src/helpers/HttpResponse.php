<?php

namespace dostoevskiy\processor\src\helpers;

use yii\helpers\ArrayHelper;

class HttpResponse {

	const CONNECTION_KEEP_ALIVE = 'keep-alive';
	const CONNECTION_CLOSE = 'close';

    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';

	public $response = [];
    protected $code;


	public function __construct($data = [], $code = 200) {
		if ($data) {
			$this->response = $data;
            $this->code = $code;
		}
	}

	public function asJson() {
		return json_encode($this->response);
	}

	protected function getResponseStatusString() {
	    $statuses = [
	        200 => 'OK',
            400 => 'Bad Request',
            500 => 'Server error',
            404 => 'Not Found'
        ];

        return ArrayHelper::getValue($statuses, $this->code);
    }

	public function asRaw() {
		$resp   = $this->asJson();
		$length = strlen($resp);
        $responseStatus = $this->getResponseStatusString();

		return "HTTP/1.1 $responseStatus\r\nServer: SmartTaskProcessor\r\nConnection:close\r\nContent-Length: $length\r\nContent-Type: application/json\r\n\r\n" . $resp;
	}
}