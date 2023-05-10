<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Info(title="API для работы с комментариями", version="1.0.0")
 */

/**
 * @OA\Schema(
 *   schema="Comment",
 *   type="object",
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="text", type="string"),
 *   @OA\Property(property="parent_id", type="integer"),
 *   @OA\Property(property="user_id", type="integer"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

/**
 * @OA\Parameter(
 *     name="text",
 *     in="query",
 *     description="Текст комментария",
 *     required=true,
 *     @OA\Schema(type="string")
 * )
 *
 * @OA\Parameter(
 *     name="parent_id",
 *     in="query",
 *     description="Идентификатор родительского комментария",
 *     required=false,
 *     @OA\Schema(type="integer")
 * )
 *
 * @OA\Parameter(
 *     name="user_id",
 *     in="query",
 *     description="Идентификатор пользователя",
 *     required=true,
 *     @OA\Schema(type="integer")
 * )
 *
 * @OA\Response(
 *     response=200,
 *     description="Идентификатор созданного комментария",
 *     @OA\JsonContent(
 *         @OA\Property(property="id", type="integer")
 *     )
 * )
 */

/**
 * @OA\Post(
 *     path="/comment",
 *     summary="Создание нового комментария",
 *     @OA\RequestBody(
 *         description="Данные для создания комментария",
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="text", type="string"),
 *             @OA\Property(property="parent_id", type="integer"),
 *             @OA\Property(property="user_id", type="integer")
 *         )
 *     ),
 *     @OA\Response(response="200", description="Идентификатор созданного комментария"),
 *     @OA\Response(response="400", description="Неверные данные для создания комментария")
 * )
 */
class RequestHandler {

