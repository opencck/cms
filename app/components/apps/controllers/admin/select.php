<?php
namespace APP\Components\Apps\Controllers\Admin;

use API\Input;
use APP\Components\Apps\Models\AppsModel;
use APP\Controllers\AdminController;
use APP\Helpers\CMSHelper;
use Doctrine\DBAL\DBALException;
use Exception;

/**
 * Class @selectController
 * @package APP\Components\Apps\Controllers\Admin
 */
class selectController extends AdminController {
    /**
     * Name of application
     * @var string
     */
    public $name = 'apps';

    /**
     * @param string $name
     * @param array<array-key, Input> $arguments
     * @return array
     * @throws DBALException
     * @throws Exception
     */
    public function __call($name, $arguments) {
        /** @var AppsModel $model */
        $model = $this->getModel();
        $helper = new CMSHelper();

        return $helper->jsonDecodeByKeys($model->getItems($name), ['config']);
    }
}
