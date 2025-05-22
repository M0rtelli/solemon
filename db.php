<?php
// Настройки подключения к базе данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'solemon_site');
define('DB_USER', 'admin');
define('DB_PASS', 'pR0fU7tR1p');


try {
    // Создаем подключение через PDO
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    // Логирование ошибки
    error_log("Database connection failed: " . $e->getMessage());
    
    // Вывод сообщения для пользователя
    die("Ошибка подключения к базе данных. Пожалуйста, попробуйте позже.");
}

// Дополнительные настройки сессии (если нужно)
session_start();
?>