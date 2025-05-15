<?php
session_start();

// Проверяем наличие сообщения о выходе
$logout_message = '';
if (isset($_SESSION['logout_message'])) {
    $logout_message = $_SESSION['logout_message'];
    unset($_SESSION['logout_message']);
}

// Подключение к базе данных
$servername = "MySQL-8.0";
$username = "solemon_site";
$password = "solemon2281488";
$dbname = "solemon";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// test

// Получаем параметры фильтрации
$current_category = $_GET['category'] ?? '';
$current_subcategory = $_GET['subcategory'] ?? '';
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

// Получаем все категории
$categories = $conn->query("SELECT DISTINCT category FROM products")->fetch_all(MYSQLI_ASSOC);

// Получаем все подкатегории с группировкой по категориям
$subcategories_result = $conn->query("
    SELECT s.id, s.name, s.parent_category 
    FROM subcategories s
    ORDER BY s.parent_category, s.name
");
$subcategories = [];
while ($row = $subcategories_result->fetch_assoc()) {
    $subcategories[$row['parent_category']][] = $row;
}

// Формируем SQL-запрос
$sql = "SELECT p.*, s.name as subcategory_name FROM products p 
        LEFT JOIN subcategories s ON p.subcategory_id = s.id 
        WHERE 1=1";
$params = [];
$types = '';

// Фильтр по категории
if ($current_category && in_array($current_category, ['Жидкости', 'Устройства'])) {
    $sql .= " AND p.category = ?";
    $params[] = $current_category;
    $types .= 's';
}

// Фильтр по подкатегории
if ($current_subcategory && $current_subcategory !== 'all') {
    $sql .= " AND p.subcategory_id = ?";
    $params[] = $current_subcategory;
    $types .= 'i';
}

// Фильтр по поисковому запросу
if (!empty($search_query)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $search_term = "%{$search_query}%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'ss';
}

// Подготовленный запрос
$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solemon Shop | Главная</title>
    <link rel="stylesheet" href="style/index.css">
</head>

<body>
    <header class="header">
        <nav class="nav-wrapper">
            <div class="nav-left">
                <div class="category-menu">
                    <button class="nav-button">
                        <svg viewBox="0 0 24 24" width="18" fill="currentColor">
                            <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z" />
                        </svg>
                        Категории
                    </button>
                    <div class="category-dropdown">
                        <a href="?"
                            class="<?= empty($current_category) && empty($current_subcategory) ? 'active' : '' ?>">Все
                            товары</a>

                        <?php foreach ($categories as $category_item): ?>
                            <?php $category = $category_item['category']; ?>
                            <div class="category-with-submenu">
                                <a href="?category=<?= urlencode($category) ?>"
                                    class="<?= $current_category === $category && empty($current_subcategory) ? 'active' : '' ?>">
                                    <?= htmlspecialchars($category) ?>
                                    <?php if (!empty($subcategories[$category])): ?>
                                        <svg class="dropdown-arrow" viewBox="0 0 24 24" width="14" fill="currentColor">
                                            <path d="M7 10l5 5 5-5z" />
                                        </svg>
                                    <?php endif; ?>
                                </a>

                                <?php if (!empty($subcategories[$category])): ?>
                                    <div class="subcategory-dropdown">
                                        <a href="?category=<?= urlencode($category) ?>&subcategory=all"
                                            class="<?= $current_category === $category && $current_subcategory === 'all' ? 'active' : '' ?>">
                                            Все <?= htmlspecialchars($category) ?>
                                        </a>
                                        <?php foreach ($subcategories[$category] as $subcat): ?>
                                            <a href="?category=<?= urlencode($category) ?>&subcategory=<?= $subcat['id'] ?>"
                                                class="<?= $current_subcategory == $subcat['id'] ? 'active' : '' ?>">
                                                <?= htmlspecialchars($subcat['name']) ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <a href="/" class="logo-link">
                <img src="src/img/logoSolemon.png" alt="Solemon Vape Shop" class="site-logo" width="160" height="40">
            </a>

            <div class="nav-right">
                <form class="search-form" method="GET" action="">
                    <?php if ($current_category): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($current_category) ?>">
                    <?php endif; ?>
                    <?php if ($current_subcategory): ?>
                        <input type="hidden" name="subcategory" value="<?= htmlspecialchars($current_subcategory) ?>">
                    <?php endif; ?>
                    <div class="search-wrapper">
                        <input type="text" name="q" placeholder="Поиск товаров..."
                            value="<?= htmlspecialchars($search_query) ?>" class="search-input">
                        <button type="submit" class="search-button">
                            <svg viewBox="0 0 24 24" width="20" fill="currentColor">
                                <path
                                    d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z" />
                            </svg>
                        </button>
                    </div>
                </form>

                <?php if (isset($_SESSION['admin_logged'])): ?>
                    <a href="admin.php" class="nav-link">
                        Админка
                    </a>
                    <a href="logout.php" class="auth-button">
                        Выйти
                    </a>
                <?php else: ?>
                    <a href="admin-login.php" class="login-button">
                        <svg class="login-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>Вход</span>
                    </a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <?php if (!empty($logout_message)): ?>
        <div class="logout-notification">
            <?= htmlspecialchars($logout_message) ?>
            <button class="close-notification">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (!empty($search_query)): ?>
        <div class="search-results-info">
            Найдено товаров: <?= $result->num_rows ?>
            <?php if (!empty($search_query)): ?>
                по запросу "<?= htmlspecialchars($search_query) ?>"
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="products-grid">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="product-card">
                <img src="<?= $row['image_url'] ?: '/src/img/sad.jpg' ?>" class="product-image" alt="Товар" loading="lazy">

                <div class="product-info">
                    <h3 class="product-title"><?= htmlspecialchars($row['name']) ?></h3>
                    <p class="product-description"><?= htmlspecialchars($row['description']) ?></p>

                    <div class="product-badges">
                        <span class="product-badge category-badge"
                            data-category="<?= htmlspecialchars($row['category']) ?>">
                            <?= htmlspecialchars($row['category']) ?>
                        </span>

                        <?php if (!empty($row['subcategory_name'])): ?>
                            <span class="product-badge subcategory-badge"
                                data-subcategory="<?= htmlspecialchars($row['subcategory_name']) ?>">
                                <?= htmlspecialchars($row['subcategory_name']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>


    <script src="script/index.js"></script>
    <script>

        document.querySelectorAll('a').forEach(link => {
            link.setAttribute('draggable', 'false');
            link.addEventListener('dragstart', function (e) {
                e.preventDefault();
                return false;
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const categoryButton = document.querySelector('.nav-button');
            const categoryDropdown = document.querySelector('.category-dropdown');

            // Адаптация для мобильных устройств
            if (window.innerWidth < 768) {
                // Переключение основного меню
                if (categoryButton && categoryDropdown) {
                    categoryButton.addEventListener('click', function (e) {
                        e.stopPropagation();
                        categoryDropdown.style.display =
                            categoryDropdown.style.display === 'block' ? 'none' : 'block';
                    });
                }

                // Обработка кликов по категориям с подкатегориями
                document.querySelectorAll('.category-with-submenu > a').forEach(link => {
                    link.addEventListener('click', function (e) {
                        if (window.innerWidth < 768) {
                            e.preventDefault();
                            const submenu = this.nextElementSibling;
                            if (submenu && submenu.classList.contains('subcategory-dropdown')) {
                                submenu.style.display =
                                    submenu.style.display === 'block' ? 'none' : 'block';

                                // Закрываем другие открытые подменю
                                document.querySelectorAll('.subcategory-dropdown').forEach(menu => {
                                    if (menu !== submenu) {
                                        menu.style.display = 'none';
                                    }
                                });
                            }
                        }
                    });
                });

                // Закрытие при клике вне меню
                document.addEventListener('click', function (e) {
                    if (!e.target.closest('.category-menu')) {
                        if (categoryDropdown) {
                            categoryDropdown.style.display = 'none';
                        }
                        document.querySelectorAll('.subcategory-dropdown').forEach(sub => {
                            sub.style.display = 'none';
                        });
                    }
                });
            } else {
                // Для десктопов - закрытие подменю при уходе курсора
                document.querySelector('.nav-left').addEventListener('mouseleave', function () {
                    document.querySelectorAll('.subcategory-dropdown').forEach(sub => {
                        sub.style.display = 'none';
                    });
                });
            }
        });
    </script>

    <?php $conn->close(); ?>
</body>

</html>