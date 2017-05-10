<?php

namespace dostoevskiy\processor\src\tasks;

use dostoevskiy\processor\src\classes\AbstractTask;
use Yii;
use yii\base\InvalidRouteException;
use yii\web\NotFoundHttpException;

class RestTaskProcessor extends AbstractTask {

	function process($data) {
		try {
			try {
				Yii::$app->restRequest->setUrl($data['route']);
				Yii::$app->restRequest->setBodyParams($data['data']);
				list ($route, $params) = Yii::$app->restRequest->resolve();
			} catch (UrlNormalizerRedirectException $e) {
				$url = $e->url;
				if (is_array($url)) {
					if (isset($url[0])) {
						// ensure the route is absolute
						$url[0] = '/' . ltrim($url[0], '/');
					}
					$url += Yii::$app->restRequest->getQueryParams();
				}

				return Yii::$app->restResponse->redirect(Url::to($url, $e->scheme), $e->statusCode);
			}
			$result = Yii::$app->runAction($route, $data['data']);

			return $result;
		} catch (InvalidRouteException $e) {
			throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'), $e->getCode(), $e);
		}
	}

	function prepare($data) {
		// TODO: Implement prepare() method.
	}
}