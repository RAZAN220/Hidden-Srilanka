<?php
include_once '../includes/config.php';
include_once '../includes/session.php';
if (!isLoggedIn()) redirect('login.php');

$uid = $_SESSION['user_id'];

// Stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM places WHERE user_id = ?"); $stmt->execute([$uid]);
$myPlaces = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM places WHERE user_id = ? AND status='approved'"); $stmt->execute([$uid]);
$myApproved = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM places WHERE user_id = ? AND status='pending'"); $stmt->execute([$uid]);
$myPending = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM places WHERE user_id = ? AND status='rejected'"); $stmt->execute([$uid]);
$myRejected = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE user_id = ?"); $stmt->execute([$uid]);
$myReviews = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?"); $stmt->execute([$uid]);
$myFavorites = $stmt->fetchColumn();

// My recent places
$stmt = $pdo->prepare(
    "SELECT p.id, p.title, p.district, p.province, p.status, p.created_at,
            c.category_name,
            (SELECT COUNT(*) FROM reviews WHERE place_id = p.id) AS review_count
     FROM places p JOIN categories c ON p.category_id = c.id
     WHERE p.user_id = ? ORDER BY p.created_at DESC LIMIT 6"
);
$stmt->execute([$uid]);
$myRecentPlaces = $stmt->fetchAll();

// Recent reviews I received on my places
$stmt = $pdo->prepare(
    "SELECT r.rating, r.comment, r.created_at, u.full_name, p.title AS place_title, p.id AS place_id
     FROM reviews r
     JOIN users u ON r.user_id = u.id
     JOIN places p ON r.place_id = p.id
     WHERE p.user_id = ?
     ORDER BY r.created_at DESC LIMIT 4"
);
$stmt->execute([$uid]);
$receivedReviews = $stmt->fetchAll();

$approvalRate = $myPlaces > 0 ? round(($myApproved / $myPlaces) * 100) : 0;

$pageTitle  = 'My Dashboard';
$activePage = 'dashboard';
$dashType   = 'user';
include_once '../includes/dashboard-header.php';
?>

<!-- ── Mini stats row ── -->
<div class="db-stats-row">
  <div class="db-stat-mini">
    <div class="db-stat-mini-icon icon-blue"><i class="fas fa-map-pin"></i></div>
    <div>
      <p class="db-stat-mini-val"><?= $myPlaces ?></p>
      <p class="db-stat-mini-lbl">My Places</p>
    </div>
  </div>
  <div class="db-stat-mini">
    <div class="db-stat-mini-icon icon-green"><i class="fas fa-check-circle"></i></div>
    <div>
      <p class="db-stat-mini-val"><?= $myApproved ?></p>
      <p class="db-stat-mini-lbl">Approved</p>
    </div>
  </div>
  <div class="db-stat-mini">
    <div class="db-stat-mini-icon icon-orange"><i class="fas fa-clock"></i></div>
    <div>
      <p class="db-stat-mini-val"><?= $myPending ?></p>
      <p class="db-stat-mini-lbl">Pending</p>
    </div>
  </div>
  <div class="db-stat-mini">
    <div class="db-stat-mini-icon icon-purple"><i class="fas fa-star"></i></div>
    <div>
      <p class="db-stat-mini-val"><?= $myReviews ?></p>
      <p class="db-stat-mini-lbl">Reviews Given</p>
    </div>
  </div>
  <div class="db-stat-mini">
    <div class="db-stat-mini-icon icon-red"><i class="fas fa-heart"></i></div>
    <div>
      <p class="db-stat-mini-val"><?= $myFavorites ?></p>
      <p class="db-stat-mini-lbl">Favorites</p>
    </div>
  </div>
</div>

<!-- ── Highlight cards + Quick actions ── -->
<div class="db-highlight-row">
  <div class="db-highlight-card card-green">
    <div class="db-highlight-icon"><i class="fas fa-check-circle"></i></div>
    <div>
      <p class="db-highlight-val"><?= $myApproved ?></p>
      <p class="db-highlight-lbl">Approved Places</p>
    </div>
  </div>
  <div class="db-highlight-card card-blue">
    <div class="db-highlight-icon"><i class="fas fa-star"></i></div>
    <div>
      <p class="db-highlight-val"><?= $myReviews ?></p>
      <p class="db-highlight-lbl">Reviews Given</p>
    </div>
  </div>
  <div class="db-quick-actions">
    <p class="db-section-title">Quick Actions</p>
    <div class="db-action-btns">
      <a href="add-place.php"  class="db-action-btn btn-action-primary"><i class="fas fa-plus-circle"></i> Add New Place</a>
      <a href="my-places.php"  class="db-action-btn btn-action-secondary"><i class="fas fa-map-pin"></i> My Places</a>
      <a href="favorites.php"  class="db-action-btn btn-action-secondary"><i class="fas fa-heart"></i> Favorites</a>
      <a href="profile.php"    class="db-action-btn btn-action-secondary"><i class="fas fa-user-circle"></i> Edit Profile</a>
    </div>
  </div>
