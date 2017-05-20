<?php

namespace dostoevskiy\processor\src\tasks;

use common\models\Bot;
use dostoevskiy\processor\src\classes\AbstractTask;
use dostoevskiy\processor\src\helpers\RestResponse;
use Yii;
use yii\base\InvalidRouteException;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class RestTaskProcessor extends AbstractTask {

	function process($data) {
		try {
			Yii::$app->restRequest->setUrl(ArrayHelper::getValue($data, 'route'));
			Yii::$app->restRequest->setBodyParams(ArrayHelper::getValue($data, 'data'));
			Yii::$app->restRequest->setToken(ArrayHelper::getValue($data, 'token'));
			Bot::login(Yii::$app->restRequest->getToken());
			list ($route, $params) = Yii::$app->restRequest->resolve();
			try {
				$result = Yii::$app->runAction($route, $data['data']);
			} catch (HttpException $e) {
				return RestResponse::error($e->getMessage(), $e->statusCode);
			} catch (\Exception $e) {
				return RestResponse::error($e->getMessage(), $e->getCode() ?: 500);
			}

			return $result;
		} catch (InvalidRouteException $e) {
			throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'), $e->getCode(), $e);
		}
	}

	function prepare($data) {
		// TODO: Implement prepare() method.
	}
}