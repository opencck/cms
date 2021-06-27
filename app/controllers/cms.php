<?php
/** @noinspection PhpUndefinedMethodInspection */

namespace APP\Controllers;

use API\DB\ORM;
use API\DB\ORM\Migration;
use APP\Components\CCK\Models\CCKModel;
use APP\Controller;
use APP\Model;
use APP\Models\CMSModel;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\ResultStatement;
use Exception;

class CMSController extends Controller {
    /**
     * Name of application
     * @var string
     */
    public $name = 'cms';

    /**
     * Notice
     * @param string $title
     * @param string|null $message
     * @param string $type
     * @param array $params
     */
    public function notice($title, $message = null, $type = 'warning', $params = []) {
        $this->addAction(
            'notice',
            array_merge(
                [
                    'title' => $title,
                    'icon' => $type,
                ],
                !is_null($message) ? ['text' => $message] : [],
                $params
            )
        );
    }

    /**
     * Get application model
     * @param string|null $name Model name
     * @param string|null $app App name
     * @param bool $root if APP\Models root model
     * @return Model|CMSModel|CCKModel
     */
    public function getModel($name = null, $app = null, $root = false) {
        $name = is_null($name) ? $this->name : $name;
        $app = is_null($app) ? $this->name : $app;
        $name = $root
            ? '\\APP\\Models\\' . $name . 'Model'
            : '\\APP\\Components\\' . $app . '\\Models\\' . $name . 'Model';
        return $name::getInstance($app, !$root);
    }

    /**
     * Debug notice @dbg
     * @param $params
     */
    public function dbg($params) {
        $this->notice('dbg', null, 'warning', [
            'html' =>
                '<pre style="text-align: left;overflow: auto;font-family: monospace;font-size: 16px;">' .
                print_r($params, true) .
                '</pre>',
        ]);
    }

    /**
     * Checking config of application ORM
     * @param string $app app name
     * @param object $config app config
     * @return array list of applications with migrations
     * @throws DBALException
     * @throws Exception
     */
    public function checkApp($app, $config) {
        // Check application name
        if ($config->name != $app) {
            throw new Exception(
                'Component "' .
                    $app .
                    '" contains configuration file for other component "' .
                    $config->name .
                    '" or component name and their directory name must be equal'
            );
        }
        // Check Object-relational Model (ORM) of application
        if (isset($config->orm)) {
            if (is_array($config->orm)) {
                $migrations = [];
                // Application ORM by config.json file
                $ORM = new ORM($app, $config);
                $ORM->install(); // Install root entities if not exist in DB

                /** @var CMSModel $model */
                $model = $this->getModel('cms', $app, true);
                $appORM = $model->getORM(); // Application ORM by CMS Model

                // If new installation
                if (!$appORM->getConfig()) {
                    if (isset($config->install) && isset($config->install->sql)) {
                        $path =
                            implode(DIRECTORY_SEPARATOR, [PATH_ROOT, 'app', 'components', $appORM->name]) .
                            DIRECTORY_SEPARATOR;
                        if (dirname($path) === dirname($path . $config->install->sql, 2)) {
                            if (is_file($path . $config->install->sql)) {
                                $migrations[] = (new Migration(
                                    null,
                                    'Application install sql',
                                    "Execute '{$config->install->sql}' for application '{$this->name}'"
                                ))->execute(explode("\n", file_get_contents($path . $config->install->sql)));
                            } else {
                                throw new Exception('Invalid sql install file: ' . $path . $config->install->sql);
                            }
                        } else {
                            throw new Exception('Invalid path to sql install file: ' . $path . $config->install->sql);
                        }
                    }
                }
                $migrations = array_merge($appORM->check($config), $migrations);

                // Get ORM migrations
                return [
                    'name' => $app,
                    'description' => isset($config->description) ? $config->description : '',
                    'orm' => $ORM,
                    'migrations' => $migrations,
                ];
            } else {
                throw new Exception(
                    'Component "' .
                        $app .
                        '" contains configuration file for other component "' .
                        $config->name .
                        '" or component name and their directory name must be equal'
                );
            }
        }
        return [
            'name' => $app,
            'description' => isset($config->description) ? $config->description : '',
        ];
    }

    /**
     * DB update based on migration checks from ORM
     * @param $app
     * @return ResultStatement|int
     * @throws Exception
     */
    public function installApp($app) {
        // Implement migration updates
        foreach ($app['migrations'] as $migration) {
            if (!$migration->implement()) {
                throw new Exception('Unable to implement migration ' . $migration->label);
            }
        }
        /**
         * CMS Model for Application
         * @var CMSModel $model
         */
        $model = $this->getModel('cms', $app['orm']->name, true);
        return $model->saveConfig($app['orm'], is_null($model->getORM()->getConfig())); // Save application config
    }
}
