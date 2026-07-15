<?php
include_once '../includes/config.php';
include_once '../includes/session.php';
if (!isAdmin()) redirect('login.php');

// Stats
$totalUsers      = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalPlaces     = $pdo->query("SELECT COUNT(*) FROM places")->fetchColumn();
$approvedPlaces  = $pdo->query("SELECT COUNT(*) FROM places WHERE status='approved'")->fetchColumn();
$pendingPlaces   = $pdo->query("SELECT COUNT(*) FROM places WHERE status='pending'")->fetchColumn();
$rejectedPlaces  = $pdo->query("SELECT COUNT(*) FROM places WHERE status='rejected'")->fetchColumn();
$totalReviews    = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalFavorites  = $pdo->query("SELECT COUNT(*) FROM favorites")->fetchColumn();

// Recent pending submissions
$recentPending = $pdo->query(
    "SELECT p.id, p.title, p.district, p.province, p.created_at, u.full_name, c.category_name
     FROM places p
     JOIN users u ON p.user_id = u.id
     JOIN categories c ON p.category_id = c.id
     WHERE p.status = 'pending'
     ORDER BY p.created_at DESC LIMIT 6"
)->fetchAll();

// Recent users
$recentUsers = $pdo->query(
    "SELECT id, full_name, email, role, created_at,
            (SELECT COUNT(*) FROM places WHERE user_id = users.id) AS place_count
     FROM users ORDER BY created_at DESC LIMIT 5"
)->fetchAll();

// Approval rate
$approvalRate = $totalPlaces > 0 ? round(($approvedPlaces / $totalPlaces) * 100) : 0;
$pendingRate  = $totalPlaces > 0 ? round(($pendingPlaces  / $totalPlaces) * 100) : 0;

$pageTitle  = 'Admin Dashboard';
$activePage = 'dashboard';
$dashType   = 'admin';
include_once '../includes/dashboard-header.php';
?>

<!-- ── Mini stats row ── -->
<div class="db-stats-row">
  <div class="db-stat-mini">
    <div class="db-stat-mini-icon icon-blue"><i class="fas fa-users"></i></div>
    <div>
      <p class="db-stat-mini-val"><?= number_format($totalUsers) ?></p>
      <p class="db-stat-mini-lbl">Total Users</p>
    </div>
  </div>
  <div class="db-stat-mini">
    <div class="db-stat-mini-icon icon-teal"><i class="fas fa-map-marker-alt"></i></div>
    <div>
      <p class="db-stat-mini-val"><?= number_format($totalPlaces) ?></p>
      <p class="db-stat-mini-lbl">Total Places</p>
    </div>
  </div>
  <div class="db-stat-mini">
    <div class="db-stat-mini-icon icon-orange"><i class="fas fa-clock"></i></div>
    <div>
      <p class="db-stat-mini-val"><?= number_format($pendingPlaces) ?></p>
      <p class="db-stat-mini-lbl">Pending Review</p>
    </div>
  </div>
  <div class="db-stat-mini">
    <div class="db-stat-mini-icon icon-purple"><i class="fas fa-star"></i></div>
    <div>
      <p class="db-stat-mini-val"><?= number_format($totalReviews) ?></p>
      <p class="db-stat-mini-lbl">Reviews</p>
    </div>
  </div>
  <div class="db-stat-mini">
    <div class="db-stat-mini-icon icon-red"><i class="fas fa-heart"></i></div>
    <div>
      <p class="db-stat-mini-val"><?= number_format($totalFavorites) ?></p>
      <p class="db-stat-mini-lbl">Favorites</p>
    </div>
  </div>
  <div class="db-stat-mini">
    <div class="db-stat-mini-icon icon-green"><i class="fas fa-tags"></i></div>
    <div>
      <p class="db-stat-mini-val"><?= number_format($totalCategories) ?></p>
      <p class="db-stat-mini-lbl">Categories</p>
    </div>
  </div>
</div>

