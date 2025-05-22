<?php
require_once('config.php');

$url = "https://api.telegram.org/bot".TELEGRAM_BOT_TOKEN."/sendMessage";
$data = [
    'chat_id' => TELEGRAM_CHAT_ID,
    'text' => 'Тестовое сообщение от бота',
    'parse_mode' => 'HTML'
];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === false) {
    echo "Ошибка отправки. Проверьте:\n";
    echo "- Токен бота: ".TELEGRAM_BOT_TOKEN."\n";
    echo "- Chat ID: ".TELEGRAM_CHAT_ID."\n";
    echo "- Ошибка: ".print_r(error_get_last(), true);
} else {
    echo "Сообщение отправлено успешно!";
    echo "<pre>".print_r(json_decode($result, true), true)."</pre>";
}