<?php
namespace APP\Components\CCK\Controllers;

use API\Input;
use APP\Components\CCK\Models\CCKModel;
use APP\Controllers\AdminController as originAdminController;
use Doctrine\DBAL\DBALException;
use Exception;

/**
 * Class @adminController
 * @package APP\Components\cck\Controllers
 */
class adminController extends originAdminController {
	/**
	 * Application name
	 * @var string
	 */
	public $name = 'cck';

	/**
	 * @return array
	 * @throws DBALException
	 */
	public function getItems() {
		/** @var CCKModel $model */
		$model = $this->getModel('cck');
		return $model->getItems('entities');
	}

	/**
	 * Checking of applications ORM and statuses from CCK
	 * @return array
	 * @throws DBALException
	 * @see CCKTest::testApplicationsChecking
	 */
	public function check() {
		/** @var CCKModel $model */
		$model = $this->getModel('cck');
		return array_map(function ($config) {
			$config = (object) $config;
			return $this->checkApp($config->name, $config); // Get ORM migrations
		}, $model->getApps());
	}

	/**
	 * Install or DB update based on migration checks from CCK application ORM
	 * @param Input $input
	 * @return bool
	 * @throws Exception
	 * @see CCKTest::testApplicationsInstalling
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
