<?php
namespace dostoevskiy\processor\src\components;
use yii\web\Request;

/**
 *
 * @property mixed $token
 */
class RestRequest extends Request {
	protected $_token;

	public function setToken($token) {
		$this->_token = $token;

		return $this;
	}

	public function getToken() {
		return $this->_token;
	}

	/**
	 * @return null|string
	 */
	public function getUserIP() {
		return $this->userIP;
	}

	/**
	 * @param null|string $userIP
	 */
	public function setUserIP($userIP) {
		$this->userIP = $userIP;
	}
}