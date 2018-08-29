<?php

/**
 * Created by PhpStorm.
 * User: Vitaly
 * Date: 16.07.2018
 * Time: 13:43
 */
class TaskModel
{
    public $id;
    public $userId;
    public $imageId;
    public $status;
    public $text;
    protected $db;

    public function __construct(PDO $db, $id = null)
    {
        $this->db = $db;
        if ($id) {
            $this->read($id);
        }
    }

    public function create($userId, $imageId, $status, $text)
    {
        $statement = $this->db->prepare('
            INSERT INTO `task` (`user_id`, `image_id`, `status`, `text`) 
            VALUES (:user_id, :image_id, :status, :text)');
        $statement->execute(array('user_id' => $userId, ':image_id' => $imageId, ':status' => $status,
            ':text' => $text));
        $this->userId = $userId;
        $this->imageId = $imageId;
        $this->status = $status;
        $this->text = $text;
        return $this->id = $this->db->lastInsertId();
    }

    public function read($id)
    {
        try {
            $statement = $this->db->prepare('
            SELECT `id`, `user_id`, `image_id`, `status`, `text` FROM `task` WHERE `id` = :id');
            $statement->execute(array(':id' => $id));
            if ($row = $statement->fetch()) {
                $this->id = $row['id'];
                $this->userId = $row['user_id'];
                $this->imageId = $row['image_id'];
                $this->status = $row['status'];
                $this->text = $row['text'];
            }
        } catch (Exception $e) {
            $this->id = null;
        }
    }

    public function update($text, $status)
    {
        if ($this->id) {
            $statement = $this->db->prepare('UPDATE `task` SET `text` = :text, `status` = :status WHERE id = :id');
            $statement->execute(array(':id' => $this->id, ':text' => $text, ':status' => $status));
        }
    }

    public function getInfo($id)
    {
        $statement = $this->db->prepare('
            SELECT t.`id`, t.`status`, t.`text`, u.`name`, u.`email`, i.`filename`, i.`width`, i.`height` 
            FROM `task` t
                LEFT JOIN `user` u ON t.user_id = u.id
                LEFT JOIN `image` i ON t.image_id = i.id
            WHERE t.`id` = :id');
        $statement->execute(array(':id' => $id));
        return $statement->fetch();
    }

    public static function count($db)
    {
        return $db->query('SELECT COUNT(*) FROM `task`')->fetchColumn();
    }

    public static function getList($db, $order_by = null, $limit = null, $offset = null)
    {
        $statement = $db->prepare('
            SELECT t.`id`, `user_id`, u.name, u.email, `image_id`, `status`, `text` 
            FROM `task` t
              LEFT JOIN `user` u ON t.user_id = u.id' .
            (!is_null($order_by) ? ' ORDER BY ' . "`" . str_replace("`", "``", $order_by) . "`" : '') .
            (!is_null($limit) ? ' LIMIT :limit' : '') .
            (!is_null($offset) ? ' OFFSET :offset' : '')
        );
        if (!is_null($limit)) {
            $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
        if (!is_null($offset)) {
            $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        $statement->execute();

        return $statement->fetchAll();
    }
}