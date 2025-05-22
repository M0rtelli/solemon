<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header("Location: admin-login.php");
    exit();
}

// Подключение к БД
$servername = "localhost";
$username = "admin";
$password = "pR0fU7tR1p";
$dbname = "solemon_site";


$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4"); // После подключения к БД

// Обработка запроса на скрытие/показ товара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_visibility'])) {
    $product_id = intval($_POST['product_id']);
    $stmt = $conn->prepare("UPDATE products SET is_hidden = NOT is_hidden WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    exit(); // Для AJAX-запроса
}

// Получение списка категорий из БД
$categories = [];
$categories_result = $conn->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL");
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row['category'];
}

// Получение выбранной категории и подкатегории
$selected_category = $_GET['category'] ?? 'all';
$selected_subcategory = $_GET['subcategory'] ?? 'all';

// Получение списка подкатегорий для выбранной категории
$subcategories = [];
if ($selected_category !== 'all') {
    $stmt = $conn->prepare("SELECT id, name FROM subcategories WHERE parent_category = ?");
    $stmt->bind_param("s", $selected_category);
    $stmt->execute();
    $subcategories_result = $stmt->get_result();
    while ($row = $subcategories_result->fetch_assoc()) {
        $subcategories[] = $row;
    }
}

// Формирование SQL-запроса
$sql = "SELECT p.*, s.name as subcategory_name 
        FROM products p 
        LEFT JOIN subcategories s ON p.subcategory_id = s.id 
        WHERE 1=1";
$params = [];
$types = '';

if ($selected_category !== 'all') {
    $sql .= " AND p.category = ?";
    $params[] = $selected_category;
    $types .= 's';
}

if ($selected_subcategory !== 'all' && !empty($selected_subcategory)) {
    $sql .= " AND p.subcategory_id = ?";
    $params[] = $selected_subcategory;
    $types .= 'i';
}

