<?php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['admin_logged'])) {
    die(json_encode([]));
}

$servername = "localhost";
$username = "admin";
$password = "pR0fU7tR1p";
$dbname = "solemon_site";


$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4"); // После подключения к БД
$category = $_GET['category'] ?? '';

$stmt = $conn->prepare("SELECT id, name FROM subcategories WHERE parent_category = ?");
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();

$subcategories = [];
while ($row = $result->fetch_assoc()) {
    $subcategories[] = $row;
}

echo json_encode($subcategories);
?>