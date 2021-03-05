<?php
namespace APP\Components\Test\Controllers;

use API\App;
use API\Session;
use APP\Controller;

class defaultController extends Controller {
	public function default($input) {
		$session = Session::getInstance();
		$store = App::getInstance()->getStore();
		return [
			'params' => $input,
			'session' => $session,
			'store' => $store,
		];
	}
}
