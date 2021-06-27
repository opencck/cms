<?php
namespace APP\Components\Users\Controllers\Admin;

use APP\Controllers\AdminController;
use API\Session;
use API\Session\User;
use Doctrine\DBAL\DBALException;
use Exception;

/**
 * Class @UserController
 * @package APP\Components\Users\Controllers\Admin
 */
class UserController extends AdminController {
    /**
     * User logout
     * @throws DBALException
     * @throws Exception
     */
    public function logout() {
        $session = Session::getInstance();
        $session->setUser(null);
        $this->addMutation('user/set', new User());
    }
}
