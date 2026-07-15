<?php
include_once '../includes/config.php';
include_once '../includes/session.php';
if (!isContributor()) redirect('login.php');
include_once '../includes/header.php';

// Fetch categories for dropdown
$catStmt = $pdo->query("SELECT * FROM categories ORDER BY category_name");
$categories = $catStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $category_id = $_POST['category_id'];
    $description = $_POST['description'];
    $province = $_POST['province'];
    $district = $_POST['district'];
    $address = $_POST['address'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $entry_fee = $_POST['entry_fee'];
    $opening_hours = $_POST['opening_hours'];
    $contact_number = $_POST['contact_number'];

    $stmt = $pdo->prepare("INSERT INTO places (user_id, category_id, title, description, province, district, address, latitude, longitude, entry_fee, opening_hours, contact_number, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?, 'pending')");
    $stmt->execute([$_SESSION['user_id'], $category_id, $title, $description, $province, $district, $address, $latitude, $longitude, $entry_fee, $opening_hours, $contact_number]);
    $place_id = $pdo->lastInsertId();

    // Handle image uploads
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = '../uploads/';
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $filename = time() . '_' . basename($_FILES['images']['name'][$key]);
            move_uploaded_file($tmp_name, $uploadDir . $filename);
            $imgStmt = $pdo->prepare("INSERT INTO place_images (place_id, image) VALUES (?, ?)");
            $imgStmt->execute([$place_id, $filename]);
        }
    }
    redirect('my-places.php');
}
?>
<div class="container add-place">
    <h1>Submit New Place</h1>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" required>
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="category_id" required>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= $cat['category_name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="5" required></textarea>
        </div>
        <div class="form-group">
            <label>Province</label>
            <input type="text" name="province">
        </div>
        <div class="form-group">
            <label>District</label>
            <input type="text" name="district" required>
        </div>
        <div class="form-group">
            <label>Address</label>
            <input type="text" name="address">
        </div>
        <div class="form-group row">
            <div class="col"><label>Latitude</label><input type="text" name="latitude" step="any"></div>
            <div class="col"><label>Longitude</label><input type="text" name="longitude" step="any"></div>
        </div>
        <div class="form-group">
            <label>Entry Fee (LKR)</label>
            <input type="number" name="entry_fee" step="0.01" value="0">
        </div>
        <div class="form-group">
            <label>Opening Hours</label>
            <input type="text" name="opening_hours" placeholder="e.g., 9:00 AM - 6:00 PM">
        </div>
        <div class="form-group">
            <label>Contact Number</label>
            <input type="text" name="contact_number">
        </div>
        <div class="form-group">
            <label>Upload Images (multiple)</label>
            <input type="file" name="images[]" multiple accept="image/*">
        </div>
        <button type="submit" class="btn btn-primary">Submit Place</button>
    </form>
</div>
<?php include_once '../includes/footer.php'; ?>