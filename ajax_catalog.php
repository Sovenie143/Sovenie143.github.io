<?php
require_once 'config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'last_updated' => time()];

try {
    $filters = [
        'category' => $_GET['category'] ?? 'all',
        'sort' => $_GET['sort'] ?? 'popular',
        'search' => $_GET['search'] ?? '',
        'last_update' => $_GET['last_update'] ?? 0
    ];

    // Проверяем, были ли изменения с момента last_update
    $lastDbUpdate = $pdo->query("SELECT MAX(updated_at) FROM catalog_all")->fetchColumn();
    $response['last_updated'] = strtotime($lastDbUpdate) ?: time();

    // Если клиент уже имеет актуальные данные, возвращаем пустой ответ
    if ($filters['last_update'] > 0 && $response['last_updated'] <= $filters['last_update']) {
        $response['success'] = true;
        $response['products'] = [];
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT * FROM catalog_all WHERE 1=1";
    $params = [];

    if ($filters['category'] !== 'all') {
        $sql .= " AND category = ?";
        $params[] = $filters['category'];
    }

    if (!empty($filters['search']) && $filters['search'] !== 'Я ищу...') {
        $sql .= " AND name LIKE ?";
        $params[] = '%' . $filters['search'] . '%';
    }

    switch ($filters['sort']) {
        case 'price-asc':
            $sql .= " ORDER BY price ASC";
            break;
        case 'price-desc':
            $sql .= " ORDER BY price DESC";
            break;
        case 'newest':
            $sql .= " ORDER BY id DESC";
            break;
        case 'popular':
        default:
            $sql .= " ORDER BY id ASC";
            break;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    foreach ($products as &$product) {
        $product['formatted_price'] = number_format($product['price'], 0, '', ' ') . ' ₽';
        $product['availability'] = 'in-stock';
    }

    $response = [
        'success' => true,
        'products' => $products,
        'last_updated' => strtotime($lastDbUpdate) ?: time()
    ];

} catch (PDOException $e) {
    $response['message'] = 'Ошибка базы данных: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);