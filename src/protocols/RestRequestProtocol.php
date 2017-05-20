<?php

namespace dostoevskiy\processor\src\protocols;

use common\models\Bot;
use dostoevskiy\processor\src\classes\AbstractTask;
use dostoevskiy\processor\src\helpers\RestResponse;
use dostoevskiy\processor\src\interfaces\RequestProtocolInterface;
use Workerman\Connection\ConnectionInterface;
use Workerman\Protocols\HttpCache;
use Yii;
use yii\helpers\ArrayHelper;

class RestRequestProtocol implements RequestProtocolInterface {

	public function getProcessRequestCallback($tasks) {
		return function ($connection, $data) use ($tasks) {
			/** @var $connection \Workerman\Connection\ConnectionInterface */
			Yii::$app->restRequest->setUserIP($connection->getRemoteIp());
			$body     = explode("\r\n", $data);
			$body     = json_decode(array_pop($body), true, 1024);
			/** @var ConnectionInterface $connection */
			/** @var AbstractTask $taskInstance */
			$taskInstance = ArrayHelper::getValue($tasks, 'rest');
			if ($taskInstance->isLive()) {
				if ($taskInstance->isTransactional()) {
					/** @var RestResponse $response */
					$response = $taskInstance->process($body);
					$connection->send($response->asRaw());
					$connection->close();

					return;
				} else {
					$connection->send(json_encode(['status' => 'success']));
					$connection->close();
					/** @var RestResponse $response */
					$taskInstance->process($data);

					return;
				}
			}
		};
	}
}