<?php
session_start();

// Удаляем только данные администратора
unset($_SESSION['admin_logged']);

// Сохраняем сообщение об успешном выходе
$_SESSION['logout_message'] = 'Вы успешно вышли из системы';

// Перенаправляем на главную
header('Location: index.php');
exit;
?>