<?php
include_once '../includes/config.php';
include_once '../includes/session.php';
if (!isLoggedIn()) redirect('login.php');
include_once '../includes/header.php';

$user_id = $_SESSION['user_id'];
// Get user stats
$stmt = $pdo->prepare("SELECT COUNT(*) as total_places FROM places WHERE user_id = ?");
$stmt->execute([$user_id]);
$places_count = $stmt->fetch()['total_places'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_reviews FROM reviews WHERE user_id = ?");
$stmt->execute([$user_id]);
$reviews_count = $stmt->fetch()['total_reviews'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_favs FROM favorites WHERE user_id = ?");
$stmt->execute([$user_id]);
$favs_count = $stmt->fetch()['total_favs'];
?>
<div class="container user-dashboard">
    <h1>Dashboard</h1>
    <div class="stats-grid">
        <div class="stat-card"><i class="fas fa-map-pin"></i> <span><?= $places_count ?></span> Places</div>
        <div class="stat-card"><i class="fas fa-star"></i> <span><?= $reviews_count ?></span> Reviews</div>
        <div class="stat-card"><i class="fas fa-heart"></i> <span><?= $favs_count ?></span> Favorites</div>
    </div>
    <div class="quick-actions">
        <a href="add-place.php" class="btn btn-primary">Add New Place</a>
        <a href="my-places.php" class="btn btn-secondary">My Places</a>
        <a href="profile.php" class="btn btn-secondary">Profile</a>
    </div>
</div>
<?php include_once '../includes/footer.php'; ?>