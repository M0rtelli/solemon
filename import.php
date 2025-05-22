<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header("Location: admin-login.php");
    exit();
}

$error = '';
$success = [];
$import_count = 0;

// Подключение к БД
$servername = "localhost";
$username = "admin";
$password = "pR0fU7tR1p";
$dbname = "solemon_site";


$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4"); // После подключения к БД
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file'])) {
    $current_category = '';

    if ($_FILES['import_file']['error'] === UPLOAD_ERR_OK) {
        $file = fopen($_FILES['import_file']['tmp_name'], 'r');

        while (!feof($file)) {
            $line = trim(fgets($file));

            if (empty($line))
                continue;

            // Обработка категории
            if (preg_match('/^<(.+?)>$/', $line, $matches)) {
                $current_category = $conn->real_escape_string($matches[1]);
                continue;
            }

            // Обработка товара
            if (strpos($line, '- ') === 0 && $current_category) {
                $parts = explode('%', substr($line, 2));
                if (count($parts) < 3) {
                    $error = "Неверный формат строки: $line";
                    break;
                }

                $name = trim($conn->real_escape_string($parts[0]));
                $description = trim($conn->real_escape_string($parts[1]));
                $image_url = trim($conn->real_escape_string($parts[2]));

                // Проверка существования товара
                $check = $conn->query("SELECT id FROM products 
                                      WHERE name = '$name' AND category = '$current_category' AND description = '$description'");

                if (!$check->num_rows) {
                    $insert = $conn->query("INSERT INTO products 
                                          (name, description, image_url, category)
                                          VALUES ('$name', '$description', '$image_url', '$current_category')");

                    if ($insert) {
                        $import_count++;
                        $success[] = "Добавлен: $name";
                    } else {
                        $error = "Ошибка при добавлении товара: " . $conn->error;
                        break;
                    }
                } else {
                    $success[] = "Пропуск дубликата: $name";
                }
            }
        }
        fclose($file);
    } else {
        $error = "Ошибка загрузки файла";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Импорт товаров</title>
    <link rel="stylesheet" href="/style/import.css">
</head>

<body>
    <div class="import-container">
        <h2>📤 Импорт товаров</h2>

        <form method="POST" enctype="multipart/form-data">
            <div class="upload-area">
                <div class="file-input">
                    <input type="file" name="import_file" accept=".txt" required>
                    <button type="button" class="custom-upload-btn">
                        <svg class="upload-icon" viewBox="0 0 512 512">
                            <path d="M288 109.3L288 352c0 17.7-14.3 32-32 32s-32-14.3-32-32l0-242.7-73.4 73.4c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3l128-128c12.5-12.5 32.8-12.5 45.3 0l128 128c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L288 109.3zM64 352l128 0c0 35.3 28.7 64 64 64s64-28.7 64-64l128 0c35.3 0 64 28.7 64 64l0 32c0 35.3-28.7 64-64 64L64 512c-35.3 0-64-28.7-64-64l0-32c0-35.3 28.7-64 64-64zM432 456a24 24 0 1 0 0-48 24 24 0 1 0 0 48z" />
                        </svg>
                        
                        Выберите файл
                    </button>
                </div>
                <p style="margin-top: 1rem; color: #64748b; font-size: 0.9rem;">
                    Поддерживаемый формат: .txt • Макс. размер: 5MB
                </p>
            </div>

            <div class="action-btns">
                <button type="submit" class="btn btn-primary">
                    Запустить импорт
                </button>
                <a href="admin.php" class="btn btn-secondary">
                    ← Назад к списку
                </a>
            </div>
        </form>

        <?php if ($error): ?>
            <div class="results">
                <div class="result-item error">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                        <path
                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
                    </svg>
                    <?= $error ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($import_count > 0): ?>
            <div class="results">
                <h3 style="margin-bottom: 1rem; color: var(--text);">📊 Результаты импорта:</h3>
                <div style="color: #64748b; margin-bottom: 1.5rem;">
                    Успешно импортировано: <?= $import_count ?> товаров
                </div>

                <?php foreach ($success as $msg): ?>
                    <div class="result-item success">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                        </svg>
                        <?= $msg ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Динамическое обновление имени файла
        document.querySelector('input[type="file"]').addEventListener('change', function (e) {
            const fileName = e.target.files[0]?.name || 'Файл не выбран';
            document.querySelector('.custom-upload-btn').innerHTML = `
                <svg class="upload-icon" viewBox="0 0 24 24">
                    <path d="M19 13v5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-5M12 3v14m0 0 4-4m-4 4-4-4"/>
                </svg>
                ${fileName}
            `;
        });
    </script>
</body>

</html>