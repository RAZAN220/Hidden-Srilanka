<?php
include_once '../includes/config.php';
include_once '../includes/session.php';
if (!isAdmin()) redirect('login.php');

$message = '';
$edit_category = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $name = trim($_POST['category_name']);
        $icon = trim($_POST['category_icon']);
        if ($name !== '') {
            $stmt = $pdo->prepare('INSERT INTO categories (category_name, category_icon) VALUES (?, ?)');
            $stmt->execute([$name, $icon]);
            $message = 'Category added.';
        }
    } elseif ($action === 'update') {
        $id = (int)$_POST['category_id'];
        $name = trim($_POST['category_name']);
        $icon = trim($_POST['category_icon']);
        if ($name !== '') {
            $stmt = $pdo->prepare('UPDATE categories SET category_name = ?, category_icon = ? WHERE id = ?');
            $stmt->execute([$name, $icon, $id]);
            $message = 'Category updated.';
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['category_id'];
        $exists = $pdo->prepare('SELECT COUNT(*) FROM places WHERE category_id = ?');
        $exists->execute([$id]);
        if ($exists->fetchColumn() == 0) {
            $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
            $message = 'Category deleted.';
        } else {
            $message = 'Cannot delete a category with associated places.';
        }
    }
}

if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$edit_id]);
    $edit_category = $stmt->fetch();
}

$categories = $pdo->query('SELECT * FROM categories ORDER BY category_name')->fetchAll();
include_once '../includes/header.php';
?>
<div class="container section admin-categories-page">
    <h1>Manage Categories</h1>
    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <div class="category-form-wrapper">
        <h2><?= $edit_category ? 'Edit Category' : 'Add Category' ?></h2>
        <form method="POST" class="category-form">
            <input type="hidden" name="action" value="<?= $edit_category ? 'update' : 'add' ?>">
            <?php if ($edit_category): ?>
                <input type="hidden" name="category_id" value="<?= $edit_category['id'] ?>">
            <?php endif; ?>
            <div class="form-group">
                <label>Category Name</label>
                <input type="text" name="category_name" value="<?= htmlspecialchars($edit_category['category_name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Font Awesome Icon (class)</label>
                <input type="text" name="category_icon" value="<?= htmlspecialchars($edit_category['category_icon'] ?? '') ?>" placeholder="e.g. fas fa-water">
            </div>
            <button type="submit" class="btn btn-primary"><?= $edit_category ? 'Update' : 'Add' ?> Category</button>
            <?php if ($edit_category): ?>
                <a href="categories.php" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </form>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Icon</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><?= htmlspecialchars($cat['category_name']) ?></td>
                    <td><i class="<?= htmlspecialchars($cat['category_icon']) ?>"></i> <?= htmlspecialchars($cat['category_icon']) ?></td>
                    <td>
                        <a href="categories.php?edit=<?= $cat['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                        <form method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this category?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include_once '../includes/footer.php'; ?>