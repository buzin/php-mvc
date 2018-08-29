<?php

require_once 'controllers/TaskController.php';
require_once 'config.php';

$tasks = new TaskController($config);
$tasks->index();