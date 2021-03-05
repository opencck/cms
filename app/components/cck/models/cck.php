<?php

namespace APP\Components\CCK\Models;

use APP\Helpers\CMSHelper;
use APP\Models\CMSModel;
use Doctrine\DBAL\DBALException;
use Exception;

/**
 * Class CCKModel
 * @package APP\Components\CCK\Models
 */
class CCKModel extends CMSModel {
	/**
	 * @var string[] entity options key bindings
	 */
	private $bindKeys = ['options' => 'field', 'keys' => 'key'];

	/**
	 * Get app configs
	 * @return array
	 * @throws DBALException
	 * @throws Exception
	 */
	public function getApps() {
		$helper = new CMSHelper();
		return array_map(
			function ($app) use ($helper) {
				$app['name'] = $this->getORM()->name . '_' . $app['name'];
				if (!$app['description']) {
					$app['description'] = 'CCK app: ' . $app['name'];
				}
				if (!isset($app['settings'])) {
					$app['settings'] = [];
				}
				if (!isset($app['orm'])) {
					$app['orm'] = [];
				}

				foreach ($app['entities'] as &$entity) {
					// options bind keys
					$entity = $helper->bindKeys((object) $entity, $this->bindKeys);
					// relation multiple option bind keys
					$this->bindRelationsKeys($entity->relations);
					// label of entity
					$entity->label = $entity->name;
					$entity->name = $entity->entity;

					$app['orm'][] = $entity;
				}

				return $app;
			},
			$helper->jsonDecodeByKeys($this->getItems('apps'), [
				'entities' => [
					'options' => ['view'],
					'relations' => ['multiple'],
					'keys' => ['fields', 'references'],
				],
				'views' => 'views',
			])
		);
	}

	private function bindRelationsKeys(&$relations) {
		$helper = new CMSHelper();
		foreach ($relations as &$relation) {
			if (isset($relation['multiple']) && $relation['multiple']) {
				$relation = $helper->bindKeys((object) $relation, [
					'multiple' => $this->bindKeys,
				]);
				$relation->multiple->label = $relation->multiple->name;
				$relation->multiple->name = $relation->multiple->entity;

				$this->bindRelationsKeys($relation->multiple->relations);
			}
		}
	}
}
