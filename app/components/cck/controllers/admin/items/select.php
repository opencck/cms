<?php
namespace APP\Components\CCK\Controllers\Admin\Items;

use API\Input;
use APP\Controllers\AdminController;
use APP\Models\CMSModel;
use Doctrine\DBAL\DBALException;

/**
 * Class selectController
 * @package APP\Components\CCK\Controllers\Admin\Items
 */
class selectController extends AdminController {
    /**
     * Name of application
     * @var string
     */
    public $name = 'cck_app';

    /**
     * @param string $name
     * @param array<array-key, Input> $arguments
     * @return array
     * @throws DBALException
     */
    public function __call($name, $arguments) {
        $input = $arguments[0];
        $this->name = $input->get('app', $this->name, 'string');
        /** @var CMSModel $model */
        $model = $this->getModel('cms', $this->name, true);

        return $model
            ->selectItems(
                $name,
                $input->get('select', [], 'array'),
                $input->get('where', [], 'array'),
                $input->get('order', [], 'array'),
                $input->get('group', [], 'array'),
                $input->get('limit', [], 'array')
            )
            ->getValues();
    }
}
