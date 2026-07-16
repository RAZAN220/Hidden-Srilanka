<?php
include_once '../includes/config.php';
include_once '../includes/session.php';
if (!isLoggedIn()) redirect('login.php');

// Fetch categories for dropdown
$catStmt = $pdo->query("SELECT * FROM categories ORDER BY category_name");
$categories = $catStmt->fetchAll();

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title          = trim($_POST['title'] ?? '');
    $category_id    = (int)($_POST['category_id'] ?? 0);
    $description    = trim($_POST['description'] ?? '');
    $province       = trim($_POST['province'] ?? '');
    $district       = trim($_POST['district'] ?? '');
    $address        = trim($_POST['address'] ?? '');
    $latitude       = $_POST['latitude'] ?? '';
    $longitude      = $_POST['longitude'] ?? '';
    $entry_fee      = $_POST['entry_fee'] ?? 0;
    $opening_hours  = trim($_POST['opening_hours'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');

    if (!$title)       $errors[] = 'Title is required.';
    if (!$category_id) $errors[] = 'Please select a category.';
    if (!$description) $errors[] = 'Description is required.';
    if (!$district)    $errors[] = 'District is required.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO places (user_id, category_id, title, description, province, district, address, latitude, longitude, entry_fee, opening_hours, contact_number, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?, 'pending')");
        $stmt->execute([$_SESSION['user_id'], $category_id, $title, $description, $province, $district, $address, $latitude ?: null, $longitude ?: null, $entry_fee ?: 0, $opening_hours, $contact_number]);
        $place_id = $pdo->lastInsertId('places_id_seq');

        // Handle image uploads
        if (!empty($_FILES['images']['name'][0])) {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $ext      = strtolower(pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION));
                    $allowed  = ['jpg','jpeg','png','webp','gif'];
                    if (in_array($ext, $allowed)) {
                        $filename = time() . '_' . uniqid() . '.' . $ext;
                        move_uploaded_file($tmp_name, $uploadDir . $filename);
                        $imgStmt = $pdo->prepare("INSERT INTO place_images (place_id, image) VALUES (?, ?)");
                        $imgStmt->execute([$place_id, $filename]);
                    }
                }
            }
        }
        redirect('my-places.php');
    }
}

