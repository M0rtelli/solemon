<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header("Location: admin-login.php");
    exit();
}

$error = '';
$product = null;
$servername = "localhost";
$username = "admin";
$password = "pR0fU7tR1p";
$dbname = "solemon_site";


$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4"); // После подключения к БД
// Получение данных товара
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT p.*, s.name as subcategory_name 
                           FROM products p 
                           LEFT JOIN subcategories s ON p.subcategory_id = s.id 
                           WHERE p.id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if (!$product) {
        header("Location: admin.php");
        exit();
    }
}

// Получение списка подкатегорий для выбранной категории
$subcategories = [];
if ($product) {
    $stmt = $conn->prepare("SELECT id, name FROM subcategories WHERE parent_category = ?");
    $stmt->bind_param("s", $product['category']);
    $stmt->execute();
    $subcategories_result = $stmt->get_result();
    while ($row = $subcategories_result->fetch_assoc()) {
        $subcategories[] = $row;
    }
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $subcategory_id = !empty($_POST['subcategory']) ? (int)$_POST['subcategory'] : null;
    $id = $_POST['id'];
    $image_url = $product['image_url'];

    // Обработка загрузки изображения
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = 'uploads/';
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            // Удаление старого изображения
            if ($image_url && file_exists($image_url)) {
                unlink($image_url);
            }
            $image_url = $targetPath;
        } else {
            $error = "Ошибка загрузки изображения";
        }
    }

    if (empty($error)) {
        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, image_url=?, category=?, subcategory_id=? WHERE id=?");
        $stmt->bind_param("ssssii", $name, $description, $image_url, $category, $subcategory_id, $id);

        if ($stmt->execute()) {
            header("Location: admin.php");
            exit();
        } else {
            $error = "Ошибка обновления данных";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование товара - VapeShop</title>
    <link rel="stylesheet" href="style/edit_product.css">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.querySelector('select[name="category"]');
            const subcategorySelect = document.querySelector('select[name="subcategory"]');
            
            if (categorySelect && subcategorySelect) {
                // Загрузка подкатегорий при изменении категории
                categorySelect.addEventListener('change', function() {
                    const category = this.value;
                    fetchSubcategories(category);
                });
                
                function fetchSubcategories(category) {
                    fetch(`get_subcategories.php?category=${encodeURIComponent(category)}`)
                        .then(response => response.json())
                        .then(data => {
                            subcategorySelect.innerHTML = '<option value="">-- Без подкатегории --</option>';
                            data.forEach(subcat => {
                                const option = document.createElement('option');
                                option.value = subcat.id;
                                option.textContent = subcat.name;
                                subcategorySelect.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Error:', error));
                }
            }
        });
    </script>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="edit-form">
            <h2>Редактирование товара</h2>
            
            <?php if($error): ?>
                <div class="error-message"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                
                <div class="form-group">
                    <label>Название товара</label>
                    <input type="text" name="name" 
                           value="<?= htmlspecialchars($product['name'] ?? '') ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label>Описание</label>
                    <textarea name="description" rows="4"><?= 
                        htmlspecialchars($product['description'] ?? '') 
                    ?></textarea>
                </div>

                <div class="form-group">
                    <label>Категория</label>
                    <select name="category" id="category-select" required>
                        <option value="Жидкости" <?= 
                            ($product['category'] ?? '') === 'Жидкости' ? 'selected' : '' 
                        ?>>Жидкости</option>
                        <option value="Устройства" <?= 
                            ($product['category'] ?? '') === 'Устройства' ? 'selected' : '' 
                        ?>>Устройства</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Подкатегория</label>
                    <select name="subcategory" id="subcategory-select">
                        <option value="">-- Без подкатегории --</option>
                        <?php foreach ($subcategories as $subcat): ?>
                            <option value="<?= $subcat['id'] ?>" <?= 
                                $product['subcategory_id'] == $subcat['id'] ? 'selected' : '' 
                            ?>>
                                <?= htmlspecialchars($subcat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Текущее изображение</label>
                    <?php if($product['image_url']): ?>
                        <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                             class="image-preview"
                             alt="Текущее изображение">
                    <?php else: ?>
                        <div class="no-image">Изображение отсутствует</div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Новое изображение</label>
                    <input type="file" name="image" accept="image/*">
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        Сохранить изменения
                    </button>
                    <a href="admin.php" class="btn btn-secondary">
                        Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>