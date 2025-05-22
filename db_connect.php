<?php
$conn = new mysqli("localhost", "admin", "pR0fU7tR1p", "solemon_site");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");