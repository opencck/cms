<?php
namespace APP;

use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase {
	/**
	 * @param string $path
	 * @param array $expected
	 * @dataProvider providerParse
	 */
	public function testParse($path, $expected) {
		$router = new Router();
		$this->assertEquals($expected, $router->parse($path));
	}

	public function providerParse() {
		return [
			'one segment' => [
				'one',
				[
					'class' => '\APP\Components\one\Controllers\defaultController',
					'method' => 'default',
				],
			],
			'two segment' => [
				'one.two',
				[
					'class' => '\APP\Components\one\Controllers\defaultController',
					'method' => 'two',
				],
			],
			'three segment' => [
				'one.two.three',
				[
					'class' => '\APP\Components\one\Controllers\twoController',
					'method' => 'three',
				],
			],
			'four segment' => [
				'one.two.three.four',
				[
					'class' => '\APP\Components\one\Controllers\two\threeController',
					'method' => 'four',
				],
			],
			'five segment' => [
				'one.two.three.four.five',
				[
					'class' => '\APP\Components\one\Controllers\two\three\fourController',
					'method' => 'five',
				],
			],
		];
	}
}
