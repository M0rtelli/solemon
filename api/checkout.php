<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_logged'])) {
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
    exit;
}

require_once('../config.php');
require_once('../db_connect.php');

function sendTelegramMessage($text)
{
    $bot_token = TELEGRAM_BOT_TOKEN;
    $chat_id = TELEGRAM_CHAT_ID;

    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";

    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];

    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    return $result !== false;
}

try {
    // 1. Получаем содержимое корзины
    $stmt = $conn->prepare("
        SELECT 
            p.id, 
            p.name,
            p.category,
            p.description,
            (SELECT name FROM subcategories WHERE id = p.subcategory_id) as subcategory,
            c.quantity
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if (empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Корзина пуста']);
        exit;
    }

    // 2. Получаем информацию о пользователе
    $user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $_SESSION['user_id']);
    $user_stmt->execute();
    $user = $user_stmt->get_result()->fetch_assoc();

    // 3. Формируем сообщение
    $message = "<b>🛒 Новый заказ</b>\n\n";
    $message .= "<b>👤 Пользователь:</b> " . "username" . "\n";
    $message .= "<b>🆔 Telegram ID:</b> " . "<a href=\"tg://user?id=" . $user['telegram_id'] . "\">" . $user['telegram_id'] . "</a>" . "\n\n";
    $message .= "<b>📦 Состав заказа:</b>\n";

    foreach ($items as $item) {
        $message .= "\n<b>— " . htmlspecialchars($item['name']) . "</b>\n";
        $message .= "<i>Категория:</i> " . htmlspecialchars($item['category']);

        if (!empty($item['subcategory'])) {
            $message .= " / " . htmlspecialchars($item['subcategory']);
        }

        $message .= "\n<i>Количество:</i> ×" . $item['quantity'] . "\n";

        // Добавляем описание товара, если оно есть
        if (!empty($item['description'])) {
            $description = strlen($item['description']) > 100
                ? substr($item['description'], 0, 100) . '...'
                : $item['description'];
            $message .= "<i>Описание:</i> " . htmlspecialchars($description) . "\n";
        }

        $message .= "━━━━━━━━━━━━━━━━";
    }
    $message .= "\n<b>🛍️ Всего товаров:</b> " . count($items) . "\n";
    $message .= "<b>📅 Дата:</b> " . date('d.m.Y H:i');

    // 4. Отправляем в Telegram
    $telegramResult = sendTelegramMessage($message);

    if (!$telegramResult) {
        throw new Exception("Не удалось отправить сообщение в Telegram");
    }

    // 5. Очищаем корзину
    $clear_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $clear_stmt->bind_param("i", $_SESSION['user_id']);
    $clear_stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Заказ успешно оформлен']);

} catch (Exception $e) {
    error_log("Ошибка при оформлении заказа: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}