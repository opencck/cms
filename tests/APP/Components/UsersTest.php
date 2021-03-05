<?php
namespace APP\Components;

use APP\AppTestCase;
use Exception;

/**
 * Class UsersTest
 * @package APP\Components
 */
class UsersTest extends AppTestCase {
	/**
	 * @throws Exception
	 */
	public function testVisitorAuth() {
		$result = $this->execute('users.auth');
		$this->assertEquals(null, $result->mutations[0]->data, "Can't visitor auth");
	}
}
