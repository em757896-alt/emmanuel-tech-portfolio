<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.php';

require_admin();
header('Content-Type: application/json');

$q = trim((string) ($_GET['q'] ?? ''));

$sql = "SELECT id, first_name, last_name, email, course, department, avatar_color, created_at
        FROM students WHERE 1=1";
$params = [];
if ($q !== '') {
    $sql .= ' AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR course LIKE ? OR department LIKE ?)';
    $like = '%' . $q . '%';
    $params = array_fill(0, 5, $like);
}
$sql .= ' ORDER BY last_name, first_name LIMIT 30';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
echo json_encode($stmt->fetchAll());
