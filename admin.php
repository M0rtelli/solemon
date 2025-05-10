<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header("Location: admin-login.php");
    exit();
}

// Подключение к БД
$conn = new mysqli('MySQL-8.0', 'solemon_site', 'solemon2281488', 'solemon');

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

if ($selected_category !== 'all' && in_array($selected_category, ['Жидкости', 'Устройства'])) {
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
                    <option value="Жидкости" <?= $selected_category === 'Жидкости' ? 'selected' : '' ?>>Жидкости</option>
                    <option value="Устройства" <?= $selected_category === 'Устройства' ? 'selected' : '' ?>>Устройства</option>
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
            <div class="product-card">
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
</body>
</html>