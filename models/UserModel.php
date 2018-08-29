<?php

/**
 * Created by PhpStorm.
 * User: Vitaly
 * Date: 17.07.2018
 * Time: 15:27
 */
class UserModel
{
    public $id;
    public $name;
    public $email;
    public $login;
    public $status;
    protected $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function read($id)
    {
        $statement = $this->db->prepare('SELECT `id`, `name`, `email`, `login` FROM `user` WHERE `id` = :id');
        $statement->execute(array(':id' => $id));
        if ($row = $statement->fetch()) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->login = $row['login'];
        }
    }

    public static function login($db, $login, $password)
    {
        $statement = $db->prepare('SELECT `id`, `hash` FROM `user` WHERE `login` = :login');
        $statement->execute(array(':login' => $login));
        $rows = $statement->fetchAll();
        // Check if there is only one row in the result set.
        if (count($rows) == 1) {
            // Secure password storage & verification.
            if (password_verify($password, $rows[0]['hash'])) {
                $_SESSION['authorized_user_id'] = $rows[0]['id'];
            } else {
                throw new Exception('Wrong password.');
            }
        } else {
            throw new Exception("User $login not found.");
        }
    }

    public static function logout()
    {
        unset($_SESSION["authorized_user_id"]);
    }

    public static function isLogged()
    {
        return isset($_SESSION["authorized_user_id"]);
    }

    public static function listAll($db)
    {
        $statement = $db->query('SELECT `id`, `name`, `email`, `login` FROM `user`');
        return $statement->fetchAll();
    }
}