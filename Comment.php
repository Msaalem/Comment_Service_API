<?php

namespace CommentServiceAPI;

use CommentServiceAPI\MySQLDatabase;

class Comment
{
    private MySQLDatabase $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Создание нового комментария
    public function createComment($text, $parent_id, $user_id)
    {
        // Экранируем сообщение, чтобы избежать SQL injection
        $text = $this->conn->escape_string($text);
        $parent_id = $this->conn->escape_string($parent_id);
        $user_id = $this->conn->escape_string($user_id);

        // Собираем строку SQL запроса
        $query = "INSERT INTO comments (text, parent_id, user_id) VALUES ('$text', '$parent_id', '$user_id')";

        // Выполняем запрос
        if ($this->conn->query($query)) {
            // Возвращаем ID созданного комментария
            return $this->conn->insert_id();
        } else {
            die("Ошибка создания комментария: " . $this->conn->error);
        }
    }

    // Редактирование комментария
    public function editComment($id, $text, $user_id)
    {
        // Проверка, существует ли комментарий с заданным id
        $query = "SELECT * FROM comments WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        $num_rows = $stmt->rowCount();
        if ($num_rows == 0) {
            return false;
        }

        // Проверка, имеет ли пользователь право редактировать комментарий
        $query = "SELECT * FROM comments WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id, $user_id]);
        $num_rows = $stmt->rowCount();
        if ($num_rows == 0) {
            return false;
        }

        // Обновление комментария
        $query = "UPDATE comments SET text = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$text, $id]);
        return true;
    }

    // Получение информации о комментарии
    public function getComment($id)
    {
        // SQL-запрос для получения информации о комментарии
        $query = "SELECT c.id, c.text, c.created_at, c.user_id, c.parent_id, u.username 
              FROM comments c 
              LEFT JOIN users u ON c.user_id = u.id 
              WHERE c.id = :id";

        // Подготовка SQL-запроса
        $stmt = $this->conn->prepare($query);

        // Привязка параметров
        $stmt->bindParam(":id", $id);

        // Выполнение запроса
        $stmt->execute();

        // Получение результата
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Если комментарий не найден, вернуть null
        if (!$result) {
            return null;
        }

        // Вернуть информацию о комментарии
        return array(
            "id" => $result["id"],
            "text" => $result["text"],
            "created_at" => $result["created_at"],
            "user_id" => $result["user_id"],
            "username" => $result["username"],
            "parent_id" => $result["parent_id"]
        );
    }

    // Удаление комментария
    public function deleteComment($id)
    {
        // Проверяем, существует ли комментарий с заданным идентификатором
        $query = "SELECT * FROM comments WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $num = $stmt->rowCount();

        if ($num == 0) {
            // Если комментария не существует, возвращаем ошибку
            return array("error" => "Comment not found.");
        } else {
            // Получаем информацию о комментарии
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $parent_id = $row['parent_id'];
            $comment_user_id = $row['user_id'];

            // Проверяем, может ли пользователь удалить этот комментарий
            if ($comment_user_id != $user_id) {
                // Если пользователь не является автором комментария, возвращаем ошибку
                return array("error" => "You are not authorized to delete this comment.");
            } else {
                // Удаляем комментарий
                $query = "DELETE FROM comments WHERE id = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(1, $id);
                $stmt->execute();

                // Если комментарий имел родительский комментарий, обновляем его количество дочерних комментариев
                if (!empty($parent_id)) {
                    $query = "UPDATE comments SET child_count = child_count - 1 WHERE id = ?";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindParam(1, $parent_id);
                    $stmt->execute();
                }

                return array("message" => "Comment deleted.");
            }
        }
    }

    // Получение дочерних комментариев
    public function getChildComments($parent_id)
    {
        // Запрос на получение дочерних комментариев
        $query = "SELECT * FROM comments WHERE parent_id = :parent_id ORDER BY created_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":parent_id", $parent_id);
        $stmt->execute();
        $num = $stmt->rowCount();

        // Проверка наличия дочерних комментариев
        if ($num > 0) {
            $comments_arr = array();

            // Получение всех дочерних комментариев
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                // Создание объекта комментария и добавление его в массив
                $comment_item = array(
                    "id" => $id,
                    "text" => $text,
                    "parent_id" => $parent_id,
                    "user_id" => $user_id,
                    "created_at" => $created_at
                );
                array_push($comments_arr, $comment_item);
            }

            return $comments_arr;
        } else {
            return array();
        }
    }
}