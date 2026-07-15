<?php
include_once '../includes/config.php';
include_once '../includes/session.php';
if (!isLoggedIn()) redirect('login.php');
include_once '../includes/header.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT p.*, c.category_name, (SELECT image FROM place_images WHERE place_id = p.id LIMIT 1) as image FROM places p JOIN categories c ON p.category_id = c.id WHERE p.user_id = ? ORDER BY p.created_at DESC");
$stmt->execute([$user_id]);
$places = $stmt->fetchAll();
?>
<div class="container my-places">
    <h1>My Submitted Places</h1>
    <?php if (empty($places)): ?>
        <p>You haven't submitted any places yet. <a href="add-place.php">Add one now</a></p>
    <?php else: ?>
        <table class="table">
            <thead><tr><th>Image</th><th>Title</th><th>Category</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($places as $p): ?>
                <tr>
                    <td><img src="<?= BASE_URL ?>uploads/<?= $p['image'] ?: 'placeholder.jpg' ?>" width="50"></td>
                    <td><a href="../place-details.php?id=<?= $p['id'] ?>"><?= htmlspecialchars($p['title']) ?></a></td>
                    <td><?= $p['category_name'] ?></td>
                    <td><span class="badge <?= $p['status'] ?>"><?= $p['status'] ?></span></td>
                    <td>
                        <a href="edit-place.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                        <a href="delete-place.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php include_once '../includes/footer.php'; ?>