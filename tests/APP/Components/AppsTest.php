<?php
namespace APP\Components;

use APP\AppTestCase;
use APP\Components\Apps\Controllers\defaultController;
use Exception;

/**
 * Class AppsTest
 * @package APP\Components
 */
class AppsTest extends AppTestCase {
	/**
	 * @covers defaultController::default
	 * @throws Exception
	 */
	public function testInstalledDefaultApps() {
		$mutations = $this->execute('apps', [], null, 'admin')->mutations;
		$this->assertEquals('apps/set', $mutations[0]->name);
		$required = ['apps', 'cck', 'users'];
		$actual = array_filter($mutations[0]->data, function ($item) use ($required) {
			return in_array($item, $required);
		});
		$this->assertEquals(
			count($required),
			count($actual),
			'You need install: ' . implode(', ', array_diff($required, $actual))
		);
	}

	/**
	 * @covers defaultController::check
	 * @throws Exception
	 */
	public function testApplicationsChecking() {
		$apps = $this->execute('apps.check', [], null, 'admin');
		foreach ($apps as $app) {
			$this->assertIsArray($app->migrations, 'migrations error in application: ' . $app->name);
		}
	}

	/**
	 * @covers defaultController::install
	 * @throws Exception
	 */
	public function testApplicationsInstalling() {
		$this->assertTrue($this->execute('apps.install', [], null, 'admin'));
	}
}