</div>

<!-- ── Main content row ── -->
<div class="db-main-row">

  <!-- My places table -->
  <div class="db-card db-card-wide">
    <div class="db-card-header">
      <h3><i class="fas fa-map-pin"></i> My Recent Places</h3>
      <a href="my-places.php" class="db-card-link">View all <i class="fas fa-arrow-right"></i></a>
    </div>
    <?php if (empty($myRecentPlaces)): ?>
      <p class="db-empty">You haven't submitted any places yet. <a href="add-place.php">Add one now →</a></p>
    <?php else: ?>
    <table class="db-table">
      <thead>
        <tr>
          <th>Place</th>
          <th>Category</th>
          <th>Location</th>
          <th>Reviews</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($myRecentPlaces as $p): ?>
        <tr>
          <td><strong><?= htmlspecialchars($p['title']) ?></strong></td>
          <td><span class="db-tag"><?= htmlspecialchars($p['category_name']) ?></span></td>
          <td><?= htmlspecialchars($p['district']) ?>, <?= htmlspecialchars($p['province']) ?></td>
          <td><?= $p['review_count'] ?></td>
          <td><span class="badge <?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
          <td>
            <a href="<?= BASE_URL ?>place-details.php?id=<?= $p['id'] ?>" class="db-btn-xs btn-xs-primary">View</a>
            <a href="edit-place.php?id=<?= $p['id'] ?>" class="db-btn-xs btn-xs-secondary">Edit</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <!-- Side column -->
  <div class="db-card-col">

    <!-- Submission stats -->
    <div class="db-card">
      <div class="db-card-header"><h3><i class="fas fa-chart-bar"></i> My Submission Status</h3></div>
      <div class="db-progress-list">
        <div class="db-progress-item">
          <div class="db-progress-label">
            <span>Approved</span><span class="db-progress-val text-green"><?= $myApproved ?></span>
          </div>
          <div class="db-progress-bar"><div class="db-progress-fill fill-green" style="width:<?= $approvalRate ?>%"></div></div>
        </div>
        <div class="db-progress-item">
          <div class="db-progress-label">
            <span>Pending</span><span class="db-progress-val text-orange"><?= $myPending ?></span>
          </div>
          <div class="db-progress-bar"><div class="db-progress-fill fill-orange" style="width:<?= $myPlaces > 0 ? round(($myPending/$myPlaces)*100) : 0 ?>%"></div></div>
        </div>
        <div class="db-progress-item">
          <div class="db-progress-label">
            <span>Rejected</span><span class="db-progress-val text-red"><?= $myRejected ?></span>
          </div>
          <div class="db-progress-bar"><div class="db-progress-fill fill-red" style="width:<?= $myPlaces > 0 ? round(($myRejected/$myPlaces)*100) : 0 ?>%"></div></div>
        </div>
      </div>
    </div>

    <!-- Recent reviews received -->
    <div class="db-card">
      <div class="db-card-header">
        <h3><i class="fas fa-comment-alt"></i> Recent Reviews</h3>
      </div>
      <?php if (empty($receivedReviews)): ?>
        <p class="db-empty">No reviews yet on your places.</p>
      <?php else: ?>
      <ul class="db-review-list">
        <?php foreach ($receivedReviews as $r): ?>
        <li class="db-review-item">
          <div class="db-review-top">
            <span class="db-review-author"><?= htmlspecialchars($r['full_name']) ?></span>
            <span class="db-review-stars"><?= str_repeat('★', $r['rating']) ?><?= str_repeat('☆', 5 - $r['rating']) ?></span>
          </div>
          <p class="db-review-place"><i class="fas fa-map-pin"></i> <?= htmlspecialchars($r['place_title']) ?></p>
          <p class="db-review-text"><?= htmlspecialchars(mb_strimwidth($r['comment'], 0, 80, '…')) ?></p>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>

  </div><!-- /.db-card-col -->
</div><!-- /.db-main-row -->

<?php include_once '../includes/dashboard-footer.php'; ?>
