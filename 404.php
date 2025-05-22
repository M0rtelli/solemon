<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Страница не найдена | 404</title>
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --gray: #6b7280;
            --light: #f9fafb;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: var(--light);
            color: #1f2937;
            padding: 20px;
            text-align: center;
        }
        
        .container {
            max-width: 600px;
            width: 100%;
        }
        
        h1 {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 16px;
            color: var(--primary);
        }
        
        h2 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 24px;
        }
        
        p {
            font-size: 18px;
            color: var(--gray);
            margin-bottom: 32px;
            line-height: 1.6;
        }
        
        .btn {
            display: inline-block;
            background-color: var(--primary);
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: var(--primary-hover);
        }
        
        @media (max-width: 640px) {
            h1 {
                font-size: 36px;
            }
            
            h2 {
                font-size: 20px;
            }
            
            p {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>404</h1>
        <h2>Упс! Страница не найдена</h2>
        <p>Запрашиваемая страница не существует или была перемещена. Пожалуйста, проверьте URL или вернитесь на главную.</p>
        <a href="/" class="btn">На главную</a>
    </div>
</body>
</html>