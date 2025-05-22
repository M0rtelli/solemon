<?php
session_start();

if (!isset($_SESSION['user_logged'])) {
    header('Location: login.php');
    exit;
}

$conn = new mysqli("localhost", "admin", "pR0fU7tR1p", "solemon_site");
$conn->set_charset("utf8mb4");

// Получаем данные пользователя
$stmt = $conn->prepare("
    SELECT u.*, COUNT(o.id) as orders_count 
    FROM users u
    LEFT JOIN orders o ON o.user_id = u.id
    WHERE u.id = ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль | Solemon</title>
    <style>
        /* Стили для профиля */
    </style>
</head>
<body>
    <div class="profile-container">
        <h1>Ваш профиль</h1>
        
        <div class="user-info">
            <p><strong>Telegram:</strong> <?= htmlspecialchars($user['username'] ?? 'Не указан') ?></p>
            <p><strong>Имя:</strong> <?= htmlspecialchars($user['full_name'] ?? 'Не указано') ?></p>
            <p><strong>Телефон:</strong> <?= htmlspecialchars($user['phone'] ?? 'Не указан') ?></p>
            <p><strong>Заказов:</strong> <?= $user['orders_count'] ?></p>
        </div>
        
        <a href="logout.php" class="logout-btn">Выйти</a>
    </div>
</body>
</html>