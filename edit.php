<?php
/**
 * Created by PhpStorm.
 * User: Vitaly
 * Date: 17.07.2018
 * Time: 15:59
 */

require_once 'controllers/TaskController.php';
require_once 'config.php';

$tasks = new TaskController($config);
$tasks->edit();