<?php
include_once 'includes/config.php';
include_once 'includes/session.php';
if (!isLoggedIn()) redirect('login.php');

$place_id = $_POST['place_id'];
$rating = $_POST['rating'];
$comment = $_POST['comment'];

$stmt = $pdo->prepare("INSERT INTO reviews (place_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
$stmt->execute([$place_id, $_SESSION['user_id'], $rating, $comment]);
redirect('place-details.php?id=' . $place_id);
?>