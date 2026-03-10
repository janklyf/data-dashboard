<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$dbFile = __DIR__ . '/dashboard.db';
try {
    $db = new PDO("sqlite:" . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => '数据库连接失败']);
    exit;
}

// 获取所有分组
$stmt = $db->query('SELECT * FROM groups ORDER BY id ASC');
$groups = $stmt->fetchAll();

// 获取所有数据块
$stmt = $db->query('SELECT b.*, g.name as group_name FROM blocks b LEFT JOIN groups g ON b.group_id = g.id ORDER BY b.id ASC');
$blocks = $stmt->fetchAll();

echo json_encode([
    'success' => true,
    'groups' => $groups,
    'blocks' => $blocks
]);
