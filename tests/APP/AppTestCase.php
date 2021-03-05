<?php

namespace APP;

use API\App;
use Exception;
use PHPUnit\Framework\TestCase;

class AppTestCase extends TestCase {
	/**
	 * @var App
	 */
	protected $app;

	public function setUp(): void {
		$this->app = new App();
	}

	public function tearDown(): void {
		$this->app = App::getInstance(false);
	}

	/**
	 * @param string $method
	 * @param array $params
	 * @param null $id
	 * @param string $location
	 * @return void|mixed
	 * @throws Exception
	 */
	public function execute($method, $params = [], $id = null, $location = 'api') {
		$this->app->getRouter()->setLocation($location);
		$this->app->getStore()->addRequest(
			(object) [
				'method' => $method,
				'params' => $params,
				'id' => $id,
			]
		);
		$response = $this->app->execute(true)[0];
		if (!isset($response['error'])) {
			return json_decode(json_encode($response['result']));
		} else {
			$this->fail(json_encode($response['error'], JSON_PRETTY_PRINT));
		}
	}
}
