<?php
namespace dostoevskiy\processor\src\components\filters;

use Yii;
use yii\base\ActionFilter;

class Bot extends ActionFilter {

	public function afterAction($action, $result) {
		if (!Yii::$app->user->isGuest) {
			/** @var \common\models\Bot $identity */
			$identity            = Yii::$app->user->identity;
			$identity->lastVisit = time();
			$identity->lastIP    = Yii::$app->restRequest->getUserIP();
			$identity->save();
		}
		Yii::$app->user->logout();

		return $result;
	}
}