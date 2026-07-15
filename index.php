<?php
include_once 'includes/config.php';
include_once 'includes/session.php';
include_once 'includes/header.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM places p WHERE p.category_id = c.id AND p.status='approved') AS place_count FROM categories c ORDER BY category_name")->fetchAll();
$featured = $pdo->query("SELECT p.*, c.category_name, c.category_icon, (SELECT image FROM place_images WHERE place_id = p.id LIMIT 1) AS image, (SELECT AVG(rating) FROM reviews WHERE place_id = p.id) AS avg_rating FROM places p JOIN categories c ON p.category_id = c.id WHERE p.status = 'approved' ORDER BY p.created_at DESC LIMIT 6")->fetchAll();
$gems = $pdo->query("SELECT p.*, c.category_name, c.category_icon, (SELECT image FROM place_images WHERE place_id = p.id LIMIT 1) AS image, (SELECT AVG(rating) FROM reviews WHERE place_id = p.id) AS avg_rating FROM places p JOIN categories c ON p.category_id = c.id WHERE p.status = 'approved' ORDER BY RANDOM() LIMIT 4")->fetchAll();
$totalPlaces = $pdo->query("SELECT COUNT(*) FROM places WHERE status = 'approved'")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalReviews = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
$mapPlaces = $pdo->query("SELECT p.id, p.title, p.district, p.province, p.latitude, p.longitude, c.category_name FROM places p JOIN categories c ON p.category_id = c.id WHERE p.status = 'approved' AND p.latitude IS NOT NULL AND p.longitude IS NOT NULL AND p.latitude != 0 AND p.longitude != 0")->fetchAll();
?>
<div class="hero">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1>Discover Sri Lanka's Hidden Gems</h1>
        <p>Explore authentic local experiences, secret spots, and off-the-beaten-path destinations curated by the community.</p>
        <form action="explore.php" method="GET" class="search-form">
            <div class="search-input-group">
                <input type="text" name="search" placeholder="Search by title, district, or keywords" value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Search</button>
            </div>
        </form>
        <div class="hero-stats">
            <div>
                <span class="stat-number"><?= $totalPlaces ?></span>
                <span class="stat-label">Approved Places</span>
            </div>
            <div>
                <span class="stat-number"><?= $totalUsers ?></span>
                <span class="stat-label">Community Members</span>
            </div>
            <div>
                <span class="stat-number"><?= $totalReviews ?></span>
                <span class="stat-label">Reviews</span>
            </div>
        </div>
    </div>
</div>
<div class="container section">
    <div class="section-header">
        <h2>Explore the <span class="highlight">Map</span></h2>
        <p>See all approved destinations across Sri Lanka</p>
    </div>
    <div id="homeMap"></div>
