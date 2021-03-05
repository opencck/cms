<?php
namespace APP;

/**
 * Class Router
 * @package APP
 */
class Router {
	/**
	 * Application visitor session location
	 * @var string
	 */
	private $location = 'api';

	/**
	 * Router constructor.
	 */
	public function __construct() {
		if (isset($_SERVER['REQUEST_URI'])) {
			$segments = explode('/', $_SERVER['REQUEST_URI']);
			foreach ($segments as $segment) {
				if (in_array($segment, array_merge(explode(';', $_ENV['SYS_LOCATIONS']), ['admin']))) {
					$this->location = $segment;
				}
			}
		}
	}

	/**
	 * Parse request method
	 * @param $path
	 * @param array $params
	 * @return array
	 */
	public function parse($path, &$params = []) {
		$controller = 'default';
		$method = 'default';
		$parts = explode('.', $path);
		if (count($parts) > 1) {
			$method = array_pop($parts);
		}
		if (count($parts) > 1) {
			$controller = array_pop($parts);
		}
		$component = array_shift($parts);
		// prettier-ignore
		return [
			'class' => implode('', [
				'\\APP\\Components\\', $component, '\\Controllers\\',
				count($parts) ? implode('\\', $parts).'\\' : '',
				$controller, 'Controller'
			]),
			'method' => $method
		];
	}

	/**
	 * Get application visitor session location
	 * @return string
	 */
	public function getSessionLocation() {
		return $this->location;
	}

	/**
	 * Set application visitor session location
	 * @param string $location
	 */
	public function setLocation(string $location): void {
		$this->location = $location;
	}
}