// Подготовленный запрос
$stmt = $conn->prepare($sql);
if (!empty($params)) {
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
    <title>Админ-панель | Vape Shop</title>
    <link rel="stylesheet" href="style/admin.css">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.querySelector('.filters select[name="category"]');
            const subcategorySelect = document.querySelector('.filters select[name="subcategory"]');
            
            if (categorySelect && subcategorySelect) {
                // Загрузка подкатегорий при изменении категории
                categorySelect.addEventListener('change', function() {
                    const category = this.value;
                    if (category === 'all') {
                        subcategorySelect.innerHTML = '<option value="all">Все подкатегории</option>';
                        return;
                    }
                    
                    fetch(`get_subcategories.php?category=${encodeURIComponent(category)}`)
                        .then(response => response.json())
                        .then(data => {
                            subcategorySelect.innerHTML = '<option value="all">Все подкатегории</option>';
                            data.forEach(subcat => {
                                const option = document.createElement('option');
                                option.value = subcat.id;
                                option.textContent = subcat.name;
                                subcategorySelect.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Error:', error));
                });
            }
        });
    </script>

    <style>
        .visibility-toggle {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 4px;
            transition: all 0.2s;
        }
        
        .visibility-toggle:hover {
            background: rgba(0,0,0,0.05);
        }
        
        .visibility-toggle.hidden svg {
            opacity: 0.5;
        }
        
        .product-card.hidden {
            opacity: 0.7;
            background: #f8f9fa;
            border: 1px dashed #ccc;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>🛠 Управление товарами</h1>
            <nav class="admin-nav">
                <a href="index.php" class="nav-button home-button">
                    <svg viewBox="0 0 24 24" width="20" fill="currentColor">
                        <path d="M12 2L2 12h3v8h6v-6h2v6h6v-8h3L12 2z"/>
                    </svg>
                    На главную
                </a>
                <a href="admin.php" class="nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path fill="#000000" d="M32 32c17.7 0 32 14.3 32 32l0 336c0 8.8 7.2 16 16 16l400 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L80 480c-44.2 0-80-35.8-80-80L0 64C0 46.3 14.3 32 32 32zm96 96c0-17.7 14.3-32 32-32l192 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-192 0c-17.7 0-32-14.3-32-32zm32 64l128 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-128 0c-17.7 0-32-14.3-32-32s14.3-32 32-32zm0 96l256 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-256 0c-17.7 0-32-14.3-32-32s14.3-32 32-32z"/>
                    </svg>
                    Статистика
                </a>
                <a href="admin.php" class="nav-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    Товары
                </a>
                <a href="add_product.php" class="nav-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14m-7-7h14"></path>
                    </svg>
                    Добавить
                </a>
                <a href="import.php" class="nav-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    Импорт
                </a>
                <a href="logout.php" class="nav-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    Выход
                </a>
            </nav>
        </header>

        <div class="filters">
            <form method="GET" class="filter-form">
                <select name="category" class="filter-select">
                    <option value="all" <?= $selected_category === 'all' ? 'selected' : '' ?>>Все категории</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category) ?>" <?= $selected_category === $category ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="subcategory" class="filter-select">
                    <option value="all" <?= $selected_subcategory === 'all' ? 'selected' : '' ?>>Все подкатегории</option>
                    <?php foreach ($subcategories as $subcat): ?>
                        <option value="<?= $subcat['id'] ?>" <?= $selected_subcategory == $subcat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($subcat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="filter-btn">
                    Применить
                </button>
            </form>
        </div>

        <div class="products-grid">
        <?php while($row = $result->fetch_assoc()): ?>
        <div class="product-card <?= $row['is_hidden'] ? 'hidden' : '' ?>">
            <?php if($row['image_url']): ?>
                <img src="<?= $row['image_url'] ?>" class="product-image" alt="<?= htmlspecialchars($row['name']) ?>">
            <?php else: ?>
                <div class="product-image" style="background: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                    <svg viewBox="0 0 24 24" width="40" height="40" fill="#94a3b8">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-5-7l-3 3.72L9 13l-3 4h12l-4-5z"/>
                    </svg>
                </div>
            <?php endif; ?>
            
            <div class="product-meta">
                <span class="product-category"><?= htmlspecialchars($row['category']) ?></span>
                <?php if($row['subcategory_name']): ?>
                    <span class="product-subcategory"><?= htmlspecialchars($row['subcategory_name']) ?></span>
                <?php endif; ?>
            </div>
            
            <h3 class="product-title"><?= htmlspecialchars($row['name']) ?></h3>
            
            <div class="product-actions">
                <button class="visibility-toggle <?= $row['is_hidden'] ? 'hidden' : '' ?>" 
                        data-product-id="<?= $row['id'] ?>"
                        title="<?= $row['is_hidden'] ? 'Показать товар' : 'Скрыть товар' ?>">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                        <?php if($row['is_hidden']): ?>
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        <?php else: ?>
                            <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>
                        <?php endif; ?>
                    </svg>
                </button>
                
                <a href="edit_product.php?id=<?= $row['id'] ?>" class="action-btn edit-btn">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                    </svg>
                </a>
                
                <a href="delete_product.php?id=<?= $row['id'] ?>" 
                   class="action-btn delete-btn" 
                   onclick="return confirm('Удалить товар безвозвратно?')">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                        <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                    </svg>
                </a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Обработка переключения видимости товара
            document.querySelectorAll('.visibility-toggle').forEach(button => {
                button.addEventListener('click', async function() {
                    const productId = this.dataset.productId;
                    const productCard = this.closest('.product-card');
                    
                    try {
                        const response = await fetch('', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `toggle_visibility=1&product_id=${productId}`
                        });
                        
                        if (response.ok) {
                            // Переключаем классы без перезагрузки страницы
                            productCard.classList.toggle('hidden');
                            this.classList.toggle('hidden');
                            
                            // Меняем иконку и tooltip
                            const svg = this.querySelector('svg');
                            if (productCard.classList.contains('hidden')) {
                                svg.innerHTML = '<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>';
                                this.title = 'Показать товар';
                            } else {
                                svg.innerHTML = '<path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>';
                                this.title = 'Скрыть товар';
                            }
                        }
                    } catch (error) {
                        console.error('Ошибка:', error);
                        alert('Произошла ошибка при обновлении статуса товара');
                    }
                });
            });
        });
    </script>
</body>
</html>