<?php
include_once 'includes/config.php';
include_once 'includes/session.php';
if (!isLoggedIn()) redirect('login.php');

$place_id = isset($_POST['place_id']) ? (int)$_POST['place_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$comment = trim($_POST['comment'] ?? '');

if (!$place_id || $rating < 1 || $rating > 5 || $comment === '') {
    redirect('place-details.php?id=' . $place_id);
}

$check = $pdo->prepare('SELECT id FROM reviews WHERE place_id = ? AND user_id = ?');
$check->execute([$place_id, $_SESSION['user_id']]);
$existing = $check->fetch();

if ($existing) {
    $stmt = $pdo->prepare('UPDATE reviews SET rating = ?, comment = ?, created_at = NOW() WHERE id = ?');
    $stmt->execute([$rating, $comment, $existing['id']]);
} else {
    $stmt = $pdo->prepare('INSERT INTO reviews (place_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())');
    $stmt->execute([$place_id, $_SESSION['user_id'], $rating, $comment]);
}

redirect('place-details.php?id=' . $place_id);
?>
