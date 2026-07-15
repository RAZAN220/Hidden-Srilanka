<?php
include_once 'includes/config.php';
include_once 'includes/session.php';

if (isLoggedIn()) {
    redirect(isAdmin() ? 'admin/dashboard.php' : 'user/dashboard.php');
}

$error = '';
$values = ['full_name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name        = trim($_POST['full_name'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $values = ['full_name' => $full_name, 'email' => $email];

    if ($full_name === '' || $email === '' || $password === '' || $confirm_password === '') {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'This email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())');
            $stmt->execute([$full_name, $email, $hash, 'tourist']);
            $userId = $pdo->lastInsertId('users_id_seq');

            session_regenerate_id(true);
            $_SESSION['user_id']      = $userId;
            $_SESSION['full_name']    = $full_name;
            $_SESSION['email']        = $email;
            $_SESSION['role']         = 'tourist';
            $_SESSION['profile_image'] = null;

            redirect('user/dashboard.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Hidden Sri Lanka</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="auth-split-page">

<div class="auth-split-wrap">

  <!-- ── Left panel ── -->
  <div class="auth-split-left">
    <a href="<?= BASE_URL ?>" class="auth-split-brand">
      <i class="fas fa-leaf"></i> Hidden Sri Lanka
    </a>

    <!-- Decorative blobs -->
    <div class="auth-blob auth-blob-1"></div>
    <div class="auth-blob auth-blob-2"></div>
    <div class="auth-blob auth-blob-3"></div>

    <!-- Illustration content -->
    <div class="auth-split-illustration">
      <div class="auth-illus-icon-wrap">
        <i class="fas fa-map-marked-alt auth-illus-main-icon"></i>
      </div>
      <div class="auth-floating-cards">
        <div class="auth-float-card fc-1"><i class="fas fa-umbrella-beach"></i><span>Beaches</span></div>
        <div class="auth-float-card fc-2"><i class="fas fa-mountain"></i><span>Mountains</span></div>
        <div class="auth-float-card fc-3"><i class="fas fa-water"></i><span>Waterfalls</span></div>
      </div>
      <h2 class="auth-split-tagline">Discover Sri Lanka's<br>Hidden Gems</h2>
      <p class="auth-split-sub">Join thousands of travellers sharing secret spots, local trails, and unforgettable places.</p>

      <div class="auth-split-stats">
        <div><span class="auth-stat-num">500+</span><span class="auth-stat-lbl">Places</span></div>
        <div class="auth-stat-div"></div>
        <div><span class="auth-stat-num">2K+</span><span class="auth-stat-lbl">Members</span></div>
        <div class="auth-stat-div"></div>
        <div><span class="auth-stat-num">8</span><span class="auth-stat-lbl">Categories</span></div>
      </div>
    </div>
  </div>

  <!-- ── Right panel ── -->
  <div class="auth-split-right">
    <div class="auth-form-box">
      <div class="auth-form-header">
        <h1>Create Account</h1>
        <p>Fill in the details below to get started</p>
      </div>

      <?php if ($error): ?>
        <div class="auth-alert"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" class="auth-form-grid" novalidate>

        <div class="auth-field auth-field-full">
          <label for="full_name"><i class="fas fa-user"></i> Full Name</label>
          <input type="text" id="full_name" name="full_name"
                 placeholder="Enter your full name"
                 value="<?= htmlspecialchars($values['full_name']) ?>" required>
        </div>

        <div class="auth-field auth-field-full">
          <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
          <input type="email" id="email" name="email"
                 placeholder="Enter your email"
                 value="<?= htmlspecialchars($values['email']) ?>" required>
        </div>

        <div class="auth-field">
          <label for="password"><i class="fas fa-lock"></i> Password</label>
          <div class="auth-input-wrap">
            <input type="password" id="password" name="password"
                   placeholder="Min. 6 characters" required>
            <button type="button" class="auth-pw-toggle" data-target="password" tabindex="-1">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>

        <div class="auth-field">
          <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password</label>
          <div class="auth-input-wrap">
            <input type="password" id="confirm_password" name="confirm_password"
                   placeholder="Repeat password" required>
            <button type="button" class="auth-pw-toggle" data-target="confirm_password" tabindex="-1">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>

        <div class="auth-field auth-field-full">
          <button type="submit" class="auth-submit-btn">
            Create Account <i class="fas fa-arrow-right"></i>
          </button>
        </div>

      </form>

      <p class="auth-switch-link">
        Already have an account? <a href="<?= BASE_URL ?>login.php">Sign In</a>
      </p>

      <a href="<?= BASE_URL ?>" class="auth-back-link">
        <i class="fas fa-arrow-left"></i> Back to Home
      </a>
    </div>
  </div>

</div>

<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script>
// Password visibility toggle
document.querySelectorAll('.auth-pw-toggle').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var inp = document.getElementById(this.dataset.target);
        var icon = this.querySelector('i');
        if (inp.type === 'password') {
            inp.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            inp.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
});
</script>
</body>
</html>
