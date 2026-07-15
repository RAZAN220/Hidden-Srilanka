<?php
include_once 'includes/config.php';
include_once 'includes/session.php';
include_once 'includes/header.php';

$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$district = isset($_GET['district']) ? trim($_GET['district']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT p.*, c.category_name, c.category_icon, (SELECT AVG(rating) FROM reviews WHERE place_id = p.id) as avg_rating, (SELECT image FROM place_images WHERE place_id = p.id LIMIT 1) as image FROM places p JOIN categories c ON p.category_id = c.id WHERE p.status = 'approved'";
$params = [];

if ($category) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category;
}
if ($district) {
    $sql .= " AND p.district = ?";
    $params[] = $district;
}
if ($search) {
    $sql .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.district LIKE ? OR p.province LIKE ? )";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}
$sql .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$places = $stmt->fetchAll();

$catStmt = $pdo->query('SELECT * FROM categories ORDER BY category_name');
$categories = $catStmt->fetchAll();
$distStmt = $pdo->query('SELECT DISTINCT district FROM places WHERE district IS NOT NULL ORDER BY district');
$districts = $distStmt->fetchAll();
?>
<div class="container explore-page">
    <h1>Explore Places</h1>
    <div class="filters">
        <form method="GET" class="filter-form">
            <div class="form-group">
                <input type="text" name="search" placeholder="Search by title, district, or description" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="form-group">
                <select name="category">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <select name="district">
                    <option value="">All Districts</option>
                    <?php foreach ($districts as $d): ?>
                        <option value="<?= htmlspecialchars($d['district']) ?>" <?= $district == $d['district'] ? 'selected' : '' ?>><?= htmlspecialchars($d['district']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>
    <div class="places-grid">
        <?php foreach ($places as $place): ?>
            <div class="place-card">
                <div class="place-image" style="background-image: url('<?= BASE_URL ?>uploads/<?= htmlspecialchars($place['image'] ?: 'assets/images/placeholder.svg') ?>')">
                    <span class="place-status <?= htmlspecialchars($place['status']) ?>"><?= htmlspecialchars($place['status']) ?></span>
                </div>
                <div class="place-info">
                    <h3><a href="place-details.php?id=<?= $place['id'] ?>"><?= htmlspecialchars($place['title']) ?></a></h3>
                    <p class="place-location"><i class="fas fa-map-pin"></i> <?= htmlspecialchars($place['district']) ?>, <?= htmlspecialchars($place['province']) ?></p>
                    <div class="place-meta">
                        <span class="place-category"><i class="<?= htmlspecialchars($place['category_icon'] ?: 'fas fa-tag') ?>"></i> <?= htmlspecialchars($place['category_name']) ?></span>
                        <span class="place-rating"><i class="fas fa-star"></i> <?= number_format($place['avg_rating'] ?? 0, 1) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($places)): ?>
            <p class="text-center">No places found.</p>
        <?php endif; ?>
    </div>
</div>
<?php include_once 'includes/footer.php'; ?>
