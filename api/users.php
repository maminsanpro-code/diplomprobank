<?php
require_once '../db_config.php';
checkAuth();
$user = getCurrentUser();

// Только админ может управлять пользователями
if ($user['role'] !== 'admin') {
    http_response_code(403);
    die(json_encode(['error' => 'Access denied']));
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT id, login, full_name, role FROM users ORDER BY id");
    echo json_encode($stmt->fetchAll());
    
} elseif ($method === 'POST') {
    $id = $_POST['id'] ?? '';
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $role = $_POST['role'] ?? 'consultant';
    
    if (empty($login) || empty($full_name)) {
        echo json_encode(['success' => false, 'message' => 'Логин и ФИО обязательны']);
        exit;
    }
    
    if ($id) {
        // Обновление существующего сотрудника
        
        // Проверка уникальности логина
        $check = $pdo->prepare("SELECT id FROM users WHERE login = ? AND id != ?");
        $check->execute([$login, $id]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Логин уже занят']);
            exit;
        }
        
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET login = ?, password = ?, full_name = ?, role = ? WHERE id = ?");
            $stmt->execute([$login, $hash, $full_name, $role, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET login = ?, full_name = ?, role = ? WHERE id = ?");
            $stmt->execute([$login, $full_name, $role, $id]);
        }
        
    } else {
        // Создание нового сотрудника
        
        if (empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Пароль обязателен для нового сотрудника']);
            exit;
        }
        
        // Проверка уникальности логина
        $check = $pdo->prepare("SELECT id FROM users WHERE login = ?");
        $check->execute([$login]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Логин уже занят']);
            exit;
        }
        
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (login, password, full_name, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$login, $hash, $full_name, $role]);
    }
    
    echo json_encode(['success' => true]);
    
} elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? 0;
    
    // Нельзя удалить самого себя
    if ($id == $user['id']) {
        echo json_encode(['success' => false, 'message' => 'Нельзя удалить самого себя']);
        exit;
    }
    
    // Проверяем роль
    $check = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $check->execute([$id]);
    $userToDelete = $check->fetch();
    
    if ($userToDelete && $userToDelete['role'] === 'admin') {
        echo json_encode(['success' => false, 'message' => 'Нельзя удалить администратора']);
        exit;
    }
    
    // Проверяем, есть ли заявки у сотрудника
    $checkRequests = $pdo->prepare("SELECT COUNT(*) as count FROM requests WHERE consultant_id = ?");
    $checkRequests->execute([$id]);
    $result = $checkRequests->fetch();
    
    if ($result['count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Нельзя удалить сотрудника, у которого есть заявки']);
        exit;
    }
    
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
}
?>