<!-- ── Highlight cards + Quick actions ── -->
<div class="db-highlight-row">
  <div class="db-highlight-card card-green">
    <div class="db-highlight-icon"><i class="fas fa-check-circle"></i></div>
    <div>
      <p class="db-highlight-val"><?= number_format($approvedPlaces) ?></p>
      <p class="db-highlight-lbl">Approved Places</p>
    </div>
  </div>
  <div class="db-highlight-card card-blue">
    <div class="db-highlight-icon"><i class="fas fa-trophy"></i></div>
    <div>
      <p class="db-highlight-val"><?= number_format($totalReviews) ?></p>
      <p class="db-highlight-lbl">Total Reviews</p>
    </div>
  </div>
  <div class="db-quick-actions">
    <p class="db-section-title">Quick Actions</p>
    <div class="db-action-btns">
      <a href="places.php" class="db-action-btn btn-action-primary"><i class="fas fa-map-marker-alt"></i> Manage Places</a>
      <a href="users.php"  class="db-action-btn btn-action-secondary"><i class="fas fa-users"></i> Manage Users</a>
      <a href="categories.php" class="db-action-btn btn-action-secondary"><i class="fas fa-tags"></i> Categories</a>
    </div>
  </div>
</div>

<!-- ── Main content row ── -->
<div class="db-main-row">

  <!-- Pending submissions table -->
  <div class="db-card db-card-wide">
    <div class="db-card-header">
      <h3><i class="fas fa-clock"></i> Pending Submissions</h3>
      <a href="places.php" class="db-card-link">View all <i class="fas fa-arrow-right"></i></a>
    </div>
    <?php if (empty($recentPending)): ?>
      <p class="db-empty">No pending submissions — you're all caught up!</p>
    <?php else: ?>
    <table class="db-table">
      <thead>
        <tr>
          <th>Place</th>
          <th>Category</th>
          <th>Location</th>
          <th>Submitted by</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentPending as $p): ?>
        <tr>
          <td><strong><?= htmlspecialchars($p['title']) ?></strong></td>
          <td><span class="db-tag"><?= htmlspecialchars($p['category_name']) ?></span></td>
          <td><?= htmlspecialchars($p['district']) ?>, <?= htmlspecialchars($p['province']) ?></td>
          <td><?= htmlspecialchars($p['full_name']) ?></td>
          <td><?= date('M d, Y', strtotime($p['created_at'])) ?></td>
          <td><a href="places.php" class="db-btn-xs btn-xs-primary">Review</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <!-- Side column: stats + recent users -->
  <div class="db-card-col">

    <!-- Approval rate -->
    <div class="db-card">
      <div class="db-card-header"><h3><i class="fas fa-chart-pie"></i> Place Status</h3></div>
      <div class="db-progress-list">
        <div class="db-progress-item">
          <div class="db-progress-label">
            <span>Approved</span><span class="db-progress-val text-green"><?= $approvedPlaces ?></span>
          </div>
          <div class="db-progress-bar"><div class="db-progress-fill fill-green" style="width:<?= $approvalRate ?>%"></div></div>
        </div>
        <div class="db-progress-item">
          <div class="db-progress-label">
            <span>Pending</span><span class="db-progress-val text-orange"><?= $pendingPlaces ?></span>
          </div>
          <div class="db-progress-bar"><div class="db-progress-fill fill-orange" style="width:<?= $pendingRate ?>%"></div></div>
        </div>
        <div class="db-progress-item">
          <div class="db-progress-label">
            <span>Rejected</span><span class="db-progress-val text-red"><?= $rejectedPlaces ?></span>
          </div>
          <div class="db-progress-bar"><div class="db-progress-fill fill-red" style="width:<?= $totalPlaces > 0 ? round(($rejectedPlaces/$totalPlaces)*100) : 0 ?>%"></div></div>
        </div>
      </div>
    </div>

    <!-- Recent users -->
    <div class="db-card">
      <div class="db-card-header">
        <h3><i class="fas fa-user-plus"></i> Recent Users</h3>
        <a href="users.php" class="db-card-link">All <i class="fas fa-arrow-right"></i></a>
      </div>
      <ul class="db-user-list">
        <?php foreach ($recentUsers as $u): ?>
        <li class="db-user-row">
          <img src="<?= BASE_URL ?>assets/images/default-avatar.svg" alt="avatar" class="db-user-thumb">
          <div class="db-user-row-info">
            <p class="db-user-row-name"><?= htmlspecialchars($u['full_name']) ?></p>
            <p class="db-user-row-meta"><?= htmlspecialchars($u['email']) ?></p>
          </div>
          <span class="db-role-badge role-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>

  </div><!-- /.db-card-col -->
</div><!-- /.db-main-row -->

<?php include_once '../includes/dashboard-footer.php'; ?>
