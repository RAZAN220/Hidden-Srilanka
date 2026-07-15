<?php
include_once '../includes/config.php';
include_once '../includes/session.php';
if (!isAdmin()) redirect('login.php');

$message = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $user_id = (int)($_POST['user_id'] ?? 0);

    if ($action === 'update_role' && in_array($_POST['role'] ?? '', ['tourist', 'contributor', 'admin'])) {
        $pdo->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$_POST['role'], $user_id]);
        $message = 'Role updated.';
    }
    if ($action === 'delete' && $user_id !== (int)$_SESSION['user_id']) {
        $placeIds = $pdo->prepare('SELECT id FROM places WHERE user_id = ?');
        $placeIds->execute([$user_id]);
        foreach ($placeIds->fetchAll() as $pl) {
            $pdo->prepare('DELETE FROM place_images WHERE place_id = ?')->execute([$pl['id']]);
            $pdo->prepare('DELETE FROM favorites   WHERE place_id = ?')->execute([$pl['id']]);
            $pdo->prepare('DELETE FROM reviews     WHERE place_id = ?')->execute([$pl['id']]);
        }
        $pdo->prepare('DELETE FROM places    WHERE user_id = ?')->execute([$user_id]);
        $pdo->prepare('DELETE FROM favorites WHERE user_id = ?')->execute([$user_id]);
        $pdo->prepare('DELETE FROM reviews   WHERE user_id = ?')->execute([$user_id]);
        $pdo->prepare('DELETE FROM users     WHERE id = ?')->execute([$user_id]);
        $message = 'User deleted.';
        $msgType = 'danger';
    }
}

$search     = trim($_GET['search'] ?? '');
$roleFilter = $_GET['role'] ?? '';

$sql    = 'SELECT u.*,
                  (SELECT COUNT(*) FROM places  WHERE user_id = u.id) AS total_places,
                  (SELECT COUNT(*) FROM reviews WHERE user_id = u.id) AS total_reviews
           FROM users u WHERE 1=1';
$params = [];
if ($search !== '')     { $sql .= ' AND (u.full_name ILIKE ? OR u.email ILIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($roleFilter !== '') { $sql .= ' AND u.role = ?'; $params[] = $roleFilter; }
$sql .= ' ORDER BY u.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$pageTitle  = 'Manage Users';
$activePage = 'users';
$dashType   = 'admin';
include_once '../includes/dashboard-header.php';
?>

<div class="adm-toolbar">
  <form method="GET" class="adm-search-form">
    <div class="adm-search-wrap">
      <i class="fas fa-search"></i>
      <input type="text" name="search" placeholder="Search by name or email…" value="<?= htmlspecialchars($search) ?>">
      <input type="hidden" name="role" value="<?= htmlspecialchars($roleFilter) ?>">
    </div>
    <div class="adm-filter-tabs">
      <a href="users.php" class="adm-tab <?= $roleFilter === '' ? 'active' : '' ?>">All</a>
      <a href="users.php?role=tourist"     class="adm-tab <?= $roleFilter === 'tourist'     ? 'active' : '' ?>">Tourists</a>
      <a href="users.php?role=contributor" class="adm-tab <?= $roleFilter === 'contributor' ? 'active' : '' ?>">Contributors</a>
      <a href="users.php?role=admin"       class="adm-tab <?= $roleFilter === 'admin'       ? 'active' : '' ?>">Admins</a>
    </div>
  </form>
</div>

<?php if ($message): ?>
  <div class="adm-alert adm-alert-<?= $msgType ?>"><i class="fas fa-<?= $msgType === 'success' ? 'check-circle' : 'trash' ?>"></i> <?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="db-card">
  <div class="db-card-header">
    <h3><i class="fas fa-users"></i> Users (<?= count($users) ?>)</h3>
  </div>
  <div class="adm-table-wrap">
  <table class="db-table">
    <thead>
      <tr>
        <th>User</th>
        <th>Email</th>
        <th>Role</th>
        <th>Places</th>
        <th>Reviews</th>
        <th>Joined</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
      <tr>
        <td>
          <div class="adm-user-cell">
            <img src="<?= $u['profile_image'] ? BASE_URL.'uploads/'.htmlspecialchars($u['profile_image']) : BASE_URL.'assets/images/default-avatar.svg' ?>" class="adm-user-thumb" alt="">
            <span class="adm-user-name"><?= htmlspecialchars($u['full_name']) ?></span>
            <?php if ($u['id'] === (int)$_SESSION['user_id']): ?><span class="adm-you-badge">You</span><?php endif; ?>
          </div>
        </td>
        <td class="adm-muted"><?= htmlspecialchars($u['email']) ?></td>
        <td>
          <form method="POST" class="adm-role-form">
            <input type="hidden" name="action"  value="update_role">
            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
            <select name="role" class="adm-role-select role-<?= $u['role'] ?>" onchange="this.form.submit()">
              <option value="tourist"     <?= $u['role']==='tourist'     ? 'selected':'' ?>>Tourist</option>
              <option value="contributor" <?= $u['role']==='contributor' ? 'selected':'' ?>>Contributor</option>
              <option value="admin"       <?= $u['role']==='admin'       ? 'selected':'' ?>>Admin</option>
            </select>
          </form>
        </td>
        <td><?= $u['total_places'] ?></td>
        <td><?= $u['total_reviews'] ?></td>
        <td class="adm-muted"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
        <td>
          <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
          <form method="POST" onsubmit="return confirm('Delete this user and all their data?')">
            <input type="hidden" name="action"  value="delete">
            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
            <button class="adm-btn adm-btn-delete" title="Delete user"><i class="fas fa-trash"></i></button>
          </form>
          <?php else: ?>
            <span class="adm-muted" style="font-size:.78rem">—</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<?php include_once '../includes/dashboard-footer.php'; ?>
