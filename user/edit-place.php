<?php
include_once '../includes/config.php';
include_once '../includes/session.php';
if (!isLoggedIn()) redirect('login.php');

$place_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT * FROM places WHERE id = ? AND user_id = ?');
$stmt->execute([$place_id, $user_id]);
$place = $stmt->fetch();
if (!$place) redirect('my-places.php');

$error = '';
$success = '';
$catStmt = $pdo->query('SELECT * FROM categories ORDER BY category_name');
$categories = $catStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $category_id = (int)$_POST['category_id'];
    $description = trim($_POST['description']);
    $province = trim($_POST['province']);
    $district = trim($_POST['district']);
    $address = trim($_POST['address']);
    $latitude = trim($_POST['latitude']);
    $longitude = trim($_POST['longitude']);
    $entry_fee = trim($_POST['entry_fee']);
    $opening_hours = trim($_POST['opening_hours']);
    $contact_number = trim($_POST['contact_number']);

    if ($title === '' || $description === '' || $district === '') {
        $error = 'Title, description, and district are required.';
    } else {
        $stmt = $pdo->prepare('UPDATE places SET category_id = ?, title = ?, description = ?, province = ?, district = ?, address = ?, latitude = ?, longitude = ?, entry_fee = ?, opening_hours = ?, contact_number = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$category_id, $title, $description, $province, $district, $address, $latitude, $longitude, $entry_fee, $opening_hours, $contact_number, $place_id, $user_id]);

        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($tmp_name) {
                    $filename = time() . '_' . basename($_FILES['images']['name'][$key]);
                    move_uploaded_file($tmp_name, '../uploads/' . $filename);
                    $imgStmt = $pdo->prepare('INSERT INTO place_images (place_id, image) VALUES (?, ?)');
                    $imgStmt->execute([$place_id, $filename]);
                }
            }
        }

        $success = 'Place updated successfully.';
        $place = array_merge($place, [
            'category_id' => $category_id,
            'title' => $title,
            'description' => $description,
            'province' => $province,
            'district' => $district,
            'address' => $address,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'entry_fee' => $entry_fee,
            'opening_hours' => $opening_hours,
            'contact_number' => $contact_number,
        ]);
    }
}

include_once '../includes/header.php';
?>
<div class="container section edit-place-page">
    <h1>Edit Submitted Place</h1>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="place-form">
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($place['title']) ?>" required>
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="category_id" required>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $place['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['category_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="5" required><?= htmlspecialchars($place['description']) ?></textarea>
        </div>
        <div class="form-group">
            <label>Province</label>
            <input type="text" name="province" value="<?= htmlspecialchars($place['province']) ?>">
        </div>
        <div class="form-group">
            <label>District</label>
            <input type="text" name="district" value="<?= htmlspecialchars($place['district']) ?>" required>
        </div>
        <div class="form-group">
            <label>Address</label>
            <input type="text" name="address" value="<?= htmlspecialchars($place['address']) ?>">
        </div>
        <div class="form-group row">
            <div class="col"><label>Latitude</label><input type="text" name="latitude" value="<?= htmlspecialchars($place['latitude']) ?>" step="any"></div>
            <div class="col"><label>Longitude</label><input type="text" name="longitude" value="<?= htmlspecialchars($place['longitude']) ?>" step="any"></div>
        </div>
        <div class="form-group">
            <label>Entry Fee (LKR)</label>
            <input type="number" name="entry_fee" step="0.01" value="<?= htmlspecialchars($place['entry_fee']) ?>">
        </div>
        <div class="form-group">
            <label>Opening Hours</label>
            <input type="text" name="opening_hours" value="<?= htmlspecialchars($place['opening_hours']) ?>" placeholder="e.g., 9:00 AM - 6:00 PM">
        </div>
        <div class="form-group">
            <label>Contact Number</label>
            <input type="text" name="contact_number" value="<?= htmlspecialchars($place['contact_number']) ?>">
        </div>
        <div class="form-group">
            <label>Upload Additional Images</label>
            <input type="file" name="images[]" multiple accept="image/*">
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>
<?php include_once '../includes/footer.php'; ?>