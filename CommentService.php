<?php

include_once "vendor/autoload.php"; // подключаем библиотеку Swagger-PHP

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
class CommentService {

    // Метод для создания нового комментария
    public function createComment($text, $parentId, $userId) {
        // TODO: реализовать создание нового комментария в базе данных и возврат его идентификатора
    }

    // Метод для редактирования существующего комментария
    public function editComment($commentId, $text, $userId) {
        // TODO: реализовать редактирование комментария в базе данных и возврат измененного комментария
    }

    // Метод для получения информации о комментарии
    public function getComment($commentId) {
        // TODO: реализовать получение информации о комментарии из базы данных и возврат ее в виде объекта
    }

    // Метод для удаления комментария
    public function deleteComment($commentId) {
        // TODO: реализовать удаление комментария из базы данных и возврат сообщения об успешном удалении
    }

    // Метод для получения всех дочерних комментариев к заданному комментарию
    private function getChildrenComments($commentId) {
        // TODO: реализовать получение дочерних комментариев из базы данных и возврат их в виде массива объектов
    }

    // Метод для получения полного дерева комментариев для заданного комментария
    public function getCommentTree($commentId) {
        // Получаем информацию о корневом комментарии
        $rootComment = $this->getComment($commentId);

        // Если комментарий не найден, возвращаем null
        if (!$rootComment) {
            return null;
        }

        // Получаем дочерние комментарии
        $children = $this->getChildrenComments($commentId);

        // Рекурсивно получаем дерево комментариев для каждого дочернего комментария
        $tree = array();
        foreach ($children as $child) {
            $subtree = $this->getCommentTree($child->id);
            if ($subtree) {
                $tree[] = $subtree;
            }
        }

        // Добавляем дочерние комментарии в информацию о корневом комментарии и возвращаем ее
        $rootComment->children = $tree;
        return $rootComment;
    }

}