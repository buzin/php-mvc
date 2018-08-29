<?php

/**
 * Created by PhpStorm.
 * User: Vitaly
 * Date: 17.07.2018
 * Time: 13:27
 */

require_once 'BaseController.php';
require_once 'models/TaskModel.php';
require_once 'models/ImageModel.php';
require_once 'models/UserModel.php';

class TaskController extends BaseController
{
    const SORT_MODES = ['name', 'email', 'status'];
    const PAGE_SIZE = 3;

    public function index()
    {
        // Sort mode.
        $sort = filter_input(INPUT_GET, "sort", FILTER_SANITIZE_STRING);
        $order_by = null;
        // Check if sort mode is correct.
        if (in_array($sort, self::SORT_MODES)) {
            $order_by = $sort;
        }

        // Page navigation.
        $page = (isset($_GET['page'])) ? filter_input(INPUT_GET, "page", FILTER_SANITIZE_NUMBER_INT) : 1;
        $total = ceil(TaskModel::count($this->pdo)/self::PAGE_SIZE);
        // Adjust pager.
        $pager_start = 1;
        $pager_size = 3;
        if ($total < $pager_size) {
            $pager_size = $total;
        } else {
            if ($page > floor($pager_size/2)) {
                $pager_start = $page - floor($pager_size/2);
            }
            if ($pager_start + $pager_size > $total) {
                $pager_start = $total - $pager_size + 1;
            }
        }

        $tasks = TaskModel::getList($this->pdo, $order_by, self::PAGE_SIZE, ($page - 1)*self::PAGE_SIZE);
        $this->twig->display('index.twig', array('title' => 'Task List', 'tasks' => $tasks, 'sort' => $sort,
            'page' => $page, 'total' => $total, 'pager_start' => $pager_start, 'pager_size' => $pager_size));
    }

    public function add()
    {
        $userId = 0;
        $text = '';
        $status = 0;
        $message = '';
        if (isset($_POST['continue'])) {
            $userId = filter_input(INPUT_POST, "user-id", FILTER_SANITIZE_NUMBER_INT);
            $text = filter_input(INPUT_POST, "text", FILTER_SANITIZE_STRING);
            $status = filter_input(INPUT_POST, "status", FILTER_SANITIZE_NUMBER_INT);

            // Upload image.
            $image = new ImageModel($this->pdo);
            try {
                $image->upload('image');

                $task = new TaskModel($this->pdo);
                if ($id = $task->create($userId, $image->id, $status, $text)) {
                    // Go to task list.
                    header('Location: /');
                    exit();
                }
            }
            catch (Exception $e) {
                $message = $e->getMessage();
            }
        }
        $users = UserModel::listAll($this->pdo);
        $this->twig->display('add.twig', array('title' => 'Add Task',
            'user_id' => $userId, 'text' => $text, 'status' => $status,
            'message' => $message, 'users' => $users,
            'script' => 'assets/js/add.js'));
    }

    public function edit()
    {
        $id = filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);
        $task = new TaskModel($this->pdo, $id);

        if (isset($_POST['update']) && $task->id && UserModel::isLogged()) {
            $text = filter_input(INPUT_POST, "text", FILTER_SANITIZE_STRING);
            $status = filter_input(INPUT_POST, "status", FILTER_SANITIZE_NUMBER_INT);
            $task->update($text, $status);
        }

        $info = $task->getInfo($id);
        $this->twig->display('edit.twig', array('title' => 'Edit Task', 'info' => $info,
            'is_logged' => UserModel::isLogged(), 'uploads' => ImageModel::UPLOADS_DIR));
    }
}