<?php
require_once '../db_config.php';
checkAuth();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode($stmt->fetch());
    } else {
        $stmt = $pdo->query("SELECT * FROM clients ORDER BY full_name");
        echo json_encode($stmt->fetchAll());
    }
    
} elseif ($method === 'POST') {
    $id = $_POST['id'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $passport = $_POST['passport'] ?? '';
    
    if (empty($full_name) || empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'ФИО и телефон обязательны']);
        exit;
    }
    
    if ($id) {
        // Обновление существующего клиента
        $stmt = $pdo->prepare("UPDATE clients SET full_name = ?, phone = ?, email = ?, passport = ? WHERE id = ?");
        $stmt->execute([$full_name, $phone, $email, $passport, $id]);
    } else {
        // Создание нового клиента
        $stmt = $pdo->prepare("INSERT INTO clients (full_name, phone, email, passport) VALUES (?, ?, ?, ?)");
        $stmt->execute([$full_name, $phone, $email, $passport]);
    }
    
    echo json_encode(['success' => true]);
    
} elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? 0;
    
    // Проверяем, есть ли заявки у клиента
    $check = $pdo->prepare("SELECT COUNT(*) as count FROM requests WHERE client_id = ?");
    $check->execute([$id]);
    $result = $check->fetch();
    
    if ($result['count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Нельзя удалить клиента, у которого есть заявки']);
        exit;
    }
    
    $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
}
?>