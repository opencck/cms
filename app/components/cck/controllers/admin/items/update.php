<?php
namespace APP\Components\CCK\Controllers\Admin\Items;

use API\Input;
use APP\Controllers\AdminController;
use APP\Models\CMSModel;
use Exception;

/**
 * Class @updateController
 * @package APP\Components\Apps\Controllers\Admin\Items
 */
class updateController extends AdminController {
    /**
     * Name of application
     * @var string
     */
    public $name = 'cck';

    /**
     * @param string $name
     * @param Input[] $arguments
     * @return bool
     * @throws Exception
     */
    public function __call($name, $arguments) {
        $input = $arguments[0];
        $this->name = $input->get('app', $this->name, 'string');
        return $this->updateEntity($name, $input);
    }

    /**
     * Update entity data with input items
     * @param string $entity
     * @param Input $input
     * @return bool
     * @throws Exception
     */
    private function updateEntity($entity, $input) {
        /** @var CMSModel $model */
        $model = $this->getModel('cms', $this->name, true);
        return $model->updateItems($entity, $input->get('items', [], 'array'));
    }
}
