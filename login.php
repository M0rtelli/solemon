<?php
session_start();
require 'db.php';

// Обработка данных от Telegram
if (isset($_GET['hash'])) {
    $auth_data = $_GET;
    $check_hash = $auth_data['hash'];
    unset($auth_data['hash']);

    // Формируем данные для проверки
    $data_check_arr = [];
    foreach ($auth_data as $key => $value) {
        $data_check_arr[] = $key . '=' . $value;
    }
    sort($data_check_arr);
    $data_check_string = implode("\n", $data_check_arr);

    // Проверяем подпись
    $secret_key = hash('sha256', 'YOUR_BOT_TOKEN', true);
    $hash = hash_hmac('sha256', $data_check_string, $secret_key);

    if ($hash !== $check_hash) {
        die('Ошибка проверки подлинности данных');
    }

    // Проверяем срок действия авторизации (не старше 1 дня)
    if ((time() - $auth_data['auth_date']) > 86400) {
        die('Данные авторизации устарели');
    }

    // Поиск/создание пользователя
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE telegram_id = ?");
        $stmt->execute([$auth_data['id']]);
        $user = $stmt->fetch();

        if (!$user) {
            $stmt = $pdo->prepare("INSERT INTO users SET 
                telegram_id = ?,
                first_name = ?,
                last_name = ?,
                username = ?,
                photo_url = ?,
                auth_date = ?
            ");
            
            $stmt->execute([
                $auth_data['id'],
                $auth_data['first_name'] ?? '',
                $auth_data['last_name'] ?? '',
                $auth_data['username'] ?? '',
                $auth_data['photo_url'] ?? '',
                $auth_data['auth_date']
            ]);
            
            $user_id = $pdo->lastInsertId();
        } else {
            $user_id = $user['id'];
        }

        $_SESSION['user_id'] = $user_id;
        header('Location: profile.php');
        exit;

    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        die('Ошибка при работе с базой данных');
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация - VapeShop</title>
    <link rel="stylesheet" href="style/login.css">
</head>
<body>
    <div class="auth-container">
        <h1>Авторизация</h1>
        <p>Для входа используйте свой аккаунт Telegram</p>
        
        <script async 
                src="https://telegram.org/js/telegram-widget.js?22" 
                data-telegram-login="Solemon_Shop_Bot" 
                data-size="large" 
                data-radius="10" 
                data-auth-url="login.php" 
                data-request-access="write">
        </script>

        <p class="terms">
            Нажимая кнопку, вы соглашаетесь с<br>
            <a href="/privacy">Политикой конфиденциальности</a>
        </p>
    </div>
</body>
</html>