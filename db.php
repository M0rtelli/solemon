<?php
// Настройки подключения к базе данных
define('DB_HOST', 'MySQL-8.0');
define('DB_NAME', 'solemon');
define('DB_USER', 'solemon_site');
define('DB_PASS', 'solemon2281488');

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