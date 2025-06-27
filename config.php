<?php
// Конфигурация базы данных
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'sovenie_cart');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

try {
    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // Установка временной зоны для БД
    $pdo->exec("SET time_zone = '+00:00'");
} catch(PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

function safeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}