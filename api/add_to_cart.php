<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_logged'])) {
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
    exit;
}

require_once('../db_connect.php'); // Подключение к БД

$data = json_decode(file_get_contents('php://input'), true);
$product_id = intval($data['product_id'] ?? 0);

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Неверный ID товара']);
    exit;
}

try {
    // Проверяем, есть ли уже товар в корзине
    $stmt = $conn->prepare("
        INSERT INTO cart (user_id, product_id, quantity) 
        VALUES (?, ?, 1)
        ON DUPLICATE KEY UPDATE quantity = quantity + 1
    ");
    $stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
    $stmt->execute();
    
    // Получаем общее количество товаров в корзине
    $count_stmt = $conn->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
    $count_stmt->bind_param("i", $_SESSION['user_id']);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result()->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'cart_count' => $count_result['count'] ?? 0
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка сервера: ' . $e->getMessage()]);
}