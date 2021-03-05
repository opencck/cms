<?php
namespace APP\Components\Apps\Controllers;

use API\Input;
use APP\Controllers\AdminController;
use Exception;

/**
 * Applications control and management
 * @package APP\Components\Apps\Controllers
 */
class defaultController extends AdminController {
	/**
	 * Name of application
	 * @var string
	 */
	public $name = 'apps';

	/**
	 * List of apps names
	 * @var array
	 */
	private $apps;

	/**
	 * Path to components folder
	 * @var string
	 */
	private $path = 'app/components';

	/**
	 * Initialize applications
	 */
	public function __construct() {
		$this->apps = array_values(array_diff(scandir(PATH_ROOT . '/' . $this->path . '/'), ['.', '..']));
		parent::__construct();
	}

	/**
	 * Get applications
	 * @see \AppsTest::testInstalledDefaultApps
	 */
	public function default() {
		$this->addMutation('apps/set', $this->apps);
	}

	/**
	 * Checking of applications ORM and statuses
	 * @return array list of applications with migrations
	 * @throws Exception
	 * @see \AppsTest::testApplicationsChecking
	 */
	public function check() {
		return array_map(function ($app) {
			$config = PATH_ROOT . '/' . $this->path . '/' . $app . '/config.json';
			if (is_file($config)) {
				$config = json_decode(file_get_contents($config));
				if (is_object($config)) {
					return $this->checkApp($app, $config);
				} else {
					throw new Exception('Component "' . $app . '" does not contain valid configuration file');
				}
			} else {
				throw new Exception('Component "' . $app . '" does not contain configuration file');
			}
		}, $this->apps);
	}

	/**
	 * Install or DB update based on migration checks from ORM
	 * @param Input $input
	 * @return bool
	 * @throws Exception
	 * @see \AppsTest::testApplicationsInstalling
	 */
	public function install($input) {
		$inputApp = $input->get('app', (object) ['name' => ''], 'object');
		foreach ($this->check() as $app) {
			if (!$inputApp->name || $inputApp->name == $app['name']) {
				$this->installApp($app);
			}
		}
		return true;
	}
}
