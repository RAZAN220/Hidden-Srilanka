<?php
include_once '../includes/config.php';
include_once '../includes/session.php';
if (!isLoggedIn()) redirect('login.php');

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if (!$user) redirect('login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    if ($full_name === '') {
        $error = 'Full name cannot be empty.';
    } else {
        $profile_image = $user['profile_image'];
        if (!empty($_FILES['profile_image']['tmp_name'])) {
            $image = $_FILES['profile_image'];
            $allowed = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($image['type'], $allowed) && $image['size'] > 0) {
                $filename = time() . '_' . basename($image['name']);
                move_uploaded_file($image['tmp_name'], '../uploads/' . $filename);
                $profile_image = $filename;
            }
        }

        $stmt = $pdo->prepare('UPDATE users SET full_name = ?, profile_image = ? WHERE id = ?');
        $stmt->execute([$full_name, $profile_image, $user_id]);
        $_SESSION['full_name'] = $full_name;
        $_SESSION['profile_image'] = $profile_image;
        $success = 'Profile updated successfully.';
        $user['full_name'] = $full_name;
        $user['profile_image'] = $profile_image;
    }
}

include_once '../includes/header.php';
?>
<div class="container section profile-page">
    <h1>My Profile</h1>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="profile-form">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
        </div>
        <div class="form-group">
            <label>Profile Photo</label>
            <input type="file" name="profile_image" accept="image/*">
        </div>
        <?php if ($user['profile_image']): ?>
            <div class="form-group">
                <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile" width="120" style="border-radius: 50%;">
            </div>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
</div>
<?php include_once '../includes/footer.php'; ?>