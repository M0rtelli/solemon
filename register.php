<?php
session_start();
require 'db.php'; // Подключение к БД

// Генерация 6-значного кода
$code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
$expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

// Сохранение в БД
$stmt = $pdo->prepare("INSERT INTO users (registration_code, code_expires) VALUES (?, ?)");
$stmt->execute([$code, $expires]);
$userId = $pdo->lastInsertId();

$_SESSION['reg_user_id'] = $userId;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Регистрация</title>
    <link rel="stylesheet" href="style/register.css">
</head>
<body>
    <h1>Ваш код для привязки Telegram:</h1>
    <div class="code-box"><?= $code ?></div>
    <p>Перейдите в @YourBot и введите этот код</p>
    <div id="status"></div>

    <script>
        // Проверка статуса каждые 5 секунд
        setInterval(async () => {
            const response = await fetch('check_status.php');
            const data = await response.json();
            
            if(data.telegram_id) {
                window.location.href = 'profile.php';
            }
        }, 5000);
    </script>
</body>
</html>