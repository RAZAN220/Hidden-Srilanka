<?php
include_once '../includes/config.php';
include_once '../includes/session.php';
if (!isAdmin()) redirect('login.php');

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['place_id'])) {
    $action = $_POST['action'];
    $place_id = (int)$_POST['place_id'];

    if ($action === 'approve') {
        $stmt = $pdo->prepare('UPDATE places SET status = "approved" WHERE id = ?');
        $stmt->execute([$place_id]);
        $message = 'Place approved successfully.';
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare('UPDATE places SET status = "rejected" WHERE id = ?');
        $stmt->execute([$place_id]);
        $message = 'Place rejected successfully.';
    } elseif ($action === 'delete') {
        $imgStmt = $pdo->prepare('SELECT image FROM place_images WHERE place_id = ?');
        $imgStmt->execute([$place_id]);
        foreach ($imgStmt->fetchAll() as $img) {
            $path = '../uploads/' . $img['image'];
            if (file_exists($path)) {
                @unlink($path);
            }
        }
        $pdo->prepare('DELETE FROM place_images WHERE place_id = ?')->execute([$place_id]);
        $pdo->prepare('DELETE FROM favorites WHERE place_id = ?')->execute([$place_id]);
        $pdo->prepare('DELETE FROM reviews WHERE place_id = ?')->execute([$place_id]);
        $pdo->prepare('DELETE FROM places WHERE id = ?')->execute([$place_id]);
        $message = 'Place deleted successfully.';
    }
}

$places = $pdo->query('SELECT p.*, c.category_name, u.full_name AS contributor, (SELECT image FROM place_images WHERE place_id = p.id LIMIT 1) AS image FROM places p JOIN categories c ON p.category_id = c.id JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC')->fetchAll();
include_once '../includes/header.php';
?>
<div class="container section admin-places-page">
    <h1>Manage Places</h1>
    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <table class="table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Title</th>
                <th>Category</th>
                <th>Contributor</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($places as $place): ?>
                <tr>
                    <td><img src="<?= BASE_URL ?><?= $place['image'] ? 'uploads/' . htmlspecialchars($place['image']) : 'assets/images/placeholder.svg' ?>" width="60" alt=""></td>
                    <td><?= htmlspecialchars($place['title']) ?></td>
                    <td><?= htmlspecialchars($place['category_name']) ?></td>
                    <td><?= htmlspecialchars($place['contributor']) ?></td>
                    <td><span class="badge <?= htmlspecialchars($place['status']) ?>"><?= htmlspecialchars($place['status']) ?></span></td>
                    <td>
                        <?php if ($place['status'] === 'pending'): ?>
                            <form method="POST" style="display:inline-block; margin-right: 6px;">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="place_id" value="<?= $place['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-primary">Approve</button>
                            </form>
                            <form method="POST" style="display:inline-block; margin-right: 6px;">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="place_id" value="<?= $place['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-secondary">Reject</button>
                            </form>
                        <?php endif; ?>
                        <form method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this place?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="place_id" value="<?= $place['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include_once '../includes/footer.php'; ?>
