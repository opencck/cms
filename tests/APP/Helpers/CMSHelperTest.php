<?php
namespace APP\Helpers;

use Exception;
use PHPUnit\Framework\TestCase;

/**
 * Class CMSHelperTest
 * @package APP
 */
class CMSHelperTest extends TestCase {
	/**
	 * Recursive structural grouping of elements
	 * @dataProvider dataProvider
	 * @param $array
	 * @param $keys
	 * @param $expected array
	 * @covers CMSHelper::jsonDecodeByKeys
	 * @throws Exception
	 */
	public function testJsonDecodeByKeys($array, $keys, $expected) {
		$helper = new CMSHelper();
		$this->assertEquals(json_encode($expected), json_encode($helper->jsonDecodeByKeys($array, $keys)));
	}
	public function dataProvider() {
		// prettier-ignore
		return [
			'simple decode' => [
				[ // array
					['json'=>'{"text":"hello world"}']
				],[ // keys
					'json'
				],[ // expected
					['json'=>json_decode('{"text":"hello world"}')]
				],
			],
			'2th deep decode' => [
				[
					[
						'json' => [
							['data'=>'{"text":"hello world"}']
						]
					]
				],[
					'json'=>['data']
				],[
					[
						'json' => [
							['data'=>json_decode('{"text":"hello world"}')]
						]
					]
				],
			],
			'3th deep decode' => [
				[
					[
						'entities' => [
							[
								'options' => [
									[
										'view' => '{"text":"hello options"}'
									]
								],
								'relations' => [
									[
										'multiple' => '{"text":"hello relations"}'
									]
								],
								'keys' => [
									[
										'fields' => '{"text":"hello keys fields"}',
										'references' => '{"text":"hello keys references"}'
									]
								]
							]
						]
					]
				],
				[
					'entities' => ['options' => ['view'], 'relations' => ['multiple'], 'keys' => ['fields', 'references']],
				],
				[
					[
						'entities' => [
							[
								'options' => [
									[
										'view' => json_decode('{"text":"hello options"}')
									]
								],
								'relations' => [
									[
										'multiple' => json_decode('{"text":"hello relations"}')
									]
								],
								'keys' => [
									[
										'fields' => json_decode('{"text":"hello keys fields"}'),
										'references' => json_decode('{"text":"hello keys references"}')
									]
								]
							]
						]
					]
				]
			]
		];
	}

	/**
	 * Recursive structural grouping of elements
	 * @dataProvider dataProvider2
	 * @param $array
	 * @param $keys
	 * @param $expected array
	 * @throws Exception
	 */
	public function testJsonEncodeByKeys($array, $keys, $expected) {
		$helper = new CMSHelper();
		$this->assertEquals($expected, $helper->jsonEncodeByKeys($array, $keys));
	}
	public function dataProvider2() {
		// prettier-ignore
		return [
			'simple encode' => [
				[ // object
					['json'=>json_decode('{"text":"hello world"}')]
				],[ // keys
					'json'
				],[ // expected
					['json'=>'{"text":"hello world"}']
				],
			],
			'2th deep encode' => [
				[
					[
						'json' => [
							['data'=>json_decode('{"text":"hello world"}')]
						]
					]
				],[
					'json'=>['data']
				],[
					[
						'json' => [
							['data'=>'{"text":"hello world"}']
						]
					]
				],
			],
			'3th deep encode' => [
				[
					[
						'entities' => [
							[
								'options' => [
									[
										'view' => json_decode('{"text":"hello options"}')
									]
								],
								'relations' => [
									[
										'multiple' => json_decode('{"text":"hello relations"}')
									]
								],
								'keys' => [
									[
										'fields' => json_decode('{"text":"hello keys fields"}'),
										'references' => json_decode('{"text":"hello keys references"}')
									]
								]
							]
						]
					]
				],
				[
					'entities' => ['options' => ['view'], 'relations' => ['multiple'], 'keys' => ['fields', 'references']],
				],
				[
					[
						'entities' => [
							[
								'options' => [
									[
										'view' => '{"text":"hello options"}'
									]
								],
								'relations' => [
									[
										'multiple' => '{"text":"hello relations"}'
									]
								],
								'keys' => [
									[
										'fields' => '{"text":"hello keys fields"}',
										'references' => '{"text":"hello keys references"}'
									]
								]
							]
						]
					]
				]
			]
		];
	}
}
