<?php
require_once '../db_config.php';
checkAuth();
$user = getCurrentUser();

// Фильтр по консультанту, если не админ
$consultantFilter = ($user['role'] !== 'admin') ? " AND consultant_id = " . $user['id'] : "";

// 1. Количество заявок по статусам
$statusCount = $pdo->query("SELECT status, COUNT(*) as count FROM requests WHERE 1=1 $consultantFilter GROUP BY status")->fetchAll();

// 2. Просроченные заявки (дедлайн прошел, статус не "сделка")
$overdue = $pdo->query("SELECT COUNT(*) as count FROM requests WHERE deadline < CURDATE() AND status != 'deal' $consultantFilter")->fetch()['count'];

// 3. Заявки в работе (in_progress)
$inProgress = $pdo->query("SELECT COUNT(*) as count FROM requests WHERE status = 'in_progress' $consultantFilter")->fetch()['count'];

// 4. Среднее время выполнения (для заявок со статусом 'deal')
$avgTime = $pdo->query("SELECT AVG(DATEDIFF(updated_at, created_at)) as avg_days FROM requests WHERE status = 'deal' $consultantFilter")->fetch()['avg_days'];

// Формируем ответ
$report = [
    'statuses' => [
        'open' => 0, 'in_progress' => 0, 'postponed' => 0, 'deal' => 0
    ],
    'overdue' => (int)$overdue,
    'in_progress' => (int)$inProgress,
    'avg_completion_days' => round((float)$avgTime, 1)
];

foreach ($statusCount as $row) {
    $report['statuses'][$row['status']] = (int)$row['count'];
}

echo json_encode($report);
?>