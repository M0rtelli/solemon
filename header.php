<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'VapeShop' ?></title>
    <link rel="stylesheet" href="style/header.css">
</head>
<body>
    <header class="main-header">
        <nav class="nav-container">
            <a href="index.php" class="logo-link">
                <img src="src/img/logoSolemon.png" alt="VapeShop Logo" class="logo-img">
            </a>

            <div class="nav-links">
                <a href="index.php" class="nav-link <?= $current_page === 'index.php' ? 'active' : '' ?>">
                    Главная
                </a>
                <a href="products.php" class="nav-link <?= $current_page === 'products.php' ? 'active' : '' ?>">
                    Товары
                </a>
                <a href="about.php" class="nav-link <?= $current_page === 'about.php' ? 'active' : '' ?>">
                    О нас
                </a>
                
                <?php if(isset($_SESSION['admin_logged'])): ?>
                    <a href="admin.php" class="nav-link <?= $current_page === 'admin.php' ? 'active' : '' ?>">
                        Админка
                    </a>
                    <a href="logout.php" class="auth-button">
                        Выйти
                    </a>
                <?php else: ?>
                    <a href="login.php" class="auth-button">
                        Войти
                    </a>
                <?php endif; ?>
            </div>
        </nav>
    </header>