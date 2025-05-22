<?php
session_start();

if (!isset($_SESSION['user_logged'])) {
    header('Location: login.php');
    exit;
}

$conn = new mysqli("localhost", "admin", "pR0fU7tR1p", "solemon_site");
$conn->set_charset("utf8mb4");

// Получаем товары в корзине
$stmt = $conn->prepare("
    SELECT 
        p.id, 
        p.name, 
        p.image_url, 
        p.description,
        p.category,
        (SELECT name FROM subcategories WHERE id = p.subcategory_id) as subcategory,
        c.quantity
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина | Solemon</title>
    <link rel="stylesheet" href="style/index.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .cart-items {
            display: grid;
            gap: 1.5rem;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 120px 1fr auto;
            gap: 1.5rem;
            padding: 1.5rem;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .cart-item-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            align-self: center;
        }

        .cart-item-details {
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
        }

        .cart-item-meta {
            display: flex;
            gap: 0.8rem;
            font-size: 0.9rem;
        }

        .cart-item-category {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
        }

        .cart-item-subcategory {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
        }

        .cart-item-description {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .cart-item-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 1rem;
            min-width: 120px;
        }

        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quantity-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 1px solid #e2e8f0;
            background: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .quantity-btn:hover {
            background: var(--background);
            border-color: var(--primary);
            color: var(--primary);
        }

        .quantity {
            min-width: 20px;
            text-align: center;
        }

        .remove-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
            padding: 0.5rem;
            transition: all 0.2s;
        }

        .remove-btn:hover {
            color: #ef4444;
            transform: scale(1.1);
        }

        @media (max-width: 768px) {
            .cart-item {
                grid-template-columns: 1fr;
            }

            .cart-item-actions {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid #f1f5f9;
                width: 100%;
            }
        }

        .cart-summary {
            margin-top: 2rem;
            padding: 1.5rem;
            background: var(--card-bg);
            border-radius: 12px;
            display: grid;
            gap: 1rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
        }

        .checkout-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .checkout-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
        }

        .empty-cart {
            text-align: center;
            padding: 4rem 0;
        }

        
    .empty-cart {
        text-align: center;
        padding: 4rem 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1.5rem;
    }

    .empty-cart h2 {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    }

    .empty-cart p {
        color: #64748b;
        margin-bottom: 0.5rem;
    }

    .auth-button {
        background: var(--primary);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
        margin-top: 1rem;
    }

    .auth-button:hover {
        background: var(--primary-hover);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
    }

    </style>
</head>

