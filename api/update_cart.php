<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_logged'])) {
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
    exit;
}

require_once('../db_connect.php');

$data = json_decode(file_get_contents('php://input'), true);
$product_id = intval($data['product_id'] ?? 0);
$action = $data['action'] ?? '';

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Неверный ID товара']);
    exit;
}

try {
    $conn->begin_transaction();
    
    // Получаем текущее количество
    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
    $stmt->execute();
    $current = $stmt->get_result()->fetch_assoc();
    
    $new_quantity = $current['quantity'] ?? 0;
    
    // Обрабатываем действие
    switch ($action) {
        case 'increase':
            $new_quantity++;
            break;
        case 'decrease':
            $new_quantity = max(0, $new_quantity - 1);
            break;
        case 'remove':
            $new_quantity = 0;
            break;
        default:
            throw new Exception("Неизвестное действие");
    }
    
    // Обновляем или удаляем запись
    if ($new_quantity > 0) {
        $stmt = $conn->prepare("
            INSERT INTO cart (user_id, product_id, quantity) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)
        ");
        $stmt->bind_param("iii", $_SESSION['user_id'], $product_id, $new_quantity);
    } else {
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
    }
    
    $stmt->execute();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'new_quantity' => $new_quantity
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}