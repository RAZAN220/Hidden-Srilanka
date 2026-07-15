<?php
include_once '../includes/config.php';
include_once '../includes/session.php';
if (!isLoggedIn()) redirect('login.php');

$place_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

if ($place_id) {
    $stmt = $pdo->prepare('SELECT id FROM places WHERE id = ? AND user_id = ?');
    $stmt->execute([$place_id, $user_id]);
    $place = $stmt->fetch();
    if ($place) {
        $imgStmt = $pdo->prepare('SELECT image FROM place_images WHERE place_id = ?');
        $imgStmt->execute([$place_id]);
        $images = $imgStmt->fetchAll();
        foreach ($images as $image) {
            $path = '../uploads/' . $image['image'];
            if (file_exists($path)) {
                @unlink($path);
            }
        }
        $pdo->prepare('DELETE FROM place_images WHERE place_id = ?')->execute([$place_id]);
        $pdo->prepare('DELETE FROM favorites WHERE place_id = ?')->execute([$place_id]);
        $pdo->prepare('DELETE FROM reviews WHERE place_id = ?')->execute([$place_id]);
        $pdo->prepare('DELETE FROM places WHERE id = ?')->execute([$place_id]);
    }
}
redirect('my-places.php');
?>