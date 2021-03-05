<?php
namespace APP\Controllers;

use API\App;
use API\Store\Response\Result;
use Exception;

/**
 * Administration side controller
 * @package APP
 */
class AdminController extends CMSController {
	/**
	 * Executing administration api
	 * @param string $method
	 * @param object|array $params
	 * @param string|integer|null $id
	 * @return Result
	 * @throws Exception
	 */
	public function execute($method, $params = [], $id = null) {
		if (
			App::getInstance()
				->getRouter()
				->getSessionLocation() != 'admin'
		) {
			throw new Exception('Invalid session location', 401);
		}

		if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'APPTesting') {
			return parent::execute($method, $params, $id);
		} else {
			$user = App::getInstance()
				->getSession()
				->getUser();
			if ($user && $user->role == 'admin') {
				return parent::execute($method, $params, $id);
			} else {
				throw new Exception('Access deny', 403);
			}
		}
	}
}
