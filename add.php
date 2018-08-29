<?php
/**
 * Created by PhpStorm.
 * User: Vitaly
 * Date: 17.07.2018
 * Time: 14:51
 */

require_once 'controllers/TaskController.php';
require_once 'config.php';

$tasks = new TaskController($config);
$tasks->add();