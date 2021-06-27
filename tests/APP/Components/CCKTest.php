<?php
namespace APP\Components;

use APP\Components\CCK\Controllers\adminController;
use APP\Components\CCK\Controllers\Admin\selectController;
use APP\AppTestCase;
use Exception;

/**
 * Class CCKTest
 * @package APP\Components
 */
class CCKTest extends AppTestCase {
    /**
     * @covers selectController::__call
     * @throws Exception
     */
    public function testInstalledDefaultApps() {
        $this->assertIsArray($this->execute('cck.admin.select.apps', [], null, 'admin'));
    }

    /**
     * @covers adminController::check
     * @throws Exception
     */
    public function testApplicationsChecking() {
        $apps = $this->execute('cck.admin.check', [], null, 'admin');
        $this->assertIsArray($apps, 'Result of application checking must be array');
        $migrations = 0;
        foreach ($apps as $app) {
            if (isset($app->migrations)) {
                $migrations += count($app->migrations);
            }
        }
        if ($migrations > 0) {
            echo json_encode($apps, JSON_PRETTY_PRINT);
        }
        $this->assertEquals(0, $migrations, 'There are unapplied migrations');
    }

    /**
     * @covers adminController::install
     * @throws Exception
     */
    public function testApplicationsInstalling() {
        $this->assertTrue($this->execute('cck.admin.install', [], null, 'admin'));
    }
}
