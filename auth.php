<?php
require 'db.php';

$tgData = $_GET;
$checkHash = $tgData['hash'];
unset($tgData['hash']);

$dataCheckArr = [];
foreach ($tgData as $key => $value) {
    $dataCheckArr[] = "$key=$value";
}

sort($dataCheckArr);
$dataCheckString = implode("\n", $dataCheckArr);
$secretKey = hash('sha256', 'YOUR_BOT_TOKEN', true);
$hash = hash_hmac('sha256', $dataCheckString, $secretKey);

if ($hash !== $checkHash) {
    die('Неверная подпись данных');
}

// Поиск или создание пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE telegram_id = ?");
$stmt->execute([$tgData['id']]);

if (!$user = $stmt->fetch()) {
    $stmt = $pdo->prepare("INSERT INTO users (telegram_id) VALUES (?)");
    $stmt->execute([$tgData['id']]);
    $userId = $pdo->lastInsertId();
} else {
    $userId = $user['id'];
}

$_SESSION['user_id'] = $userId;
header('Location: profile.php');