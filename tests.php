<?php

// Создание комментария с корректными параметрами
$comment = array(
    'text' => 'Текст комментария',
    'parent_comment_id' => 123, // Идентификатор родительского комментария (если есть)
    'user_id' => 456 // Идентификатор пользователя
);
$ch = curl_init('http://example.com/comment');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $comment);
$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($status == 201) {
    // Код 201 (Created) - комментарий успешно создан, получаем идентификатор созданного комментария из ответа
    $comment_id = json_decode($response)->comment_id;
    echo "Комментарий создан, идентификатор комментария: " . $comment_id;
} else {
    // Ошибка при создании комментария
    echo "Ошибка при создании комментария, код ошибки: " . $status;
}

// Создание комментария без текста
$comment = array(
    'parent_comment_id' => 123, // Идентификатор родительского комментария (если есть)
    'user_id' => 456 // Идентификатор пользователя
);
$ch = curl_init('http://example.com/comment');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $comment);
$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($status == 400) {
    // Код 400 (Bad Request) - ошибка при создании комментария без текста
    echo "Ошибка при создании комментария без текста";
} else {
    // Комментарий создан, но не должен быть создан без текста
    echo "Комментарий создан, но не должен быть создан без текста";
}

// Создание комментария без идентификатора пользователя
$comment = array(
    'text' => 'Текст комментария',
    'parent_comment_id' => 123 // Идентификатор родительского комментария (если есть)
);
$ch = curl_init('http://example.com/comment');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $comment);
$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
