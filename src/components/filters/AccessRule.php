<?php
namespace dostoevskiy\processor\src\components\filters;
use Yii;

class AccessRule extends \yii\filters\AccessRule {
	/**
	 * Checks whether the Web user is allowed to perform the specified action.
	 * @param Action $action the action to be performed
	 * @param User $user the user object
	 * @param Request $request
	 * @return bool|null true if the user is allowed, false if the user is denied, null if the rule does not apply to the user
	 */
	public function allows($action, $user, $request)
	{
		if ($this->matchAction($action)
		    && $this->matchRole($user)
		    && $this->matchIP(Yii::$app->restRequest->getUserIP())
		    && $this->matchController($action->controller)
		    && $this->matchCustom($action)
		) {
			return $this->allow ? true : false;
		} else {
			return null;
		}
	}
}