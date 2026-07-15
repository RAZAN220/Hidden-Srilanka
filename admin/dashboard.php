<?php
include_once '../includes/config.php';
include_once '../includes/session.php';
if (!isAdmin()) redirect('login.php');
include_once '../includes/header.php';

// Counts
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalPlaces = $pdo->query("SELECT COUNT(*) FROM places")->fetchColumn();
$pendingPlaces = $pdo->query("SELECT COUNT(*) FROM places WHERE status='pending'")->fetchColumn();
$totalReviews = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
?>
<div class="container admin-dashboard">
    <h1>Admin Dashboard</h1>
    <div class="stats-grid">
        <div class="stat-card"><i class="fas fa-users"></i> <?= $totalUsers ?> Users</div>
        <div class="stat-card"><i class="fas fa-map-marker-alt"></i> <?= $totalPlaces ?> Places</div>
        <div class="stat-card"><i class="fas fa-clock"></i> <?= $pendingPlaces ?> Pending</div>
        <div class="stat-card"><i class="fas fa-star"></i> <?= $totalReviews ?> Reviews</div>
    </div>
    <div class="admin-links">
        <a href="places.php" class="btn btn-primary">Manage Places</a>
        <a href="users.php" class="btn btn-secondary">Manage Users</a>
        <a href="categories.php" class="btn btn-secondary">Manage Categories</a>
        <a href="reviews.php" class="btn btn-secondary">Manage Reviews</a>
    </div>
</div>
<?php include_once '../includes/footer.php'; ?>