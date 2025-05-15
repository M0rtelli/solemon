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

            <button class="mobile-menu-toggle" aria-label="Меню" style="display: none;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            <div class="mobile-menu">
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

<script>
// Адаптивное меню
const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener('click', function() {
        const menu = document.querySelector('.mobile-menu');
        menu.style.display = menu.style.display === 'flex' ? 'none' : 'flex';
    });
    
    // Показываем/скрываем кнопку меню при изменении размера
    function checkMenuVisibility() {
        mobileMenuToggle.style.display = window.innerWidth <= 640 ? 'block' : 'none';
    }
    
    window.addEventListener('resize', checkMenuVisibility);
    checkMenuVisibility();
}
</script>

</body>
</html>