<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header("Location: admin-login.php");
    exit();
}

$error = '';
$servername = "localhost";
$username = "admin";
$password = "pR0fU7tR1p";
$dbname = "solemon_site";


$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4"); // После подключения к БД

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $image_url = '';
    $category = trim($_POST['category']);

    // Загрузка изображения
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = 'uploads/';
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image_url = $targetPath;
        } else {
            $error = "Ошибка загрузки изображения";
        }
    }

    if (empty($error) && !empty($name)) {
        $stmt = $conn->prepare("INSERT INTO products (name, description, image_url, category) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $description, $image_url, $category);
        $stmt->execute();
        header("Location: admin.php");
        exit();
    } else {
        $error = $error ?: "Заполните обязательные поля";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить товар | Vape Shop</title>
    <link rel="stylesheet" href="style/add_product.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>➕ Добавить товар</h1>
            <nav class="admin-nav">
                <a href="admin.php" class="nav-link">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    Назад
                </a>
            </nav>
        </header>

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <h2 class="form-title">

                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="28" height="28">
                        <path fill="#000000" d="M320 464c8.8 0 16-7.2 16-16l0-288-80 0c-17.7 0-32-14.3-32-32l0-80L64 48c-8.8 0-16 7.2-16 16l0 384c0 8.8 7.2 16 16 16l256 0zM0 64C0 28.7 28.7 0 64 0L229.5 0c17 0 33.3 6.7 45.3 18.7l90.5 90.5c12 12 18.7 28.3 18.7 45.3L384 448c0 35.3-28.7 64-64 64L64 512c-35.3 0-64-28.7-64-64L0 64z"/>
                    </svg>
                    Новая позиция
                </h2>

                <?php if($error): ?>
                    <div class="error-message">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="var(--danger)">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                        </svg>
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Название товара</label>
                    <input type="text" name="name" required placeholder="Введите название">
                </div>

                <div class="form-group">
                    <label>Описание</label>
                    <textarea name="description" rows="4" placeholder="Добавьте описание товара" style=" resize: none;"></textarea>
                </div>

                <div class="form-group">
                    <label>Категория</label>
                    <select name="category" required>
                        <option value="Жидкости">Жидкости</option>
                        <option value="Устройства">Устройства</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Изображение товара</label>
                    <div class="file-upload">
                        <input type="file" name="image" class="file-input" accept="image/*">
                        <label class="upload-label">
                            <svg class="upload-icon" viewBox="0 0 512 512">
                                <path d="M288 109.3L288 352c0 17.7-14.3 32-32 32s-32-14.3-32-32l0-242.7-73.4 73.4c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3l128-128c12.5-12.5 32.8-12.5 45.3 0l128 128c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L288 109.3zM64 352l128 0c0 35.3 28.7 64 64 64s64-28.7 64-64l128 0c35.3 0 64 28.7 64 64l0 32c0 35.3-28.7 64-64 64L64 512c-35.3 0-64-28.7-64-64l0-32c0-35.3 28.7-64 64-64zM432 456a24 24 0 1 0 0-48 24 24 0 1 0 0 48z" />
                            </svg>
                            <span>Перетащите файл или кликните для загрузки</span>
                            <span style="color: #64748b; font-size: 0.9rem;">PNG, JPG (макс. 5MB)</span>
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                        Сохранить товар
                    </button>
                    <a href="admin.php" class="btn btn-secondary">
                        Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Динамическое обновление имени файла
        document.querySelector('.file-input').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'Файл не выбран';
            document.querySelector('.upload-label span:first-child').textContent = fileName;
        });

        // Drag and drop
        const uploadArea = document.querySelector('.file-upload');
        
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = 'var(--primary)';
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.style.borderColor = 'var(--border)';
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = 'var(--border)';
            const file = e.dataTransfer.files[0];
            if (file) document.querySelector('.file-input').files = e.dataTransfer.files;
        });
    </script>
</body>
</html>