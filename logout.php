<?php
session_start();

// Удаляем только данные администратора
unset($_SESSION['admin_logged']);

// Удаляем только данные пользователя
unset($_SESSION['user_logged']);
unset($_SESSION['user_id']);
unset($_SESSION['telegram_id']);
unset($_SESSION['username']);

// Сохраняем сообщение об успешном выходе
$_SESSION['logout_message'] = 'Вы успешно вышли из системы';

// Перенаправляем на главную
header('Location: index.php');
exit;
?>