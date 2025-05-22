<?php
session_start();

$conn = new mysqli("localhost", "admin", "pR0fU7tR1p", "solemon_site");
$conn->set_charset("utf8mb4");

// Обработка AJAX-запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (isset($_POST['verify_code'])) {
        $code = $conn->real_escape_string($_POST['code'] ?? '');
        
        // Проверяем код с использованием UTC времени
        $stmt = $conn->prepare("
            SELECT u.id, u.telegram_id, u.username 
            FROM telegram_auth_codes tc
            JOIN users u ON tc.telegram_id = u.telegram_id
            WHERE tc.code = ? 
              AND tc.is_used = FALSE
              AND tc.expires_at > UTC_TIMESTAMP()
            LIMIT 1
        ");
        
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Помечаем код как использованный
            $update_stmt = $conn->prepare("
                UPDATE telegram_auth_codes 
                SET is_used = TRUE, 
                    user_id = ?
                WHERE code = ?
            ");
            $update_stmt->bind_param("is", $user['id'], $code);
            $update_stmt->execute();
            
            // Сохраняем данные пользователя в сессии
            $_SESSION['user_logged'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['telegram_id'] = $user['telegram_id'];
            $_SESSION['username'] = $user['username'];
            
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'invalid', 'message' => 'Неверный или просроченный код']);
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация через Telegram | Solemon</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0088cc;
            --primary-hover: #0077b3;
            --background: #f8fafc;
            --card-bg: #ffffff;
            --text: #1e293b;
            --text-secondary: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --error: #ef4444;
            --radius: 12px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            color: var(--text);
            line-height: 1.5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .auth-container {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 440px;
            padding: 40px;
            text-align: center;
            border: 1px solid var(--border);
        }
        
        .logo {
            margin-bottom: 24px;
        }
        
        .logo img {
            height: 48px;
        }

        .logo-link {
            margin-bottom: 24px;
            transition: transform 0.2s;
        }
        
        h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 32px;
            color: var(--text);
        }
        
        .steps {
            display: grid;
            gap: 16px;
            margin-bottom: 28px;
            text-align: left;
        }
        
        .step {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        
        .step-number {
            background: var(--primary);
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 12px;
            font-weight: 600;
            flex-shrink: 0;
            margin-top: 2px;
        }
        
        .step-content {
            color: var(--text-secondary);
        }
        
        .step-content strong {
            color: var(--text);
            font-weight: 500;
        }
        
        .code-input {
            margin: 24px 0;
        }
        
        .code-field {
            display: flex;
            justify-content: center;
            gap: 8px;
        }
        
        .code-field input {
            width: 48px;
            height: 56px;
            text-align: center;
            font-size: 20px;
            font-weight: 600;
            border: 1px solid var(--border);
            border-radius: 8px;
            transition: all 0.2s;
        }
        
        .code-field input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 136, 204, 0.1);
        }
        
        .btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            width: 100%;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .btn:hover {
            background: var(--primary-hover);
        }
        
        .bot-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 24px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .bot-link:hover {
            color: var(--primary-hover);
        }
        
        .bot-link svg {
            width: 16px;
            height: 16px;
        }
        
        .status-message {
            margin-top: 20px;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            display: none;
        }
        
        .success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }
        
        .error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error);
        }
        
        @media (max-width: 480px) {
            .auth-container {
                padding: 32px 24px;
            }
            
            .code-field input {
                width: 40px;
                height: 48px;
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="logo">
            <a href="index.php" class="logo-link">
                <img src="src/img/logoSolemon.png" alt="Solemon" class="logo-img">
            </a>
        </div>
        
        <h1>Вход через Telegram</h1>
        
        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-content">Откройте нашего бота в Telegram</div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-content">Отправьте команду <strong>/login</strong></div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-content">Введите полученный код ниже</div>
            </div>
        </div>
        
        <div class="code-input">
            <div class="code-field">
                <input type="text" id="digit1" maxlength="1" pattern="[0-9]" inputmode="numeric" autofocus>
                <input type="text" id="digit2" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" id="digit3" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" id="digit4" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" id="digit5" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" id="digit6" maxlength="1" pattern="[0-9]" inputmode="numeric">
            </div>
        </div>
        
        <button id="verifyBtn" class="btn">Подтвердить</button>
        
        <a href="https://t.me/Solemon_Shop_Bot" target="_blank" class="bot-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
            </svg>
            Перейти в бота
        </a>
        
        <div id="statusMessage" class="status-message"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const digitInputs = Array.from({length: 6}, (_, i) => document.getElementById(`digit${i+1}`));
            const verifyBtn = document.getElementById('verifyBtn');
            const statusMessage = document.getElementById('statusMessage');
            
            // Обработка ввода кода с автоматическим переходом между полями
            digitInputs.forEach((input, index) => {
                input.addEventListener('input', (e) => {
                    if (e.target.value.length === 1) {
                        if (index < 5) {
                            digitInputs[index + 1].focus();
                        }
                    }
                });
                
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && e.target.value.length === 0 && index > 0) {
                        digitInputs[index - 1].focus();
                    }
                });
            });
            
            // Проверка кода при нажатии кнопки
            verifyBtn.addEventListener('click', async function() {
                const code = digitInputs.map(input => input.value).join('');
                
                if (code.length !== 6 || !/^\d+$/.test(code)) {
                    showStatus('Введите 6-значный код', 'error');
                    return;
                }
                
                try {
                    verifyBtn.disabled = true;
                    verifyBtn.textContent = 'Проверка...';
                    
                    const response = await fetch('login.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `verify_code=true&code=${code}`
                    });
                    
                    const result = await response.json();
                    
                    if (result.status === 'success') {
                        showStatus('Успешная авторизация! Перенаправление...', 'success');
                        setTimeout(() => {
                            window.location.href = '/index.php';
                        }, 1500);
                    } else {
                        showStatus(result.message || 'Неверный или просроченный код', 'error');
                        digitInputs[0].focus();
                    }
                } catch (error) {
                    showStatus('Ошибка соединения', 'error');
                    console.error('Auth error:', error);
                } finally {
                    verifyBtn.disabled = false;
                    verifyBtn.textContent = 'Подтвердить';
                }
            });
            
            // Показ статуса
            function showStatus(message, type) {
                statusMessage.textContent = message;
                statusMessage.className = `status-message ${type}`;
                statusMessage.style.display = 'block';
                
                // Скрыть сообщение через 5 секунд
                setTimeout(() => {
                    statusMessage.style.display = 'none';
                }, 5000);
            }
            
            // Автоматический фокус на первое поле при загрузке
            digitInputs[0].focus();
        });
    </script>
</body>
</html>