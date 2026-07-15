<?php
include_once '../includes/config.php';
include_once '../includes/session.php';
if (!isAdmin()) redirect('login.php');

$message = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['place_id'])) {
    $action   = $_POST['action'];
    $place_id = (int)$_POST['place_id'];

    if ($action === 'approve') {
        $pdo->prepare('UPDATE places SET status = \'approved\' WHERE id = ?')->execute([$place_id]);
        $message = 'Place approved successfully.';
    } elseif ($action === 'reject') {
        $pdo->prepare('UPDATE places SET status = \'rejected\' WHERE id = ?')->execute([$place_id]);
        $message = 'Place rejected.';
    } elseif ($action === 'delete') {
        $imgs = $pdo->prepare('SELECT image FROM place_images WHERE place_id = ?');
        $imgs->execute([$place_id]);
        foreach ($imgs->fetchAll() as $img) {
            $path = '../uploads/' . $img['image'];
            if (file_exists($path)) @unlink($path);
        }
        $pdo->prepare('DELETE FROM place_images WHERE place_id = ?')->execute([$place_id]);
        $pdo->prepare('DELETE FROM favorites   WHERE place_id = ?')->execute([$place_id]);
        $pdo->prepare('DELETE FROM reviews     WHERE place_id = ?')->execute([$place_id]);
        $pdo->prepare('DELETE FROM places      WHERE id = ?')->execute([$place_id]);
        $message = 'Place deleted.';
        $msgType = 'danger';
    }
}

// Filters
$statusFilter = $_GET['status'] ?? '';
$search       = trim($_GET['search'] ?? '');

$sql    = 'SELECT p.*, c.category_name, u.full_name AS contributor,
                  (SELECT image FROM place_images WHERE place_id = p.id LIMIT 1) AS image
           FROM places p
           JOIN categories c ON p.category_id = c.id
           JOIN users u ON p.user_id = u.id
           WHERE 1=1';
$params = [];
if ($statusFilter !== '') { $sql .= ' AND p.status = ?'; $params[] = $statusFilter; }
if ($search !== '')       { $sql .= ' AND (p.title ILIKE ? OR p.district ILIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
$sql .= ' ORDER BY p.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$places = $stmt->fetchAll();

$counts = $pdo->query("SELECT status, COUNT(*) AS cnt FROM places GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);

$pageTitle  = 'Manage Places';
$activePage = 'places';
$dashType   = 'admin';
include_once '../includes/dashboard-header.php';
?>

<!-- Toolbar -->
<div class="adm-toolbar">
  <form method="GET" class="adm-search-form">
    <div class="adm-search-wrap">
      <i class="fas fa-search"></i>
      <input type="text" name="search" placeholder="Search by title or district…" value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="adm-filter-tabs">
      <a href="places.php" class="adm-tab <?= $statusFilter === '' ? 'active' : '' ?>">All <span><?= array_sum($counts) ?></span></a>
      <a href="places.php?status=pending"  class="adm-tab <?= $statusFilter === 'pending'  ? 'active' : '' ?>">Pending  <span><?= $counts['pending']  ?? 0 ?></span></a>
      <a href="places.php?status=approved" class="adm-tab <?= $statusFilter === 'approved' ? 'active' : '' ?>">Approved <span><?= $counts['approved'] ?? 0 ?></span></a>
      <a href="places.php?status=rejected" class="adm-tab <?= $statusFilter === 'rejected' ? 'active' : '' ?>">Rejected <span><?= $counts['rejected'] ?? 0 ?></span></a>
    </div>
  </form>
</div>

<?php if ($message): ?>
  <div class="adm-alert adm-alert-<?= $msgType ?>"><i class="fas fa-<?= $msgType === 'success' ? 'check-circle' : 'trash' ?>"></i> <?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="db-card">
  <div class="db-card-header">
    <h3><i class="fas fa-map-marker-alt"></i> Places (<?= count($places) ?>)</h3>
  </div>
  <?php if (empty($places)): ?>
    <p class="db-empty">No places found for this filter.</p>
  <?php else: ?>
  <div class="adm-table-wrap">
  <table class="db-table adm-places-table">
    <thead>
      <tr>
        <th>Place</th>
        <th>Category</th>
        <th>Location</th>
        <th>Contributor</th>
        <th>Date</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($places as $p): ?>
      <tr>
        <td>
          <div class="adm-place-cell">
            <div class="adm-place-thumb" style="background-image:url('<?= $p['image'] ? BASE_URL.'uploads/'.htmlspecialchars($p['image']) : BASE_URL.'assets/images/placeholder.svg' ?>')"></div>
            <a href="<?= BASE_URL ?>place-details.php?id=<?= $p['id'] ?>" target="_blank" class="adm-place-title"><?= htmlspecialchars($p['title']) ?></a>
          </div>
        </td>
        <td><span class="db-tag"><?= htmlspecialchars($p['category_name']) ?></span></td>
        <td class="adm-muted"><?= htmlspecialchars($p['district']) ?>, <?= htmlspecialchars($p['province']) ?></td>
        <td class="adm-muted"><?= htmlspecialchars($p['contributor']) ?></td>
        <td class="adm-muted"><?= date('M d, Y', strtotime($p['created_at'])) ?></td>
        <td><span class="badge <?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
        <td>
          <div class="adm-actions">
            <?php if ($p['status'] === 'pending'): ?>
              <form method="POST">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="place_id" value="<?= $p['id'] ?>">
                <button class="adm-btn adm-btn-approve" title="Approve"><i class="fas fa-check"></i></button>
              </form>
              <form method="POST">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="place_id" value="<?= $p['id'] ?>">
                <button class="adm-btn adm-btn-reject" title="Reject"><i class="fas fa-times"></i></button>
              </form>
            <?php elseif ($p['status'] === 'rejected'): ?>
              <form method="POST">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="place_id" value="<?= $p['id'] ?>">
                <button class="adm-btn adm-btn-approve" title="Re-approve"><i class="fas fa-check"></i></button>
              </form>
            <?php endif; ?>
            <form method="POST" onsubmit="return confirm('Delete this place permanently?')">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="place_id" value="<?= $p['id'] ?>">
              <button class="adm-btn adm-btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php endif; ?>
</div>

<?php include_once '../includes/dashboard-footer.php'; ?>
