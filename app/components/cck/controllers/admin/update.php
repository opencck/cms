<?php
namespace APP\Components\CCK\Controllers\Admin;

use API\Input;
use APP\Controllers\AdminController;
use APP\Helpers\CMSHelper;
use APP\Models\CMSModel;
use Exception;

/**
 * Class @updateController
 * @package APP\Components\Apps\Controllers\Admin
 */
class updateController extends AdminController {
	/**
	 * Name of application
	 * @var string
	 */
	public $name = 'cck';

	/**
	 * @param string $name
	 * @param array<array-key, Input> $arguments
	 * @return bool
	 * @throws Exception
	 */
	public function __call($name, $arguments) {
		return $this->updateEntity($name, $arguments[0]);
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

	/**
	 * Update `apps` entity
	 * @param Input $input
	 * @return bool
	 * @throws Exception
	 */
	public function apps($input) {
		$helper = new CMSHelper();
		$input->set(
			'items',
			$helper->jsonEncodeByKeys($input->get('items', [], 'array'), [
				'entities' => ['options' => ['view'], 'relations' => ['multiple'], 'keys' => ['fields', 'references']],
				'views' => 'views',
			])
		);
		return $this->updateEntity('apps', $input);
	}
}