</div>
<script>
(function() {
    var places = <?= json_encode($mapPlaces) ?>;
    var map = L.map('homeMap').setView([7.8731, 80.7718], 7);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    places.forEach(function(p) {
        var lat = parseFloat(p.latitude);
        var lng = parseFloat(p.longitude);
        if (!isNaN(lat) && !isNaN(lng)) {
            L.marker([lat, lng])
                .addTo(map)
                .bindPopup(
                    '<strong><a href="place-details.php?id=' + p.id + '">' + p.title + '</a></strong>' +
                    '<br><small>' + p.category_name + ' &bull; ' + p.district + ', ' + p.province + '</small>'
                );
        }
    });

    // ── Current Location Button ──
    var LocateControl = L.Control.extend({
        options: { position: 'topleft' },
        onAdd: function() {
            var btn = L.DomUtil.create('button', 'map-locate-btn');
            btn.title = 'Show my location';
            btn.innerHTML = '<i class="fas fa-location-crosshairs"></i>';
            var locationMarker = null;
            var locationCircle  = null;
            L.DomEvent.on(btn, 'click', function(e) {
                L.DomEvent.stopPropagation(e);
                btn.classList.add('locating');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                if (!navigator.geolocation) {
                    alert('Geolocation is not supported by your browser.');
                    btn.classList.remove('locating');
                    btn.innerHTML = '<i class="fas fa-location-crosshairs"></i>';
                    return;
                }
                navigator.geolocation.getCurrentPosition(
                    function(pos) {
                        var lat = pos.coords.latitude;
                        var lng = pos.coords.longitude;
                        var acc = pos.coords.accuracy;
                        if (locationMarker) { map.removeLayer(locationMarker); map.removeLayer(locationCircle); }
                        locationCircle = L.circle([lat, lng], { radius: acc, color: '#1e6fad', fillColor: '#3b82c4', fillOpacity: 0.15, weight: 1 }).addTo(map);
                        locationMarker = L.circleMarker([lat, lng], { radius: 8, color: '#fff', weight: 2, fillColor: '#1e6fad', fillOpacity: 1 })
                            .addTo(map)
                            .bindPopup('<b><i class="fas fa-person"></i> You are here</b><br><small>Accuracy: ~' + Math.round(acc) + 'm</small>')
                            .openPopup();
                        map.setView([lat, lng], 12);
                        btn.classList.remove('locating');
                        btn.classList.add('located');
                        btn.innerHTML = '<i class="fas fa-location-crosshairs"></i>';
                    },
                    function() {
                        alert('Unable to get your location. Please allow location access and try again.');
                        btn.classList.remove('locating');
                        btn.innerHTML = '<i class="fas fa-location-crosshairs"></i>';
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            });
            return btn;
        }
    });
    new LocateControl().addTo(map);
})();
</script>
<div class="container section">
    <div class="section-header">
        <h2>Explore by <span class="highlight">Category</span></h2>
    </div>
    <div class="categories-grid">
        <?php foreach ($categories as $category): ?>
            <a href="explore.php?category=<?= $category['id'] ?>" class="category-card">
                <div class="category-icon"><i class="<?= htmlspecialchars($category['category_icon'] ?: 'fas fa-map-marker-alt') ?>"></i></div>
                <h3><?= htmlspecialchars($category['category_name']) ?></h3>
                <p><?= $category['place_count'] ?> places</p>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<div class="container section">
    <div class="section-header">
        <h2>Featured <span class="highlight">Places</span></h2>
    </div>
    <div class="places-grid">
        <?php if (empty($featured)): ?>
            <p>No featured places yet. Check back soon!</p>
        <?php endif; ?>
        <?php foreach ($featured as $place): ?>
            <div class="place-card">
                <div class="place-image" style="background-image: url('<?= $place['image'] ? BASE_URL . 'uploads/' . htmlspecialchars($place['image']) : BASE_URL . 'assets/images/placeholder.svg' ?>')"></div>
                <div class="place-info">
                    <h3><a href="place-details.php?id=<?= $place['id'] ?>"><?= htmlspecialchars($place['title']) ?></a></h3>
                    <div class="place-meta">
                        <span class="place-category"><i class="<?= htmlspecialchars($place['category_icon'] ?: 'fas fa-tag') ?>"></i> <?= htmlspecialchars($place['category_name']) ?></span>
                        <span class="place-rating"><i class="fas fa-star"></i> <?= number_format($place['avg_rating'] ?? 0, 1) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<div class="container section cta-section">
    <div class="cta-container">
        <div class="cta-content">
            <h2>Share Your Hidden Sri Lanka</h2>
            <p>Know a secret waterfall or a local restaurant worth visiting? Submit it and help travelers discover a new side of Sri Lanka.</p>
            <a href="user/add-place.php" class="btn btn-primary">Add New Place</a>
        </div>
        <div class="cta-image"><i class="fas fa-compass"></i></div>
    </div>
</div>
<div class="container section">
    <div class="section-header">
        <h2>Hidden <span class="highlight">Gems</span></h2>
    </div>
    <div class="places-grid">
        <?php if (empty($gems)): ?>
            <p>No hidden gems available yet.</p>
        <?php endif; ?>
        <?php foreach ($gems as $place): ?>
            <div class="place-card">
                <div class="place-image" style="background-image: url('<?= $place['image'] ? BASE_URL . 'uploads/' . htmlspecialchars($place['image']) : BASE_URL . 'assets/images/placeholder.svg' ?>')">
                    <span class="gem-badge">Hidden Gem</span>
                </div>
                <div class="place-info">
                    <h3><a href="place-details.php?id=<?= $place['id'] ?>"><?= htmlspecialchars($place['title']) ?></a></h3>
                    <div class="place-meta">
                        <span class="place-category"><i class="<?= htmlspecialchars($place['category_icon'] ?: 'fas fa-tag') ?>"></i> <?= htmlspecialchars($place['category_name']) ?></span>
                        <span class="place-rating"><i class="fas fa-star"></i> <?= number_format($place['avg_rating'] ?? 0, 1) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<!-- ── About Section ── -->
<section class="home-about" id="about">
  <div class="container">
    <div class="home-about-inner">
      <div class="home-about-text">
        <span class="home-section-eyebrow">Who We Are</span>
        <h2>About <span class="highlight">Hidden Sri Lanka</span></h2>
        <p class="home-about-lead">Hidden Sri Lanka is a community-powered tourism platform that connects adventurous travellers with authentic local experiences across the island.</p>
        <p>Our mission is to showcase hidden gems, support local communities, and give travellers a trusted way to discover off-the-beaten-path places. Contributors submit new destinations and our team reviews every submission for quality and accuracy.</p>
        <div class="home-about-pillars">
          <div class="home-pillar">
            <div class="home-pillar-icon"><i class="fas fa-users"></i></div>
            <div>
              <strong>Community-Driven</strong>
              <span>Local contributors share their favourite secret spots.</span>
            </div>
          </div>
          <div class="home-pillar">
            <div class="home-pillar-icon"><i class="fas fa-shield-alt"></i></div>
            <div>
              <strong>Curated Quality</strong>
              <span>Admin approval keeps every listing reliable.</span>
            </div>
          </div>
          <div class="home-pillar">
            <div class="home-pillar-icon"><i class="fas fa-star"></i></div>
            <div>
              <strong>Discover With Confidence</strong>
              <span>Browse approved places, reviews, and ratings.</span>
            </div>
          </div>
        </div>
      </div>
      <div class="home-about-visual">
        <div class="home-about-card hac-1"><i class="fas fa-umbrella-beach"></i><span>Beaches</span></div>
        <div class="home-about-card hac-2"><i class="fas fa-mountain"></i><span>Mountains</span></div>
        <div class="home-about-card hac-3"><i class="fas fa-water"></i><span>Waterfalls</span></div>
        <div class="home-about-card hac-4"><i class="fas fa-tree"></i><span>Forests</span></div>
        <div class="home-about-globe"><i class="fas fa-map-marked-alt"></i></div>
      </div>
    </div>
  </div>
</section>

<!-- ── Contact Section ── -->
<section class="home-contact" id="contact">
  <div class="container">
    <div class="home-section-header">
      <span class="home-section-eyebrow">Get In Touch</span>
      <h2>Contact <span class="highlight">Us</span></h2>
      <p>Need help or want to share feedback? Reach out and we'll get back to you shortly.</p>
    </div>
    <div class="home-contact-grid">
      <div class="home-contact-info">
        <div class="home-contact-item">
          <div class="home-contact-icon"><i class="fas fa-envelope"></i></div>
          <div>
            <strong>Email Us</strong>
            <a href="mailto:support@hiddensrilanka.com">support@hiddensrilanka.com</a>
          </div>
        </div>
        <div class="home-contact-item">
          <div class="home-contact-icon"><i class="fas fa-phone"></i></div>
          <div>
            <strong>Call Us</strong>
            <a href="tel:+94771234567">+94 77 123 4567</a>
          </div>
        </div>
        <div class="home-contact-item">
          <div class="home-contact-icon"><i class="fas fa-map-marker-alt"></i></div>
          <div>
            <strong>Visit Us</strong>
            <span>Colombo, Sri Lanka</span>
          </div>
        </div>
        <div class="home-contact-socials">
          <a href="#" class="home-social-btn" title="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="home-social-btn" title="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="#" class="home-social-btn" title="Twitter/X"><i class="fab fa-x-twitter"></i></a>
          <a href="#" class="home-social-btn" title="YouTube"><i class="fab fa-youtube"></i></a>
        </div>
      </div>
      <form class="home-contact-form" onsubmit="handleContactForm(event)">
        <div class="hcf-row">
          <div class="hcf-field">
            <label>Your Name</label>
            <input type="text" placeholder="John Doe" required>
          </div>
          <div class="hcf-field">
            <label>Email Address</label>
            <input type="email" placeholder="john@email.com" required>
          </div>
        </div>
        <div class="hcf-field">
          <label>Subject</label>
          <input type="text" placeholder="How can we help?">
        </div>
        <div class="hcf-field">
          <label>Message</label>
          <textarea rows="4" placeholder="Your message…" required></textarea>
        </div>
        <button type="submit" class="hcf-submit">Send Message <i class="fas fa-paper-plane"></i></button>
        <div class="hcf-success" id="hcfSuccess" style="display:none"><i class="fas fa-check-circle"></i> Thanks! We'll be in touch soon.</div>
      </form>
    </div>
  </div>
</section>

<script>
function handleContactForm(e) {
    e.preventDefault();
    e.target.querySelector('.hcf-submit').disabled = true;
    document.getElementById('hcfSuccess').style.display = 'flex';
    setTimeout(function(){ e.target.reset(); e.target.querySelector('.hcf-submit').disabled = false; document.getElementById('hcfSuccess').style.display='none'; }, 4000);
}
</script>

<?php include_once 'includes/footer.php'; ?>
