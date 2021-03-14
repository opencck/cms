<?php
namespace APP\Helpers;

use APP\CMSHelperTest;
use Exception;

/**
 * Class CMSHelper
 * @package APP\Helpers
 */
class CMSHelper {
	/**
	 * JSON decode options in objects[] by keys
	 * @param mixed[] $array
	 * @param array $keys
	 * @return mixed[]
	 * @throws Exception
	 * @see CMSHelperTest::testJsonDecodeByKeys
	 */
	public function jsonDecodeByKeys($array, $keys = []) {
		return array_map(function ($item) use ($keys) {
			foreach ($keys as $option => $key) {
				if (is_array($key)) {
					$item[$option] = isset($item[$option]) ? $this->jsonDecodeByKeys($item[$option], $key) : [];
				} else {
					if (!is_string($item[$key])) {
						throw new Exception("Can't jsonDecodeByKeys, expected string in {$key} key");
					}
					$item[$key] = json_decode($item[$key]);
				}
			}
			return $item;
		}, $array);
	}

	/**
	 * JSON encode options in objects[] by keys
	 * @param mixed[] $array
	 * @param array $keys
	 * @return mixed[]
	 * @throws Exception
	 * @see CMSHelperTest::testJsonEncodeByKeys
	 */
	public function jsonEncodeByKeys($array, $keys = []) {
		return array_map(function ($item) use ($keys) {
			foreach ($keys as $option => $key) {
				if (is_object($item)) {
					if (is_array($key)) {
						$item->{$option} = $this->jsonEncodeByKeys($item->{$option}, $key);
					} else {
						$item->{$key} = json_encode($item->{$key});
					}
				} else {
					if (is_array($key)) {
						if (!is_array($item[$option])) {
							throw new Exception("Can't jsonEncodeByKeys, expected array in {$option} option");
						}
						$item[$option] = $this->jsonEncodeByKeys($item[$option], $key);
					} else {
						$item[$key] = json_encode($item[$key]);
					}
				}
			}
			return $item;
		}, $array);
	}

	/**
	 * Bind object keys by array fields
	 * @param object $object
	 * @param array $fields
	 * @return object
	 */
	public function bindKeys($object, $fields) {
		foreach ($fields as $key => $property) {
			$items = [];
			if (is_array($property)) {
				$object->{$key} = $this->bindKeys((object) $object->{$key}, $property);
			} else {
				foreach ($object->{$key} as $item) {
					if (is_array($item)) {
						$item = (object) $item;
					}
					$items[$item->{$property}] = $item;
				}

				$object->{$key} = $items;
			}
		}
		return $object;
	}
}