$pageTitle  = 'Add New Place';
$activePage = 'add-place';
$dashType   = 'user';
include_once '../includes/dashboard-header.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
.ap-card{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.07);padding:32px;margin-bottom:24px;}
.ap-section-title{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin:0 0 18px;}
.ap-grid{display:grid;gap:18px;}
.ap-grid-2{grid-template-columns:1fr 1fr;}
.ap-grid-3{grid-template-columns:1fr 1fr 1fr;}
.ap-form-group{display:flex;flex-direction:column;gap:6px;}
.ap-form-group label{font-size:.85rem;font-weight:600;color:#374151;}
.ap-form-group input,
.ap-form-group select,
.ap-form-group textarea{width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.9rem;color:#1e293b;background:#fff;transition:border .2s,box-shadow .2s;box-sizing:border-box;}
.ap-form-group input:focus,
.ap-form-group select:focus,
.ap-form-group textarea:focus{outline:none;border-color:#3a7c52;box-shadow:0 0 0 3px rgba(58,124,82,.12);}
.ap-form-group textarea{resize:vertical;min-height:110px;}
#pickMap{height:280px;border-radius:10px;border:1.5px solid #e2e8f0;margin-top:6px;cursor:crosshair;}
.ap-coord-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:10px;}
.ap-coord-hint{font-size:.78rem;color:#64748b;margin-top:4px;grid-column:1/-1;}
.ap-file-drop{border:2px dashed #cbd5e1;border-radius:10px;padding:28px;text-align:center;cursor:pointer;transition:border .2s,background .2s;position:relative;}
.ap-file-drop:hover,.ap-file-drop.dragover{border-color:#3a7c52;background:#f0fdf4;}
.ap-file-drop i{font-size:2rem;color:#94a3b8;display:block;margin-bottom:8px;}
.ap-file-drop p{color:#64748b;font-size:.88rem;margin:0;}
.ap-file-drop input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;}
.ap-preview-row{display:flex;flex-wrap:wrap;gap:10px;margin-top:12px;}
.ap-preview-thumb{width:80px;height:80px;object-fit:cover;border-radius:8px;border:2px solid #e2e8f0;}
.ap-alert-error{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:.88rem;}
.ap-alert-error ul{margin:6px 0 0 18px;padding:0;}
.ap-btn-row{display:flex;gap:12px;align-items:center;margin-top:8px;}
.ap-submit{background:linear-gradient(135deg,#3a7c52,#2d6b45);color:#fff;border:none;padding:12px 32px;border-radius:8px;font-size:.95rem;font-weight:600;cursor:pointer;transition:opacity .2s,transform .2s;}
.ap-submit:hover{opacity:.9;transform:translateY(-1px);}
.ap-cancel{color:#64748b;font-size:.88rem;text-decoration:none;}
.ap-cancel:hover{color:#1e293b;}
@media(max-width:640px){.ap-grid-2,.ap-grid-3{grid-template-columns:1fr;}.ap-coord-row{grid-template-columns:1fr 1fr;}}
</style>

<?php if (!empty($errors)): ?>
<div class="ap-alert-error">
  <strong><i class="fas fa-exclamation-circle"></i> Please fix the following:</strong>
  <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" id="addPlaceForm">

  <!-- Basic Info -->
  <div class="ap-card">
    <p class="ap-section-title"><i class="fas fa-info-circle"></i> Basic Information</p>
    <div class="ap-grid">
      <div class="ap-form-group">
        <label for="title">Place Title <span style="color:#ef4444">*</span></label>
        <input type="text" id="title" name="title" placeholder="e.g. Ravana Falls, Ella" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
      </div>
      <div class="ap-grid ap-grid-2">
        <div class="ap-form-group">
          <label for="category_id">Category <span style="color:#ef4444">*</span></label>
          <select id="category_id" name="category_id" required>
            <option value="">— Select category —</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= (($_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['category_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="ap-form-group">
          <label for="entry_fee">Entry Fee (LKR)</label>
          <input type="number" id="entry_fee" name="entry_fee" min="0" step="0.01" value="<?= htmlspecialchars($_POST['entry_fee'] ?? '0') ?>" placeholder="0 = Free">
        </div>
      </div>
      <div class="ap-form-group">
        <label for="description">Description <span style="color:#ef4444">*</span></label>
        <textarea id="description" name="description" placeholder="Describe what makes this place special..." required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
      </div>
      <div class="ap-grid ap-grid-2">
        <div class="ap-form-group">
          <label for="opening_hours">Opening Hours</label>
          <input type="text" id="opening_hours" name="opening_hours" placeholder="e.g. 8:00 AM – 6:00 PM" value="<?= htmlspecialchars($_POST['opening_hours'] ?? '') ?>">
        </div>
        <div class="ap-form-group">
          <label for="contact_number">Contact Number</label>
          <input type="text" id="contact_number" name="contact_number" placeholder="e.g. +94 77 123 4567" value="<?= htmlspecialchars($_POST['contact_number'] ?? '') ?>">
        </div>
      </div>
    </div>
  </div>

  <!-- Location -->
  <div class="ap-card">
    <p class="ap-section-title"><i class="fas fa-map-marker-alt"></i> Location</p>
    <div class="ap-grid">
      <div class="ap-grid ap-grid-3">
        <div class="ap-form-group">
          <label for="province">Province</label>
          <input type="text" id="province" name="province" placeholder="e.g. Uva" value="<?= htmlspecialchars($_POST['province'] ?? '') ?>">
        </div>
        <div class="ap-form-group">
          <label for="district">District <span style="color:#ef4444">*</span></label>
          <input type="text" id="district" name="district" placeholder="e.g. Badulla" value="<?= htmlspecialchars($_POST['district'] ?? '') ?>" required>
        </div>
        <div class="ap-form-group">
          <label for="address">Address / Landmark</label>
          <input type="text" id="address" name="address" placeholder="e.g. Ella Road, Badulla" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
        </div>
      </div>

      <!-- Map picker -->
      <div class="ap-form-group">
        <label><i class="fas fa-crosshairs"></i> Pin on Map <small style="font-weight:400;color:#64748b;">(click to set coordinates)</small></label>
        <div id="pickMap"></div>
        <div class="ap-coord-row">
          <div class="ap-form-group">
            <label for="latitude">Latitude</label>
            <input type="text" id="latitude" name="latitude" placeholder="e.g. 6.8667" value="<?= htmlspecialchars($_POST['latitude'] ?? '') ?>">
          </div>
          <div class="ap-form-group">
            <label for="longitude">Longitude</label>
            <input type="text" id="longitude" name="longitude" placeholder="e.g. 81.0467" value="<?= htmlspecialchars($_POST['longitude'] ?? '') ?>">
          </div>
          <p class="ap-coord-hint"><i class="fas fa-info-circle"></i> Click anywhere on the map to pin your location, or type coordinates manually.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Images -->
  <div class="ap-card">
    <p class="ap-section-title"><i class="fas fa-images"></i> Photos</p>
    <div class="ap-file-drop" id="fileDrop">
      <i class="fas fa-cloud-upload-alt"></i>
      <p><strong>Click to upload</strong> or drag &amp; drop photos here</p>
      <p style="margin-top:4px;font-size:.78rem;">JPG, PNG, WebP · up to 5 images</p>
      <input type="file" name="images[]" id="imageInput" multiple accept="image/*">
    </div>
    <div class="ap-preview-row" id="previewRow"></div>
  </div>

  <div class="ap-btn-row">
    <button type="submit" class="ap-submit"><i class="fas fa-paper-plane"></i> Submit Place</button>
    <a href="my-places.php" class="ap-cancel">Cancel</a>
  </div>

</form>

<script>
// ── Map picker ──
(function() {
    var initLat = parseFloat(document.getElementById('latitude').value) || 7.8731;
    var initLng = parseFloat(document.getElementById('longitude').value) || 80.7718;
    var initZoom = (document.getElementById('latitude').value) ? 13 : 7;

    var map = L.map('pickMap').setView([initLat, initLng], initZoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var marker = null;
    function setPin(lat, lng) {
        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        document.getElementById('latitude').value  = lat.toFixed(6);
        document.getElementById('longitude').value = lng.toFixed(6);
        marker.on('dragend', function(e) {
            var p = e.target.getLatLng();
            document.getElementById('latitude').value  = p.lat.toFixed(6);
            document.getElementById('longitude').value = p.lng.toFixed(6);
        });
    }

    // Place initial pin if coords exist
    if (document.getElementById('latitude').value) setPin(initLat, initLng);

    map.on('click', function(e) { setPin(e.latlng.lat, e.latlng.lng); });

    // Sync manual input → map
    ['latitude','longitude'].forEach(function(id) {
        document.getElementById(id).addEventListener('change', function() {
            var lat = parseFloat(document.getElementById('latitude').value);
            var lng = parseFloat(document.getElementById('longitude').value);
            if (!isNaN(lat) && !isNaN(lng)) { map.setView([lat, lng], 13); setPin(lat, lng); }
        });
    });
})();

// ── Image preview ──
document.getElementById('imageInput').addEventListener('change', function() {
    var row = document.getElementById('previewRow');
    row.innerHTML = '';
    Array.from(this.files).slice(0, 5).forEach(function(f) {
        var r = new FileReader();
        r.onload = function(e) {
            var img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'ap-preview-thumb';
            row.appendChild(img);
        };
        r.readAsDataURL(f);
    });
});

// ── Drag-over highlight ──
var drop = document.getElementById('fileDrop');
['dragenter','dragover'].forEach(function(ev) { drop.addEventListener(ev, function(e){e.preventDefault();drop.classList.add('dragover');}); });
['dragleave','drop'].forEach(function(ev) { drop.addEventListener(ev, function(){drop.classList.remove('dragover');}); });
</script>

<?php include_once '../includes/dashboard-footer.php'; ?>