<body>
    <!-- Шапка (аналогичная index.php) -->
    <?php include 'header.php'; ?>

    <div class="cart-container">
        <h1>Ваша корзина</h1>

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <h2>Ваша корзина пуста</h2>
                <p>Добавьте товары из каталога</p>
                <a href="/" class="auth-button">Перейти в каталог</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item" data-product-id="<?= $item['id'] ?>">
                        <img src="<?= $item['image_url'] ?>" alt="<?= $item['name'] ?>" class="cart-item-image">

                        <div class="cart-item-details">
                            <h3 class="cart-item-title"><?= htmlspecialchars($item['name']) ?></h3>

                            <div class="cart-item-meta">
                                <span class="cart-item-category"><?= $item['category'] ?></span>
                                <?php if ($item['subcategory']): ?>
                                    <span class="cart-item-subcategory"><?= $item['subcategory'] ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="cart-item-description">
                                <?= nl2br(htmlspecialchars($item['description'])) ?>
                            </div>
                        </div>

                        <div class="cart-item-actions">
                            <div class="cart-item-quantity">
                                <button class="quantity-btn minus" data-product-id="<?= $item['id'] ?>">−</button>
                                <span class="quantity"><?= $item['quantity'] ?></span>
                                <button class="quantity-btn plus" data-product-id="<?= $item['id'] ?>">+</button>
                            </div>
                            <button class="remove-btn" data-product-id="<?= $item['id'] ?>">
                                <svg viewBox="0 0 24 24" width="18">
                                    <path
                                        d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-actions">
                <button id="checkout-btn" class="checkout-btn">
                    <svg viewBox="0 0 24 24" width="20" fill="currentColor" style="margin-right: 8px;">
                        <path
                            d="M18 6h-2c0-2.21-1.79-4-4-4S8 3.79 8 6H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-6-2c1.1 0 2 .9 2 2h-4c0-1.1.9-2 2-2zm6 16H6V8h2v2c0 .55.45 1 1 1s1-.45 1-1V8h4v2c0 .55.45 1 1 1s1-.45 1-1V8h2v12z" />
                    </svg>
                    Оформить заказ
                </button>
            </div>

            <style>
                .checkout-btn {
                    background: var(--primary);
                    color: white;
                    border: none;
                    padding: 1rem 1.5rem;
                    border-radius: 8px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.2s;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    width: 100%;
                    margin-top: 1.5rem;
                }

                .checkout-btn:hover {
                    background: var(--primary-hover);
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
                }

                .checkout-btn:disabled {
                    background: #94a3b8;
                    cursor: not-allowed;
                    transform: none;
                    box-shadow: none;
                }

                .cart-actions {
                    margin-top: 2rem;
                }
            </style>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Обработчики для кнопок +/-
            document.querySelectorAll('.quantity-btn').forEach(btn => {
                btn.addEventListener('click', async function () {
                    const productId = this.dataset.productId;
                    const isPlus = this.classList.contains('plus');
                    const quantityElement = this.parentElement.querySelector('.quantity');

                    try {
                        const response = await fetch('api/update_cart.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                product_id: productId,
                                action: isPlus ? 'increase' : 'decrease'
                            })
                        });

                        const result = await response.json();

                        if (result.success) {
                            quantityElement.textContent = result.new_quantity;

                            // Если количество стало 0, удаляем элемент
                            if (result.new_quantity <= 0) {
                                document.querySelector(`.cart-item[data-product-id="${productId}"]`).remove();
                                checkEmptyCart();
                            }
                        } else {
                            showAlert('Ошибка', result.message, 'error');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showAlert('Ошибка', 'Ошибка при обновлении корзины', 'error');
                    }
                });
            });

            // Обработчики для кнопок удаления
            document.querySelectorAll('.remove-btn').forEach(btn => {
                btn.addEventListener('click', async function () {
                    const productId = this.dataset.productId;

                    if (confirm('Удалить товар из корзины?')) {
                        try {
                            const response = await fetch('api/update_cart.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    product_id: productId,
                                    action: 'remove'
                                })
                            });

                            const result = await response.json();

                            if (result.success) {
                                document.querySelector(`.cart-item[data-product-id="${productId}"]`).remove();
                                checkEmptyCart();
                            } else {
                                showAlert('Ошибка', result.message, 'error');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            showAlert('Ошибка', 'Ошибка при удалении товара', 'error');
                        }
                    }
                });
            });

            // Обработчик для кнопки "Оформить заказ"
            document.getElementById('checkout-btn').addEventListener('click', async function () {
                if (!confirm('Подтвердить оформление заказа?')) return;

                this.disabled = true;
                this.textContent = 'Оформляем...';

                try {
                    const response = await fetch('api/checkout.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        showAlert('Успешно', 'Заказ оформлен!', 'success');
                        setTimeout(() => {
                            window.location.href = '/';
                        }, 2000);
                    } else {
                        showAlert('Ошибка', result.message, 'error');
                        this.disabled = false;
                        this.textContent = 'Оформить заказ';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showAlert('Ошибка', 'Ошибка при оформлении заказа', 'error');
                    this.disabled = false;
                    this.textContent = 'Оформить заказ';
                }
            });

            // Проверка пустой корзины
            function checkEmptyCart() {
                if (document.querySelectorAll('.cart-item').length === 0) {
                    window.location.reload();
                }
            }

            // Функция показа уведомлений
            function showAlert(title, message, type) {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type}`;
                alertDiv.innerHTML = `
            <h3>${title}</h3>
            <p>${message}</p>
        `;
                document.body.appendChild(alertDiv);

                setTimeout(() => {
                    alertDiv.classList.add('hide');
                    setTimeout(() => alertDiv.remove(), 300);
                }, 3000);
            }
        });
    </script>

    <style>
        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            color: white;
            max-width: 300px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
        }

        .alert-success {
            background: #10b981;
        }

        .alert-error {
            background: #ef4444;
        }

        .alert.hide {
            animation: slideOut 0.3s ease-in forwards;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
            }

            to {
                transform: translateX(0);
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(100%);
            }
        }
    </style>
</body>

</html>