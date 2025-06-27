<?php
session_start();
require_once 'config.php';

// Функция для получения количества товаров в корзине
function getCartCount($pdo, $userId = null) {
    $count = 0;
    
    try {
        if ($userId) {
            // Для авторизованных пользователей
            $stmt = $pdo->prepare("
                SELECT SUM(quantity) as total 
                FROM cart 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            $count = $result['total'] ?? 0;
        } elseif (isset($_SESSION['guest_cart'])) {
            // Для гостей
            $count = array_sum($_SESSION['guest_cart']);
            
            // Если гость авторизовался, переносим его корзину
            if ($userId && !empty($_SESSION['guest_cart'])) {
                transferGuestCart($pdo, $userId, $_SESSION['guest_cart']);
                unset($_SESSION['guest_cart']);
            }
        }
    } catch(PDOException $e) {
        error_log("Cart error: " . $e->getMessage());
    }
    
    return $count;
}

// Функция переноса гостевой корзины в учетную запись пользователя
function transferGuestCart($pdo, $userId, $guestCart) {
    try {
        $pdo->beginTransaction();
        
        foreach ($guestCart as $productId => $quantity) {
            // Проверяем, есть ли уже такой товар в корзине пользователя
            $stmt = $pdo->prepare("
                SELECT quantity FROM cart 
                WHERE user_id = ? AND product_id = ?
            ");
            $stmt->execute([$userId, $productId]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Обновляем количество
                $stmt = $pdo->prepare("
                    UPDATE cart SET quantity = quantity + ? 
                    WHERE user_id = ? AND product_id = ?
                ");
                $stmt->execute([$quantity, $userId, $productId]);
            } else {
                // Добавляем новый товар
                $stmt = $pdo->prepare("
                    INSERT INTO cart (user_id, product_id, quantity) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$userId, $productId, $quantity]);
            }
        }
        
        $pdo->commit();
    } catch(PDOException $e) {
        $pdo->rollBack();
        error_log("Cart transfer error: " . $e->getMessage());
    }
}

// Получаем количество товаров в корзине
$cartCount = getCartCount($pdo, $_SESSION['user_id'] ?? null);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sovenie - Ювелирные изделия</title>
    <link rel="stylesheet" href="catalog_style.css">
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
    <style>
        /* Стили для модального окна остаются без изменений */
    </style>
</head>
<body>
    <header>
    <div class="header-container">
        <div class="logo">
            <a href="mainpage.php">
                <img src="images/logo.png" alt="Логотип Sovenie">
            </a>
        </div>
        
        <nav class="main-nav">
            <ul>
                <li><a href="catalog.php" class="<?= basename($_SERVER['PHP_SELF']) === 'catalog.php' ? 'active' : '' ?>">КАТАЛОГ</a></li>
                <li><a href="new.php" class="<?= basename($_SERVER['PHP_SELF']) === 'new.php' ? 'active' : '' ?>">НОВИНКИ</a></li>
                <li><a href="shops.php" class="<?= basename($_SERVER['PHP_SELF']) === 'shops.php' ? 'active' : '' ?>">МАГАЗИНЫ</a></li>
                <li><a href="contacts.php" class="<?= basename($_SERVER['PHP_SELF']) === 'contacts.php' ? 'active' : '' ?>">КОНТАКТЫ</a></li>
            </ul>
        </nav>
        
        <div class="user-actions">
            <a href="#" class="user-icon" id="user-icon" title="<?= isset($_SESSION['user_id']) ? 'Личный кабинет' : 'Войти' ?>">
                <img src="images/<?= isset($_SESSION['user_id']) ? 'user-icon.png' : 'login-icon.png' ?>" alt="<?= isset($_SESSION['user_id']) ? 'Личный кабинет' : 'Войти' ?>">
            </a>
            
            <a href="cart.php" class="cart-icon" title="Корзина">
                <img src="images/cart-icon.png" alt="Корзина">
                <?php if ($cartCount > 0): ?>
                    <span class="cart-counter"><?= $cartCount ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>
</header>
<!-- Модальное окно авторизации -->
<div class="auth-modal" id="auth-modal" style="display: none;">
    <div class="auth-content">
        <span class="close-modal">&times;</span>
        
        <div class="auth-tabs">
            <button class="auth-tab active" data-tab="login">Вход</button>
            <button class="auth-tab" data-tab="register">Регистрация</button>
        </div>
        
        <form id="login-form" class="auth-form active" data-action="login">
            <div class="form-group">
                <label for="login-email">Email:</label>
                <input type="email" id="login-email" name="email" required>
            </div>
            <div class="form-group">
                <label for="login-password">Пароль:</label>
                <input type="password" id="login-password" name="password" required>
            </div>
            <button type="submit" class="auth-submit">Войти</button>
        </form>
        
        <form id="register-form" class="auth-form" data-action="register">
            <div class="form-group">
                <label for="register-name">Имя:</label>
                <input type="text" id="register-name" name="name" required>
            </div>
            <div class="form-group">
                <label for="register-email">Email:</label>
                <input type="email" id="register-email" name="email" required>
            </div>
            <div class="form-group">
                <label for="register-password">Пароль:</label>
                <input type="password" id="register-password" name="password" required minlength="6">
            </div>
            <button type="submit" class="auth-submit">Зарегистрироваться</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const authModal = document.getElementById('auth-modal');
    const userIcon = document.getElementById('user-icon');
    const closeModal = document.querySelector('.close-modal');
    const authTabs = document.querySelectorAll('.auth-tab');
    const authForms = document.querySelectorAll('.auth-form');
    
    // Открытие модального окна
    userIcon?.addEventListener('click', function(e) {
        e.preventDefault();
        authModal.style.display = 'flex';
    });
    
    // Закрытие модального окна
    closeModal?.addEventListener('click', function() {
        authModal.style.display = 'none';
    });
    
    authModal?.addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    });
    
    // Переключение между вкладками
    authTabs?.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            
            // Активируем выбранную вкладку
            authTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Показываем соответствующую форму
            authForms.forEach(form => {
                form.classList.remove('active');
                if (form.dataset.action === tabName) {
                    form.classList.add('active');
                }
            });
        });
    });
    
    // Обработка форм
    document.getElementById('login-form')?.addEventListener('submit', handleAuthForm);
    document.getElementById('register-form')?.addEventListener('submit', handleAuthForm);
    
    function handleAuthForm(e) {
        e.preventDefault();
        const form = e.target;
        const action = form.dataset.action;
        const formData = new FormData(form);
        
        fetch(`auth_process.php?action=${action}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(action === 'login' ? 'Вы успешно вошли!' : 'Регистрация прошла успешно!');
                authModal.style.display = 'none';
                form.reset();
                
                // Обновляем иконку пользователя
                const userIconImg = document.querySelector('#user-icon img');
                if (userIconImg) {
                    userIconImg.src = 'images/user-icon.png';
                    userIconImg.alt = 'Личный кабинет';
                    userIcon.title = 'Личный кабинет';
                }
            } else {
                alert(data.message || 'Произошла ошибка');
            }
        })
        .catch(() => alert('Ошибка соединения с сервером'));
    }
    
    // AJAX обновление корзины
    function updateCartCounter() {
        fetch('api/get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                const counter = document.querySelector('.cart-counter');
                if (data.count > 0) {
                    if (!counter) {
                        const cartIcon = document.querySelector('.cart-icon');
                        const span = document.createElement('span');
                        span.className = 'cart-counter';
                        cartIcon.appendChild(span);
                    }
                    document.querySelector('.cart-counter').textContent = data.count;
                } else if (counter) {
                    counter.remove();
                }
            });
    }

    // Обновляем счетчик при событиях
    document.addEventListener('cartUpdated', updateCartCounter);
});
</script>