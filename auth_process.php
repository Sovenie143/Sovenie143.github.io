<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_GET['action'] ?? '';
        
        if ($action === 'login') {
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Введите корректный email');
            }
            
            $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($password, $user['password'])) {
                throw new Exception('Неверный email или пароль');
            }
            
            $_SESSION['user_id'] = $user['id'];
            echo json_encode(['success' => true]);
            
        } elseif ($action === 'register') {
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Введите корректный email');
            }
            
            if (strlen($password) < 6) {
                throw new Exception('Пароль должен содержать минимум 6 символов');
            }
            
            // Проверка существования пользователя
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                throw new Exception('Пользователь с таким email уже существует');
            }
            
            // Создание нового пользователя
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (email, password, name) VALUES (?, ?, ?)");
            $stmt->execute([$email, $hashedPassword, $name]);
            
            $userId = $pdo->lastInsertId();
            $_SESSION['user_id'] = $userId;
            
            echo json_encode(['success' => true]);
            
        } else {
            throw new Exception('Неизвестное действие');
        }
    } else {
        throw new Exception('Метод не поддерживается');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}