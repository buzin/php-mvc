<?php
/**
 * Created by PhpStorm.
 * User: Vitaly
 * Date: 17.07.2018
 * Time: 15:33
 */

require_once 'controllers/UserController.php';
require_once 'config.php';

$tasks = new UserController($config);
$tasks->login();