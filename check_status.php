<?php
session_start();
require 'db.php';

$userId = $_SESSION['reg_user_id'] ?? null;
$stmt = $pdo->prepare("SELECT telegram_id FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

header('Content-Type: application/json');
echo json_encode(['telegram_id' => $user['telegram_id'] ?? null]);