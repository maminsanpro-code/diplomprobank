<?php
require_once '../db_config.php';
checkAuth();
$user = getCurrentUser();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("
            SELECT r.*, c.full_name as client_name, u.full_name as consultant_name 
            FROM requests r 
            JOIN clients c ON r.client_id = c.id 
            JOIN users u ON r.consultant_id = u.id 
            WHERE r.id = ?
        ");
        $stmt->execute([$_GET['id']]);
        echo json_encode($stmt->fetch());
    } else {
        $sql = "
            SELECT r.*, c.full_name as client_name, u.full_name as consultant_name 
            FROM requests r 
            JOIN clients c ON r.client_id = c.id 
            JOIN users u ON r.consultant_id = u.id 
            WHERE 1=1
        ";
        
        if ($user['role'] !== 'admin') {
            $sql .= " AND r.consultant_id = " . $user['id'];
        }
        
        if (isset($_GET['status']) && !empty($_GET['status'])) {
            $sql .= " AND r.status = '" . $_GET['status'] . "'";
        }
        
        $sql .= " ORDER BY r.created_at DESC";
        
        $stmt = $pdo->query($sql);
        echo json_encode($stmt->fetchAll());
    }
    
} elseif ($method === 'POST') {
    // Получаем данные из FormData
    $id = $_POST['id'] ?? '';
    $client_id = $_POST['client_id'] ?? '';
    $topic = $_POST['topic'] ?? '';
    $description = $_POST['description'] ?? '';
    $status = $_POST['status'] ?? 'open';
    $priority = $_POST['priority'] ?? 'medium';
    $deadline = $_POST['deadline'] ?? null;
    
    // Если deadline пустой, устанавливаем NULL
    if (empty($deadline)) {
        $deadline = null;
    }
    
    try {
        if (!empty($id)) {
            // Обновление существующей заявки
            $stmt = $pdo->prepare("
                UPDATE requests 
                SET client_id = ?, 
                    topic = ?, 
                    description = ?, 
                    status = ?, 
                    priority = ?, 
                    deadline = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $result = $stmt->execute([$client_id, $topic, $description, $status, $priority, $deadline, $id]);
        } else {
            // Создание новой заявки
            $stmt = $pdo->prepare("
                INSERT INTO requests (client_id, consultant_id, topic, description, status, priority, deadline, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $result = $stmt->execute([$client_id, $user['id'], $topic, $description, $status, $priority, $deadline]);
        }
        
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ошибка выполнения запроса']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка БД: ' . $e->getMessage()]);
    }
    
} elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? 0;
    $stmt = $pdo->prepare("DELETE FROM requests WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
}
?>