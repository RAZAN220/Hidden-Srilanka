<?php
header('Content-Type: application/json');
include_once '../includes/config.php';

$stmt = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM places WHERE category_id = c.id AND status='approved') as place_count FROM categories c ORDER BY c.category_name");
$cats = $stmt->fetchAll();
echo json_encode($cats);
?>