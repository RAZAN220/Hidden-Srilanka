<?php
include_once '../includes/config.php';
include_once '../includes/session.php';
if (!isLoggedIn()) redirect('login.php');

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT f.id AS fav_id, p.*, c.category_name, c.category_icon, (SELECT image FROM place_images WHERE place_id = p.id LIMIT 1) AS image FROM favorites f JOIN places p ON f.place_id = p.id JOIN categories c ON p.category_id = c.id WHERE f.user_id = ? AND p.status = \'approved\' ORDER BY f.created_at DESC');
$stmt->execute([$user_id]);
$favorites = $stmt->fetchAll();

include_once '../includes/header.php';
?>
<div class="container section favorites-page">
    <h1>Saved Favorites</h1>
    <?php if (empty($favorites)): ?>
        <p>You haven't saved any places yet. Explore the <a href="../explore.php">Explore</a> page to add favorites.</p>
    <?php else: ?>
        <div class="places-grid">
            <?php foreach ($favorites as $place): ?>
                <div class="place-card">
                    <div class="place-image" style="background-image: url('<?= BASE_URL ?>uploads/<?= $place['image'] ?: 'assets/images/placeholder.svg' ?>')"></div>
                    <div class="place-info">
                        <h3><a href="../place-details.php?id=<?= $place['id'] ?>"><?= htmlspecialchars($place['title']) ?></a></h3>
                        <p class="place-location"><i class="fas fa-map-pin"></i> <?= htmlspecialchars($place['district']) ?>, <?= htmlspecialchars($place['province']) ?></p>
                        <div class="place-meta">
                            <span class="place-category"><i class="<?= $place['category_icon'] ?: 'fas fa-tag' ?>"></i> <?= htmlspecialchars($place['category_name']) ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php include_once '../includes/footer.php'; ?>