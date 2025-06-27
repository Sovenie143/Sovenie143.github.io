<?php
require_once 'config.php';
require_once 'header.php';

$productId = $_GET['id'] ?? 0;

try {
    $stmt = $pdo->prepare("SELECT * FROM catalog_all WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        throw new Exception("Товар не найден");
    }
} catch (PDOException $e) {
    die("Ошибка выполнения запроса: " . $e->getMessage());
} catch (Exception $e) {
    die($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> | Sovenie</title>
    <link rel="stylesheet" href="product_style.css">
</head>
<body>
    <main class="product-container">
        <div class="product-image">
            <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>
        
        <div class="product-details">
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            <p class="price"><?= number_format($product['price'], 0, '', ' ') ?> ₽</p>
            
            <?php if ($product['gold_sample']): ?>
                <p class="sample">Проба: <?= $product['gold_sample'] ?></p>
            <?php endif; ?>
            
            <p class="category">Категория: <?= htmlspecialchars($product['category']) ?></p>
            
            <div class="product-actions">
                <button class="add-to-cart">В корзину</button>
            </div>
            
            <div class="product-description">
                <h2>Описание</h2>
                <p>Подробное описание товара будет здесь.</p>
            </div>
        </div>
    </main>

    <?php require_once 'footer.php'; ?>
</body>
</html>