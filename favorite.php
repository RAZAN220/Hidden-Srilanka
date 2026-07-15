<?php
include_once 'includes/config.php';
include_once 'includes/session.php';
if (!isLoggedIn()) redirect('login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['place_id'])) {
    redirect('explore.php');
}

$place_id = (int)$_POST['place_id'];
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT id FROM favorites WHERE user_id = ? AND place_id = ?');
$stmt->execute([$user_id, $place_id]);
$fav = $stmt->fetch();

if ($fav) {
    $delete = $pdo->prepare('DELETE FROM favorites WHERE id = ?');
    $delete->execute([$fav['id']]);
} else {
    $insert = $pdo->prepare('INSERT INTO favorites (user_id, place_id, created_at) VALUES (?, ?, NOW())');
    $insert->execute([$user_id, $place_id]);
}

$redirect = isset($_POST['return_to']) ? $_POST['return_to'] : 'place-details.php?id=' . $place_id;
redirect($redirect);
?>