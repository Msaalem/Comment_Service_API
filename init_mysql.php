<?php

// Подключение к серверу MySQL
$servername = "localhost";
$username = "username";
$password = "password";

try {
    $conn = new PDO("mysql:host=$servername;dbname=mydb", $username, $password);
    // Установка режима выброса исключений для обработки ошибок
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Создание таблицы users
    $conn->exec("CREATE TABLE users (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(30) NOT NULL,
        email VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Создание таблицы comments
    $conn->exec("CREATE TABLE comments (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        text TEXT NOT NULL,
        user_id INT(6) UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    echo "Tables created successfully";
} catch(PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}

// Закрытие соединения с сервером MySQL
$conn = null;
