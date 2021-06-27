<?php
namespace APP\Components\Users\Controllers;

use API\Input;
use API\Session;
use API\Session\User;
use APP\Controllers\CMSController;
use Doctrine\DBAL\DBALException;
use Exception;

class DefaultController extends CMSController {
    /**
     * Name of application
     * @var string
     */
    public $name = 'users';

    /**
     * Default method
     * @throws Exception
     */
    public function default() {
        $this->auth();
    }

    /**
     * Visitor auth
     * @throws Exception
     */
    public function auth() {
        $user = $this->session->getUser();
        $this->addMutation('user/set', $user);
    }

    /**
     * User login
     * @param Input $params
     * @throws DBALException
     * @throws Exception
     */
    public function login($params) {
        $session = Session::getInstance();
        $model = $this->getModel('users');

        $checkUser = $model->select([
            'username' => $params->get('username', '', 'string'),
            'role' => 'admin',
            'state' => 1,
        ]);
        if ($checkUser->count() != 0) {
            /** @var array $data */
            $data = $checkUser->first();
            $user = User::load($data['id']);
            if ($user->checkPassword($params->get('password', '', 'string'))) {
                $session->setUser($user);
                $this->addMutation('user/set', $user);
            } else {
                $this->notice('Error login', 'Invalid username or password', 'error');
            }
        } else {
            $this->notice('Error login', 'Invalid username or password', 'error');
        }
    }
}
