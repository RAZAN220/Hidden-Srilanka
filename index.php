<?php
include_once 'includes/config.php';
include_once 'includes/session.php';
include_once 'includes/header.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM places p WHERE p.category_id = c.id AND p.status='approved') AS place_count FROM categories c ORDER BY category_name")->fetchAll();
$featured = $pdo->query("SELECT p.*, c.category_name, c.category_icon, (SELECT image FROM place_images WHERE place_id = p.id LIMIT 1) AS image, (SELECT AVG(rating) FROM reviews WHERE place_id = p.id) AS avg_rating FROM places p JOIN categories c ON p.category_id = c.id WHERE p.status = 'approved' ORDER BY p.created_at DESC LIMIT 6")->fetchAll();
$gems = $pdo->query("SELECT p.*, c.category_name, c.category_icon, (SELECT image FROM place_images WHERE place_id = p.id LIMIT 1) AS image, (SELECT AVG(rating) FROM reviews WHERE place_id = p.id) AS avg_rating FROM places p JOIN categories c ON p.category_id = c.id WHERE p.status = 'approved' ORDER BY RAND() LIMIT 4")->fetchAll();
$totalPlaces = $pdo->query("SELECT COUNT(*) FROM places WHERE status = 'approved'")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalReviews = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
?>
<div class="hero">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1>Discover Sri Lanka's Hidden Gems</h1>
        <p>Explore authentic local experiences, secret spots, and off-the-beaten-path destinations curated by the community.</p>
        <form action="explore.php" method="GET" class="search-form">
            <div class="search-input-group">
                <input type="text" name="search" placeholder="Search by title, district, or keywords" value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Search</button>
            </div>
        </form>
        <div class="hero-stats">
            <div>
                <span class="stat-number"><?= $totalPlaces ?></span>
                <span class="stat-label">Approved Places</span>
            </div>
            <div>
                <span class="stat-number"><?= $totalUsers ?></span>
                <span class="stat-label">Community Members</span>
            </div>
            <div>
                <span class="stat-number"><?= $totalReviews ?></span>
                <span class="stat-label">Reviews</span>
            </div>
        </div>
    </div>
</div>
<div class="container section">
    <div class="section-header">
        <h2>Explore by <span class="highlight">Category</span></h2>
    </div>
    <div class="categories-grid">
        <?php foreach ($categories as $category): ?>
            <a href="explore.php?category=<?= $category['id'] ?>" class="category-card">
                <div class="category-icon"><i class="<?= htmlspecialchars($category['category_icon'] ?: 'fas fa-map-marker-alt') ?>"></i></div>
                <h3><?= htmlspecialchars($category['category_name']) ?></h3>
                <p><?= $category['place_count'] ?> places</p>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<div class="container section">
    <div class="section-header">
        <h2>Featured <span class="highlight">Places</span></h2>
    </div>
    <div class="places-grid">
        <?php if (empty($featured)): ?>
            <p>No featured places yet. Check back soon!</p>
        <?php endif; ?>
        <?php foreach ($featured as $place): ?>
            <div class="place-card">
                <div class="place-image" style="background-image: url('<?= $place['image'] ? BASE_URL . 'uploads/' . htmlspecialchars($place['image']) : BASE_URL . 'assets/images/placeholder.svg' ?>')"></div>
                <div class="place-info">
                    <h3><a href="place-details.php?id=<?= $place['id'] ?>"><?= htmlspecialchars($place['title']) ?></a></h3>
                    <div class="place-meta">
                        <span class="place-category"><i class="<?= htmlspecialchars($place['category_icon'] ?: 'fas fa-tag') ?>"></i> <?= htmlspecialchars($place['category_name']) ?></span>
                        <span class="place-rating"><i class="fas fa-star"></i> <?= number_format($place['avg_rating'] ?? 0, 1) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<div class="container section cta-section">
    <div class="cta-container">
        <div class="cta-content">
            <h2>Share Your Hidden Sri Lanka</h2>
            <p>Know a secret waterfall or a local restaurant worth visiting? Submit it and help travelers discover a new side of Sri Lanka.</p>
            <a href="user/add-place.php" class="btn btn-primary">Add New Place</a>
        </div>
        <div class="cta-image"><i class="fas fa-compass"></i></div>
    </div>
</div>
<div class="container section">
    <div class="section-header">
        <h2>Hidden <span class="highlight">Gems</span></h2>
    </div>
    <div class="places-grid">
        <?php if (empty($gems)): ?>
            <p>No hidden gems available yet.</p>
        <?php endif; ?>
        <?php foreach ($gems as $place): ?>
            <div class="place-card">
                <div class="place-image" style="background-image: url('<?= BASE_URL ?>uploads/<?= htmlspecialchars($place['image'] ?: 'assets/images/placeholder.svg') ?>')">
                    <span class="gem-badge">Hidden Gem</span>
                </div>
                <div class="place-info">
                    <h3><a href="place-details.php?id=<?= $place['id'] ?>"><?= htmlspecialchars($place['title']) ?></a></h3>
                    <div class="place-meta">
                        <span class="place-category"><i class="<?= htmlspecialchars($place['category_icon'] ?: 'fas fa-tag') ?>"></i> <?= htmlspecialchars($place['category_name']) ?></span>
                        <span class="place-rating"><i class="fas fa-star"></i> <?= number_format($place['avg_rating'] ?? 0, 1) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include_once 'includes/footer.php'; ?>
