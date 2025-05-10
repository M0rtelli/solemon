<?php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['admin_logged'])) {
    die(json_encode([]));
}

$conn = new mysqli('MySQL-8.0', 'solemon_site', 'solemon2281488', 'solemon');
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