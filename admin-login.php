<?php
// Старт сессии ДО любого вывода
session_start();

// Инициализация переменных
$error = '';
$username = '';

// Генерация CSRF-токена при каждой загрузке страницы
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Обработка POST-запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка CSRF-токена
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Ошибка безопасности. Пожалуйста, обновите страницу.";
    } else {
        // Фильтрация и валидация ввода
        $username = htmlspecialchars($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Подключение к БД
        $conn = new mysqli('MySQL-8.0', 'solemon_site', 'solemon2281488', 'solemon');
        
        if ($conn->connect_error) {
            die("Ошибка подключения: " . $conn->connect_error);
        }
        
        // Поиск пользователя с подготовленным запросом
        $stmt = $conn->prepare("SELECT id, password_hash FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            // Верификация пароля
            if (password_verify($password, $user['password_hash'])) {
                // Успешная аутентификация
                $_SESSION['admin_logged'] = true;
                $_SESSION['user_id'] = $user['id'];
                
                // Регенерация ID сессии
                session_regenerate_id(true);
                
                // Новый CSRF-токен
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                
                header("Location: admin.php");
                exit();
            }
        }
        
        // Ошибка аутентификации
        $error = "Неверные учетные данные";
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация - Vape Shop</title>
    <link rel="stylesheet" href="style/admin-login.css">
</head>
<body>
    <div class="login-box">
        <h1 class="form-title">🔐 Авторизация</h1>
        
        <?php if($error): ?>
            <div class="error-message">
                <svg viewBox="0 0 24 24" width="20" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            
            <div class="input-group">
                <input type="text" 
                       name="username" 
                       placeholder="Логин"
                       value="<?= htmlspecialchars($username) ?>"
                       required>
            </div>
            
            <div class="input-group">
                <input type="password" 
                       name="password" 
                       placeholder="Пароль"
                       required>
            </div>
            
            <button type="submit">Войти в систему</button>
        </form>
    </div>
</body>
</html>