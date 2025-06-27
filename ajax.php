<?php
header('Content-Type: application/json');
require_once 'config.php';
require_once 'script.php';

$response = ['success' => false, 'message' => ''];

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';

    if ($method === 'GET') {
        if ($action === 'get_items') {
            $items = $dbHandler->getSiteItems();
            $response = ['success' => true, 'data' => $items];
        } elseif ($action === 'get_feedback') {
            $feedback = $dbHandler->getFeedbackMessages();
            $response = ['success' => true, 'data' => $feedback];
        }
    }
    elseif ($method === 'POST') {
        if ($action === 'save_feedback') {
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

            if (!$name || !$email || !$message) {
                throw new Exception('Неверные данные формы');
            }

            $dbHandler->saveFeedback($name, $email, $message);
            $response = ['success' => true, 'message' => 'Сообщение успешно отправлено!'];
        }
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(400);
}

echo json_encode($response);