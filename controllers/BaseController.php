<?php

require_once 'vendor/autoload.php';

/**
 * Created by PhpStorm.
 * User: Vitaly
 * Date: 17.07.2018
 * Time: 13:24
 */
class BaseController
{
    protected $pdo;
    protected $twig;

    public function __construct($config)
    {
        // Start session variables storage.
        session_start();

        // Create DB connection.
        $this->pdo = new PDO(
            'mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['name'] . ';charset=utf8mb4',
            $config['db']['user'], $config['db']['password'],
            array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 10,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ));

        // Prepare Twig.
        $loader = new Twig_Loader_Filesystem('views');
        $this->twig = new Twig_Environment($loader, array(
            'autoescape' => 'html',
            'cache' => 'views/cache',
        ));
    }
}