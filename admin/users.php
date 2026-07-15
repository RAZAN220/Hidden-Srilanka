<?php
include_once '../includes/config.php';
include_once '../includes/session.php';
if (!isAdmin()) redirect('login.php');

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

    if ($action === 'update_role' && in_array($_POST['role'], ['tourist', 'contributor', 'admin'])) {
        $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
        $stmt->execute([$_POST['role'], $user_id]);
        $message = 'Role updated successfully.';
    }

    if ($action === 'delete' && $user_id !== $_SESSION['user_id']) {
        $pdo->prepare('DELETE FROM favorites WHERE user_id = ?')->execute([$user_id]);
        $pdo->prepare('DELETE FROM reviews WHERE user_id = ?')->execute([$user_id]);
        // Delete user places and related place data
        $placeIds = $pdo->prepare('SELECT id FROM places WHERE user_id = ?');
        $placeIds->execute([$user_id]);
        foreach ($placeIds->fetchAll() as $place) {
            $pdo->prepare('DELETE FROM place_images WHERE place_id = ?')->execute([$place['id']]);
            $pdo->prepare('DELETE FROM favorites WHERE place_id = ?')->execute([$place['id']]);
            $pdo->prepare('DELETE FROM reviews WHERE place_id = ?')->execute([$place['id']]);
        }
        $pdo->prepare('DELETE FROM places WHERE user_id = ?')->execute([$user_id]);
        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$user_id]);
        $message = 'User deleted successfully.';
    }
}

$users = $pdo->query('SELECT *, (SELECT COUNT(*) FROM places WHERE user_id = users.id) AS total_places, (SELECT COUNT(*) FROM reviews WHERE user_id = users.id) AS total_reviews FROM users ORDER BY created_at DESC')->fetchAll();
include_once '../includes/header.php';
?>
<div class="container section admin-users-page">
    <h1>Manage Users</h1>
    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Places</th>
                <th>Reviews</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td><?= $user['total_places'] ?></td>
                    <td><?= $user['total_reviews'] ?></td>
                    <td>
                        <form method="POST" style="display:inline-block; margin-right:8px;">
                            <input type="hidden" name="action" value="update_role">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <select name="role" onchange="this.form.submit()">
                                <option value="tourist" <?= $user['role'] === 'tourist' ? 'selected' : '' ?>>Tourist</option>
                                <option value="contributor" <?= $user['role'] === 'contributor' ? 'selected' : '' ?>>Contributor</option>
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </form>
                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                            <form method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this user and all related data?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include_once '../includes/footer.php'; ?>