    function isValidUser($username, $password) {
        $dbhost = 'localhost';
        $dbuser = 'yourusername';
        $dbpass = 'yourpassword';
        $dbname = 'yourdatabasename';

        // Подключение к базе данных
        $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
        if (!$conn) {
            die('Ошибка подключения к базе данных: ' . mysqli_connect_error());
        }

        // Запрос к базе данных для получения пользователя по имени
        $sql = "SELECT id, password FROM users WHERE username = '" . mysqli_real_escape_string($conn, $username) . "'";
        $result = mysqli_query($conn, $sql);

        // Проверка наличия записи с данным именем пользователя
        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            $hashedPassword = $row['password'];

            // Проверка правильности пароля
            if (password_verify($password, $hashedPassword)) {
                // Пароль верный
                $userId = $row['id'];
                mysqli_close($conn);
                return $userId;
            } else {
                // Неверный пароль
                mysqli_close($conn);
                return false;
            }
        } else {
            // Не найдено записей с данным именем пользователя
            mysqli_close($conn);
            return false;
        }
    }

    function isValidToken($token) {
        // Раскодирование токена из формата base64
        $tokenData = json_decode(base64_decode($token), true);

        // Проверка срока действия токена
        if (time() > $tokenData['exp']) {
            return false;
        }

        // Поиск токена в базе данных
        $conn = mysqli_connect("localhost", "username", "password", "database_name");
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        $userId = mysqli_real_escape_string($conn, $tokenData['sub']);
        $token = mysqli_real_escape_string($conn, $token);

        $sql = "SELECT * FROM tokens WHERE user_id = '$userId' AND token = '$token'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) == 1) {
            return true;
        } else {
            return false;
        }

        mysqli_close($conn);
    }

    public function handleRequest() {

        $method = $_SERVER['REQUEST_METHOD'];
        $token = null;

        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
            list($jwt) = sscanf($authHeader, 'Bearer %s');
            if ($jwt) {
                $token = json_decode(base64_decode($jwt), true);
            }
        }

        if (!$token || !$this->isValidToken($token)) {
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Basic realm="Restricted area"');
            echo 'Требуется авторизация';
            return;
        }

        // Аутентификация пользователя
        $username = $_POST['username'];
        $password = $_POST['password'];
        $userId = $this->isValidUser($username, $password);

        if ($userId) {

            // Создание токена
            $token = array(
                'sub' => $userId, // идентификатор пользователя
                'exp' => time() + 3600, // время истечения срока действия токена (1 час)
                'iss' => 'yourdomain.com', // имя вашего сайта
                'aud' => 'yourdomain.com', // имя вашего сайта
            );

            // Кодирование токена в формат base64
            $jwt = base64_encode(json_encode($token));

            // Сохранение токена в базе данных
            $conn = mysqli_connect("localhost", "username", "password", "database_name");
            if (!$conn) {
                die("Connection failed: " . mysqli_connect_error());
            }

            $sql = "INSERT INTO tokens (user_id, token) VALUES ('$userId', '$jwt')";

            if (mysqli_query($conn, $sql)) {
                // Выдача токена
                header('HTTP/1.0 200 OK');
                header('Content-Type: application/json');
                echo json_encode(array('token' => $jwt));
            } else {
                // Ошибка при сохранении токена
                header('HTTP/1.0 500 Internal Server Error');
                echo 'Ошибка при сохранении токена';
            }

            mysqli_close($conn);

        } else {
            // Неверное имя пользователя или пароль
            header('HTTP/1.0 401 Unauthorized');
            echo 'Неверное имя пользователя или пароль';
        }

        switch($method) {
            case 'GET':
                echo 'GET';

                /**
                 * @OA\Get(
                 *     path="/comment/{id}",
                 *     summary="Get comment by id",
                 *     tags={"Comment"},
                 *     @OA\Parameter(
                 *         name="id",
                 *         in="path",
                 *         required=true,
                 *         description="ID of comment to return",
                 *         @OA\Schema(
                 *             type="integer",
                 *             format="int64",
                 *             minimum=1
                 *         )
                 *     ),
                 *     @OA\Response(
                 *         response="200",
                 *         description="Comment response",
                 *         @OA\JsonContent(ref="#/components/schemas/Comment")
                 *     ),
                 *     @OA\Response(
                 *         response="404",
                 *         description="Comment not found"
                 *     ),
                 *     @OA\Response(
                 *         response="500",
                 *         description="Internal Server Error"
                 *     )
                 * )
                 */
                // Проверяем, существует ли комментарий с данным ID
                $comment = $this->db->query("SELECT * FROM comments WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
                if (!$comment) {
                    // Если комментарий не найден, возвращаем HTTP-ответ с кодом 404
                    http_response_code(404);
                    echo json_encode(["message" => "Comment not found"]);
                    return;
                }

                // Формируем ответ с информацией о комментарии и возвращаем его в формате JSON
                $response = [
                    "id" => $comment["id"],
                    "text" => $comment["text"],
                    "parent_id" => $comment["parent_id"],
                    "user_id" => $comment["user_id"],
                    "created_at" => $comment["created_at"]
                ];
                echo json_encode($response);

                break;
            case 'POST':
                echo 'POST';

                /**
                 * @OA\Post(
                 *     path="/comment",
                 *     summary="Create new comment",
                 *     tags={"Comment"},
                 *     @OA\RequestBody(
                 *         required=true,
                 *         description="Comment data",
                 *         @OA\JsonContent(
                 *             required={"text", "user_id"},
                 *             @OA\Property(
                 *                 property="text",
                 *                 type="string",
                 *                 example="This is a new comment"
                 *             ),
                 *             @OA\Property(
                 *                 property="user_id",
                 *                 type="integer",
                 *                 example=1
                 *             ),
                 *             @OA\Property(
                 *                 property="parent_id",
                 *                 type="integer",
                 *                 example=2
                 *             )
                 *         )
                 *     ),
                 *     @OA\Response(
                 *         response="200",
                 *         description="Comment created successfully",
                 *         @OA\JsonContent(
                 *             type="object",
                 *             @OA\Property(
                 *                 property="id",
                 *                 type="integer",
                 *                 example=1
                 *             )
                 *         )
                 *     ),
                 *     @OA\Response(
                 *         response="400",
                 *         description="Bad Request"
                 *     ),
                 *     @OA\Response(
                 *         response="500",
                 *         description="Internal Server Error"
                 *     )
                 * )
                 */
                // Получаем данные комментария из запроса
                $data = json_decode(file_get_contents("php://input"), true);

                // Проверяем, что переданы все обязательные поля
                if (!isset($data["text"]) || !isset($data["user_id"])) {
                    // Если не переданы обязательные поля, возвращаем HTTP-ответ с кодом 400
                    http_response_code(400);
                    echo json_encode(["message" => "Bad Request"]);
                    return;
                }

                // Добавляем комментарий в базу данных
                $parentId = isset($data["parent_id"]) ? $data["parent_id"] : 0;
                $this->conn->query("INSERT INTO comments (text, parent_id, user_id) VALUES ('{$data["text"]}', $parentId, {$data["user_id"]})");

                // Получаем ID созданного комментария
                $commentId = $this->conn->lastInsertId();

                // Возвращаем HTTP-ответ с кодом 200 и ID созданного комментария
                http_response_code(200);
                echo json_encode(["id" => $commentId]);

                break;
            case 'PUT':
                echo 'PUT';

                /**
                 * @OA\Put(
                 *     path="/comment/{id}",
                 *     summary="Update comment by id",
                 *     tags={"Comment"},
                 *     @OA\Parameter(
                 *         name="id",
                 *         in="path",
                 *         required=true,
                 *         description="ID of comment to update",
                 *         @OA\Schema(
                 *             type="integer",
                 *             format="int64",
                 *             minimum=1
                 *         )
                 *     ),
                 *     @OA\RequestBody(
                 *         required=true,
                 *         @OA\JsonContent(
                 *             @OA\Property(
                 *                 property="text",
                 *                 type="string",
                 *                 example="Updated comment text"
                 *             ),
                 *             @OA\Property(
                 *                 property="user_id",
                 *                 type="integer",
                 *                 example="1"
                 *             )
                 *         )
                 *     ),
                 *     @OA\Response(
                 *         response="200",
                 *         description="Comment updated successfully",
                 *         @OA\JsonContent(
                 *             type="object",
                 *             @OA\Property(
                 *                 property="id",
                 *                 type="integer",
                 *                 example="1"
                 *             ),
                 *             @OA\Property(
                 *                 property="text",
                 *                 type="string",
                 *                 example="Updated comment text"
                 *             ),
                 *             @OA\Property(
                 *                 property="created_at",
                 *                 type="string",
                 *                 example="2022-05-07 15:30:00"
                 *             ),
                 *             @OA\Property(
                 *                 property="user_id",
                 *                 type="integer",
                 *                 example="1"
                 *             ),
                 *             @OA\Property(
                 *                 property="parent_id",
                 *                 type="integer",
                 *                 example="2"
                 *             )
                 *         )
                 *     ),
                 *     @OA\Response(
                 *         response="400",
                 *         description="Bad Request"
                 *     ),
                 *     @OA\Response(
                 *         response="404",
                 *         description="Comment not found"
                 *     ),
                 *     @OA\Response(
                 *         response="500",
                 *         description="Internal Server Error"
                 *     )
                 * )
                 */

                // Получаем данные из тела запроса
                $data = json_decode(file_get_contents("php://input"), true);

                // Проверяем, существует ли комментарий с данным ID
                $comment = $this->db->query("SELECT * FROM comments WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
                if (!$comment) {
                    // Если комментарий не найден, возвращаем HTTP-ответ с кодом 404
                    http_response_code(404);
                    echo json_encode(["message" => "Comment not found"]);
                    return;
                }

                // Проверяем, что переданы все обязательные поля
                if (!isset($data["text"]) || !isset($data["user_id"])) {
                    // Если не переданы обязательные поля, возвращаем HTTP-ответ с кодом 400
                    http_response_code(400);
                    echo json_encode(["message" => "Bad Request"]);
                    return;
                }

                // Обновляем комментарий в базе данных
                $this->db->query("UPDATE comments SET text = '{$data["text"]}', user_id = {$data["user_id"]} WHERE id = $id");

                // Получаем обновленный комментарий из базы данных
                $updatedComment = $this->db->query("SELECT * FROM comments WHERE id = $id")->fetch(PDO::FETCH_ASSOC);

                // Проверяем, был ли найден комментарий
                if (!$updatedComment) {
                    // Если комментарий не найден, возвращаем HTTP-ответ с кодом 404
                    http_response_code(404);
                    echo json_encode(["message" => "Comment not found"]);
                    return;
                }

                // Возвращаем HTTP-ответ с кодом 200 и информацией об обновленном комментарии
                http_response_code(200);
                echo json_encode($updatedComment);

                break;
            case 'DELETE':
                echo 'DELETE';
                /**
                 * @OA\Delete(
                 *     path="/comment/{id}",
                 *     summary="Delete comment by id",
                 *     tags={"Comment"},
                 *     @OA\Parameter(
                 *         name="id",
                 *         in="path",
                 *         required=true,
                 *         description="ID of comment to delete",
                 *         @OA\Schema(
                 *             type="integer",
                 *             format="int64",
                 *             minimum=1
                 *         )
                 *     ),
                 *     @OA\Parameter(
                 *         name="token",
                 *         in="header",
                 *         required=true,
                 *         description="User token"
                 *     ),
                 *     @OA\Response(
                 *         response="200",
                 *         description="Comment deleted successfully",
                 *         @OA\JsonContent(
                 *             type="object",
                 *             @OA\Property(
                 *                 property="message",
                 *                 type="string",
                 *                 example="Comment deleted successfully"
                 *             )
                 *         )
                 *     ),
                 *     @OA\Response(
                 *         response="401",
                 *         description="Unauthorized"
                 *     ),
                 *     @OA\Response(
                 *         response="404",
                 *         description="Comment not found"
                 *     ),
                 *     @OA\Response(
                 *         response="500",
                 *         description="Internal Server Error"
                 *     )
                 * )
                 */

                // Проверяем, существует ли комментарий с данным ID
                $comment = $this->conn->query("SELECT * FROM comments WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
                if (!$comment) {
                    // Если комментарий не найден, возвращаем HTTP-ответ с кодом 404
                    http_response_code(404);
                    echo json_encode(["message" => "Comment not found"]);
                    return;
                }

                // Удаляем комментарий из базы данных
                $this->conn->query("DELETE FROM comments WHERE id = $id");

                // Возвращаем HTTP-ответ с кодом 200 и сообщением об успешном удалении комментария
                http_response_code(200);
                echo json_encode(["message" => "Comment deleted successfully"]);

                break;
            default:
                // если метод запроса не соответствует ни одному из перечисленных, выдать ошибку
                http_response_code(405);
                echo 'Метод не поддерживается';
                break;
        }
    }
}
