    </main>
    <footer class="site-footer">
      <div class="container">
        <div class="sf-grid">

          <!-- Brand -->
          <div class="sf-col sf-brand-col">
            <a href="<?= BASE_URL ?>" class="sf-brand"><i class="fas fa-leaf"></i> Hidden Sri Lanka</a>
            <p class="sf-tagline">Connecting adventurous travellers with authentic local experiences across the island of Sri Lanka.</p>
            <div class="sf-socials">
              <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
              <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
              <a href="#" title="Twitter/X"><i class="fab fa-x-twitter"></i></a>
              <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
            </div>
          </div>

          <!-- Explore -->
          <div class="sf-col">
            <h4>Explore</h4>
            <ul>
              <li><a href="<?= BASE_URL ?>explore.php">All Places</a></li>
              <li><a href="<?= BASE_URL ?>explore.php?status=approved">Approved Gems</a></li>
              <li><a href="<?= BASE_URL ?>user/add-place.php">Submit a Place</a></li>
              <li><a href="<?= BASE_URL ?>register.php">Join the Community</a></li>
            </ul>
          </div>

          <!-- Company -->
          <div class="sf-col">
            <h4>Company</h4>
            <ul>
              <li><a href="<?= BASE_URL ?>#about">About Us</a></li>
              <li><a href="<?= BASE_URL ?>#contact">Contact</a></li>
              <li><a href="<?= BASE_URL ?>login.php">Sign In</a></li>
              <li><a href="<?= BASE_URL ?>register.php">Register</a></li>
            </ul>
          </div>

          <!-- Contact -->
          <div class="sf-col">
            <h4>Contact</h4>
            <ul class="sf-contact-list">
              <li><i class="fas fa-envelope"></i> <a href="mailto:support@hiddensrilanka.com">support@hiddensrilanka.com</a></li>
              <li><i class="fas fa-phone"></i> <a href="tel:+94771234567">+94 77 123 4567</a></li>
              <li><i class="fas fa-map-marker-alt"></i> Colombo, Sri Lanka</li>
            </ul>
          </div>

        </div>
        <div class="sf-bottom">
          <span>&copy; <?= date('Y') ?> Hidden Sri Lanka. All rights reserved.</span>
          <span>Made with <i class="fas fa-heart" style="color:#e74c3c"></i> for Sri Lanka</span>
        </div>
      </div>
    </footer>
    <script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>
