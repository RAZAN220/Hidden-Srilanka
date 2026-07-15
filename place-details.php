<?php
include_once 'includes/config.php';
include_once 'includes/session.php';
include_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { redirect('explore.php'); }

$stmt = $pdo->prepare("SELECT p.*, c.category_name, u.full_name as contributor,
                        (SELECT AVG(rating) FROM reviews WHERE place_id = p.id) as avg_rating,
                        (SELECT COUNT(*) FROM reviews WHERE place_id = p.id) as review_count
                        FROM places p
                        JOIN categories c ON p.category_id = c.id
                        JOIN users u ON p.user_id = u.id
                        WHERE p.id = ? AND p.status = 'approved'");
$stmt->execute([$id]);
$place = $stmt->fetch();
if (!$place) { redirect('explore.php'); }

// Images
$imgStmt = $pdo->prepare("SELECT image FROM place_images WHERE place_id = ?");
$imgStmt->execute([$id]);
$images = $imgStmt->fetchAll();

// Reviews
$revStmt = $pdo->prepare("SELECT r.*, u.full_name, u.profile_image FROM reviews r
                          JOIN users u ON r.user_id = u.id
                          WHERE r.place_id = ? ORDER BY r.created_at DESC");
$revStmt->execute([$id]);
$reviews = $revStmt->fetchAll();

// Check if user already reviewed
$userReview = null;
if (isLoggedIn()) {
    $check = $pdo->prepare("SELECT * FROM reviews WHERE place_id = ? AND user_id = ?");
    $check->execute([$id, $_SESSION['user_id']]);
    $userReview = $check->fetch();
}
?>
<div class="container place-detail">
    <div class="detail-header">
        <h1><?= htmlspecialchars($place['title']) ?></h1>
        <div class="detail-meta">
            <span class="category"><i class="<?= $place['category_icon'] ?? 'fas fa-tag' ?>"></i> <?= $place['category_name'] ?></span>
            <span class="location"><i class="fas fa-map-pin"></i> <?= htmlspecialchars($place['district']) ?>, <?= htmlspecialchars($place['province']) ?></span>
            <span class="rating"><i class="fas fa-star"></i> <?= number_format($place['avg_rating'] ?? 0, 1) ?> (<?= $place['review_count'] ?? 0 ?> reviews)</span>
        </div>
    </div>
    <div class="detail-gallery">
        <?php if ($images): ?>
            <div class="gallery-grid">
                <?php foreach ($images as $img): ?>
                    <img src="<?= BASE_URL ?>uploads/<?= $img['image'] ?>" alt="Place image">
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <img src="<?= BASE_URL ?>assets/images/placeholder.jpg" alt="No image">
        <?php endif; ?>
    </div>
    <div class="detail-info">
        <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($place['description'])) ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($place['address']) ?></p>
        <p><strong>Entry Fee:</strong> <?= $place['entry_fee'] ? 'LKR '.number_format($place['entry_fee'],2) : 'Free' ?></p>
        <p><strong>Opening Hours:</strong> <?= htmlspecialchars($place['opening_hours']) ?></p>
        <p><strong>Contact:</strong> <?= htmlspecialchars($place['contact_number']) ?></p>
        <p><strong>Added by:</strong> <?= htmlspecialchars($place['contributor']) ?></p>
    </div>
    <div class="detail-map" id="detailMap" data-lat="<?= $place['latitude'] ?>" data-lng="<?= $place['longitude'] ?>"></div>

    <!-- Reviews Section -->
    <div class="reviews-section">
        <h3>Reviews</h3>
        <?php if (isLoggedIn() && !$userReview): ?>
            <form method="POST" action="submit-review.php" class="review-form">
                <input type="hidden" name="place_id" value="<?= $place['id'] ?>">
                <div class="form-group">
                    <label>Rating (1-5)</label>
                    <input type="number" name="rating" min="1" max="5" required>
                </div>
                <div class="form-group">
                    <label>Comment</label>
                    <textarea name="comment" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Review</button>
            </form>
        <?php elseif ($userReview): ?>
            <p>You already reviewed this place.</p>
        <?php else: ?>
            <p><a href="login.php">Login</a> to leave a review.</p>
        <?php endif; ?>
        <div class="reviews-list">
            <?php foreach ($reviews as $rev): ?>
                <div class="review-item">
                    <div class="review-user">
                        <img src="<?= BASE_URL ?>uploads/<?= $rev['profile_image'] ?: 'default-avatar.png' ?>" alt="Avatar">
                        <span><?= htmlspecialchars($rev['full_name']) ?></span>
                    </div>
                    <div class="review-content">
                        <span class="review-rating"><?= str_repeat('⭐', $rev['rating']) ?></span>
                        <p><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
                        <small><?= date('M d, Y', strtotime($rev['created_at'])) ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($reviews)): ?>
                <p>No reviews yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
    // Init map
    const lat = parseFloat(document.getElementById('detailMap').dataset.lat);
    const lng = parseFloat(document.getElementById('detailMap').dataset.lng);
    if (lat && lng) {
        const map = L.map('detailMap').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        L.marker([lat, lng]).addTo(map);
    }
</script>
<?php include_once 'includes/footer.php'; ?>