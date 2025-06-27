<?php
include 'config.php';
require_once 'header.php';
// Данные товаров в корзине
$cartItems = [
    [
        'id' => 1,
        'title' => 'Обручальные кольца золото 585',
        'size' => '18 размер',
        'material' => 'Золото',
        'quantity' => 1,
        'price' => 132900,
        'image' => 'images/ring1.jpg'
    ],
    [
        'id' => 2,
        'title' => 'Обручальные кольца золото 585',
        'size' => '15 размер',
        'material' => 'Золото',
        'quantity' => 2,
        'price' => 132900,
        'image' => 'images/ring2.jpg'
    ],
    [
        'id' => 3,
        'title' => 'Обручальные кольца золото 585',
        'size' => '21 размер',
        'material' => 'Золото',
        'quantity' => 7,
        'price' => 132900,
        'image' => 'images/ring3.jpg'
    ]
];

// Обработка изменения количества товаров
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = $_POST['item_id'] ?? null;
    $action = $_POST['action'] ?? null;
    
    foreach ($cartItems as &$item) {
        if ($item['id'] == $itemId) {
            if ($action === 'increase') {
                $item['quantity']++;
            } elseif ($action === 'decrease' && $item['quantity'] > 1) {
                $item['quantity']--;
            }
            break;
        }
    }
}

// Расчет итоговой суммы
$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>sovenie - Корзина</title>
    <link rel="stylesheet" href="cart_style.css">
</head>
<body>
    

    <main class="cart-container">
        <h1>Корзина</h1>
        
        <?php foreach ($cartItems as $item): ?>
        <div class="cart-item">
            <div class="item-image">
                <img src="<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
            </div>
            
            <div class="item-info">
                <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                <p><?php echo htmlspecialchars($item['size']); ?></p>
                <p><?php echo htmlspecialchars($item['material']); ?></p>
            </div>
            
            <div class="item-quantity">
                <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, 'decrease')">-</button>
                <span><?php echo $item['quantity']; ?></span>
                <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, 'increase')">+</button>
            </div>
            
            <div class="item-price">
                <?php echo number_format($item['price'] * $item['quantity'], 0, '', ' '); ?> ₽
            </div>
        </div>
        <?php endforeach; ?>
        
        <div class="cart-total">
            <span>Итого</span>
            <span><?php echo number_format($total, 0, '', ' '); ?> ₽</span>
        </div>
        
        <button class="checkout-btn">ОФОРМИТЬ ЗАКАЗ</button>
    </main>
</body>

<?php require_once 'footer.php'; ?>
</html>