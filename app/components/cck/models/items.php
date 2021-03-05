<?php
namespace APP\Components\CCK\Models;

use APP\Models\CMSModel;

/**
 * Class @ItemsModel
 * @package APP\Components\CCK\Models
 */
class ItemsModel extends CMSModel {
	/**
	 * Table name
	 * @var string
	 */
	public $table = 'units';

	/**
	 * Fields of table
	 * @var string[]
	 */
	static $fields = [
		'id' => 'integer',
		'courses_id' => 'integer',
		'state' => 'integer',
		'ordering' => 'integer',
		'name' => 'string',
		'pseudoname' => 'string',
	];
}
