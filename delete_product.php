<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header("Location: admin-login.php");
    exit();
}

if (isset($_GET['id'])) {
    $conn = new mysqli('MySQL-8.0', 'solemon_site', 'solemon2281488', 'solemon');
    
    // Получаем информацию о изображении для удаления
    $stmt = $conn->prepare("SELECT image_url FROM products WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if ($product) {
        // Удаляем изображение если существует
        if ($product['image_url'] && file_exists($product['image_url'])) {
            unlink($product['image_url']);
        }
        
        // Удаляем запись из БД
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $_GET['id']);
        $stmt->execute();
    }
}

header("Location: admin.php");
exit();