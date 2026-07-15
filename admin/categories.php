<?php
include_once '../includes/config.php';
include_once '../includes/session.php';
if (!isAdmin()) redirect('login.php');

$message    = '';
$msgType    = 'success';
$edit_cat   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['category_name'] ?? '');
        $icon = trim($_POST['category_icon'] ?? '');
        if ($name !== '') {
            $pdo->prepare('INSERT INTO categories (category_name, category_icon) VALUES (?, ?)')->execute([$name, $icon]);
            $message = 'Category added.';
        }
    } elseif ($action === 'update') {
        $id   = (int)$_POST['category_id'];
        $name = trim($_POST['category_name'] ?? '');
        $icon = trim($_POST['category_icon'] ?? '');
        if ($name !== '') {
            $pdo->prepare('UPDATE categories SET category_name = ?, category_icon = ? WHERE id = ?')->execute([$name, $icon, $id]);
            $message = 'Category updated.';
        }
    } elseif ($action === 'delete') {
        $id  = (int)$_POST['category_id'];
        $cnt = $pdo->prepare('SELECT COUNT(*) FROM places WHERE category_id = ?');
        $cnt->execute([$id]);
        if ($cnt->fetchColumn() > 0) {
            $message = 'Cannot delete — this category has places assigned to it.';
            $msgType = 'danger';
        } else {
            $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
            $message = 'Category deleted.';
            $msgType = 'danger';
        }
    }
}

if (isset($_GET['edit'])) {
    $s = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $s->execute([(int)$_GET['edit']]);
    $edit_cat = $s->fetch();
}

$categories = $pdo->query(
    'SELECT c.*, (SELECT COUNT(*) FROM places WHERE category_id = c.id) AS place_count
     FROM categories c ORDER BY category_name'
)->fetchAll();

$pageTitle  = 'Manage Categories';
$activePage = 'categories';
$dashType   = 'admin';
include_once '../includes/dashboard-header.php';
?>

<div class="adm-cat-layout">

  <!-- Form card -->
  <div class="db-card adm-cat-form-card">
    <div class="db-card-header">
      <h3><i class="fas fa-<?= $edit_cat ? 'pen' : 'plus-circle' ?>"></i> <?= $edit_cat ? 'Edit Category' : 'Add Category' ?></h3>
    </div>
    <form method="POST" class="adm-cat-form">
      <input type="hidden" name="action" value="<?= $edit_cat ? 'update' : 'add' ?>">
      <?php if ($edit_cat): ?>
        <input type="hidden" name="category_id" value="<?= $edit_cat['id'] ?>">
      <?php endif; ?>

      <div class="adm-cat-field">
        <label>Category Name</label>
        <input type="text" name="category_name"
               value="<?= htmlspecialchars($edit_cat['category_name'] ?? '') ?>"
               placeholder="e.g. Waterfalls" required>
      </div>
      <div class="adm-cat-field">
        <label>Font Awesome Icon Class</label>
        <div class="adm-icon-input">
          <input type="text" name="category_icon" id="iconInput"
                 value="<?= htmlspecialchars($edit_cat['category_icon'] ?? '') ?>"
                 placeholder="e.g. fas fa-water"
                 oninput="document.getElementById('iconPreview').className=this.value">
          <span class="adm-icon-preview"><i id="iconPreview" class="<?= htmlspecialchars($edit_cat['category_icon'] ?? 'fas fa-tag') ?>"></i></span>
        </div>
        <small class="adm-hint">Browse icons at <a href="https://fontawesome.com/icons" target="_blank">fontawesome.com/icons</a></small>
      </div>

      <div class="adm-cat-form-btns">
        <button type="submit" class="db-action-btn btn-action-primary">
          <i class="fas fa-<?= $edit_cat ? 'save' : 'plus' ?>"></i>
          <?= $edit_cat ? 'Save Changes' : 'Add Category' ?>
        </button>
        <?php if ($edit_cat): ?>
          <a href="categories.php" class="db-action-btn btn-action-secondary"><i class="fas fa-times"></i> Cancel</a>
        <?php endif; ?>
      </div>
    </form>

    <?php if ($message): ?>
      <div class="adm-alert adm-alert-<?= $msgType ?>" style="margin:16px 20px 0">
        <i class="fas fa-<?= $msgType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Table card -->
  <div class="db-card adm-cat-table-card">
    <div class="db-card-header">
      <h3><i class="fas fa-tags"></i> Categories (<?= count($categories) ?>)</h3>
    </div>
    <table class="db-table">
      <thead>
        <tr>
          <th>Icon</th>
          <th>Name</th>
          <th>Places</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categories as $cat): ?>
        <tr>
          <td><div class="adm-cat-icon-cell"><i class="<?= htmlspecialchars($cat['category_icon'] ?: 'fas fa-tag') ?>"></i></div></td>
          <td><strong><?= htmlspecialchars($cat['category_name']) ?></strong></td>
          <td><span class="db-tag"><?= $cat['place_count'] ?> places</span></td>
          <td>
            <div class="adm-actions">
              <a href="categories.php?edit=<?= $cat['id'] ?>" class="adm-btn adm-btn-edit" title="Edit"><i class="fas fa-pen"></i></a>
              <form method="POST" onsubmit="return confirm('Delete this category?')">
                <input type="hidden" name="action"      value="delete">
                <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                <button class="adm-btn adm-btn-delete" title="Delete" <?= $cat['place_count'] > 0 ? 'disabled' : '' ?>>
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<?php include_once '../includes/dashboard-footer.php'; ?>
