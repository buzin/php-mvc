<?php

/**
 * Created by PhpStorm.
 * User: Vitaly
 * Date: 17.07.2018
 * Time: 15:34
 */

require_once 'BaseController.php';
require_once 'models/UserModel.php';

class UserController extends BaseController
{
    public function login()
    {
        if (isset($_GET['logout'])) {
            UserModel::logout();
        }

        $login = '';
        $message = '';
        if (isset($_POST['submit'])) {
            $login = filter_input(INPUT_POST, "login", FILTER_SANITIZE_STRING);
            $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING);
            try {
                UserModel::login($this->pdo, $login, $password);
            }
            catch (Exception $e) {
                $message = $e->getMessage();
            }
        }

        $this->twig->display('login.twig', array('title' => 'Login', 'is_logged' => UserModel::isLogged(),
            'login' => $login, 'message' => $message));
    }
}