<?php
namespace APP\Components\CCK\Controllers\Admin;

use API\Input;
use APP\Components\CCK\Models\CCKModel;
use APP\Controllers\AdminController;
use APP\Helpers\CMSHelper;
use Doctrine\DBAL\DBALException;
use Exception;

/**
 * Class selectController
 * @package APP\Components\CCK\Controllers\Admin
 */
class selectController extends AdminController {
    /**
     * Name of application
     * @var string
     */
    public $name = 'cck';

    /**
     * @param string $name
     * @param array<array-key, Input> $arguments
     * @return array
     * @throws DBALException|Exception
     * @see \CCKTest::testInstalledDefaultApps
     */
    public function __call($name, $arguments) {
        /** @var CCKModel $model */
        $model = $this->getModel();
        $helper = new CMSHelper();

        return $helper->jsonDecodeByKeys(
            $model->getItems(
                $name,
                [],
                [
                    'apps.ordering' => 'ASC',
                    'entities.ordering' => 'ASC',
                    'options.ordering' => 'ASC',
                    'keys.ordering' => 'ASC',
                    'relations.ordering' => 'ASC',
                ]
            ),
            [
                'entities' => ['options' => ['view'], 'relations' => ['multiple'], 'keys' => ['fields', 'references']],
                'views' => 'views',
            ]
        );
    }
}
