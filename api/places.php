<?php
header('Content-Type: application/json');
include_once '../includes/config.php';

$featured = isset($_GET['featured']);
$hidden = isset($_GET['hidden']);
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

$sql = "SELECT p.*, c.category_name, c.category_icon,
               (SELECT AVG(rating) FROM reviews WHERE place_id = p.id) as avg_rating,
               (SELECT image FROM place_images WHERE place_id = p.id LIMIT 1) as image
        FROM places p
        JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'approved'";
if ($hidden) {
    $sql .= " ORDER BY RANDOM() LIMIT $limit";
} else {
    $sql .= " ORDER BY p.created_at DESC LIMIT $limit";
}
$stmt = $pdo->query($sql);
$places = $stmt->fetchAll();
echo json_encode($places);
?>