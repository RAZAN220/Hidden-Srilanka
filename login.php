<?php
include_once 'includes/config.php';
include_once 'includes/session.php';

if (isLoggedIn()) {
    if (isAdmin()) redirect('admin/dashboard.php');
    redirect('user/dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Both email and password are required.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']       = $user['id'];
            $_SESSION['full_name']     = $user['full_name'];
            $_SESSION['email']         = $user['email'];
            $_SESSION['role']          = $user['role'];
            $_SESSION['profile_image'] = $user['profile_image'];

            if ($user['role'] === 'admin') redirect('admin/dashboard.php');
            redirect('user/dashboard.php');
        }
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Hidden Sri Lanka</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #7b2ff7 0%, #5b6ef5 50%, #a855f7 100%);
      font-family: 'Segoe UI', system-ui, sans-serif;
      padding: 20px;
    }

    /* ── Card ── */
    .login-card {
      display: flex;
      width: 100%;
      max-width: 860px;
      min-height: 500px;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 24px 60px rgba(0,0,0,.28);
    }

    /* ── Left panel ── */
    .login-left {
      flex: 0 0 42%;
      background: linear-gradient(160deg, #00c6fb 0%, #3a8ef6 55%, #1d6ef5 100%);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 36px 32px 0;
      position: relative;
      overflow: hidden;
      color: #fff;
    }

    .login-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 1rem;
      font-weight: 700;
      letter-spacing: .02em;
      text-transform: uppercase;
    }
    .login-brand i { font-size: 1.2rem; }

    .login-welcome {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding-bottom: 30px;
    }
    .login-welcome h2 {
      font-size: 2.1rem;
      font-weight: 800;
      line-height: 1.2;
      margin-bottom: 16px;
    }
    .login-welcome p {
      font-size: .9rem;
      line-height: 1.6;
      opacity: .88;
      max-width: 240px;
    }

    /* Wave SVG at bottom of left panel */
    .login-wave {
      position: absolute;
      bottom: 0; left: 0; right: 0;
      line-height: 0;
    }
    .login-wave svg { display: block; width: 100%; }

    .login-footer-text {
      position: relative;
      z-index: 2;
      font-size: .78rem;
      opacity: .75;
      padding: 14px 32px;
      background: transparent;
    }

    /* ── Right panel ── */
    .login-right {
      flex: 1;
      background: #fff;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 48px 44px;
    }

    .login-right h1 {
      font-size: 2rem;
      font-weight: 800;
      color: #3a8ef6;
      margin-bottom: 6px;
    }
    .login-right .login-subtitle {
      font-size: .88rem;
      color: #64748b;
      margin-bottom: 32px;
    }

    /* Error */
    .login-error {
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #b91c1c;
      border-radius: 8px;
      padding: 10px 14px;
      font-size: .85rem;
      margin-bottom: 18px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    /* Form */
    .lf-group { margin-bottom: 18px; }
    .lf-group label {
      display: block;
      font-size: .82rem;
      color: #64748b;
      margin-bottom: 7px;
    }
    .lf-group input {
      width: 100%;
      padding: 13px 16px;
      border: 1.5px solid #e2e8f0;
      border-radius: 8px;
      font-size: .92rem;
      color: #1e293b;
      transition: border .2s, box-shadow .2s;
      background: #fff;
    }
    .lf-group input:focus {
      outline: none;
      border-color: #3a8ef6;
      box-shadow: 0 0 0 3px rgba(58,142,246,.14);
    }

    /* Password wrapper */
    .lf-pw-wrap { position: relative; }
    .lf-pw-wrap input { padding-right: 44px; }
    .lf-pw-toggle {
      position: absolute;
      right: 14px; top: 50%;
      transform: translateY(-50%);
      background: none; border: none;
      color: #94a3b8; cursor: pointer;
      font-size: .95rem; padding: 0;
    }
    .lf-pw-toggle:hover { color: #3a8ef6; }

    /* Remember me */
    .lf-remember {
      display: flex;
      align-items: center;
      gap: 9px;
      margin-bottom: 24px;
      font-size: .85rem;
      color: #475569;
      cursor: pointer;
    }
    .lf-remember input[type=checkbox] {
      width: 16px; height: 16px;
      accent-color: #3a8ef6;
      cursor: pointer;
    }

    /* Login button */
    .lf-btn {
      width: 100%;
      padding: 14px;
      background: linear-gradient(90deg, #3a8ef6, #1d6ef5);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 700;
      letter-spacing: .07em;
      text-transform: uppercase;
      cursor: pointer;
      transition: opacity .2s, transform .2s;
      margin-bottom: 24px;
    }
    .lf-btn:hover { opacity: .9; transform: translateY(-1px); }
    .lf-btn:active { transform: translateY(0); }

    /* Bottom links */
    .lf-links {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: .83rem;
      color: #64748b;
    }
    .lf-links a { color: #3a8ef6; font-weight: 600; text-decoration: none; }
    .lf-links a:hover { text-decoration: underline; }
    .lf-links .lf-forgot { font-style: italic; color: #94a3b8; }
    .lf-links .lf-forgot:hover { color: #3a8ef6; }

    /* ── Responsive ── */
    @media (max-width: 640px) {
      .login-left { display: none; }
      .login-right { padding: 36px 28px; }
    }
  </style>
</head>
<body>

<div class="login-card">

  <!-- Left panel -->
  <div class="login-left">
    <div class="login-brand">
      <i class="fas fa-leaf"></i>
      <span>Hidden Sri Lanka</span>
    </div>

    <div class="login-welcome">
      <h2>Welcome<br>Back!</h2>
      <p>Discover Sri Lanka's hidden gems — breathtaking spots curated by a passionate community of explorers.</p>
    </div>

    <!-- Decorative wave -->
    <div class="login-wave">
      <svg viewBox="0 0 400 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
        <path d="M0,60 C80,110 160,10 240,60 C320,110 360,30 400,60 L400,120 L0,120 Z"
              fill="rgba(255,255,255,0.12)"/>
        <path d="M0,80 C60,40 140,100 220,70 C300,40 360,90 400,70 L400,120 L0,120 Z"
              fill="rgba(255,255,255,0.18)"/>
      </svg>
    </div>

    <p class="login-footer-text">Explore · Discover · Share</p>
  </div>

  <!-- Right panel -->
  <div class="login-right">
    <h1>Login</h1>
    <p class="login-subtitle">Welcome back! Sign in to continue your journey.</p>

    <?php if ($error): ?>
      <div class="login-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="lf-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               placeholder="you@example.com" required autocomplete="email">
      </div>

      <div class="lf-group">
        <label for="password">Password</label>
        <div class="lf-pw-wrap">
          <input type="password" id="password" name="password"
                 placeholder="••••••••" required autocomplete="current-password">
          <button type="button" class="lf-pw-toggle" id="pwToggle" title="Show/hide password">
            <i class="fas fa-eye" id="pwIcon"></i>
          </button>
        </div>
      </div>

      <label class="lf-remember">
        <input type="checkbox" name="remember"> Remember me
      </label>

      <button type="submit" class="lf-btn">Login</button>

      <div class="lf-links">
        <span>New User? <a href="<?= BASE_URL ?>register.php">Sign Up</a></span>
        <a href="<?= BASE_URL ?>" class="lf-forgot">Back to Home</a>
      </div>
    </form>
  </div>

</div>

<script>
  // Password show/hide
  document.getElementById('pwToggle').addEventListener('click', function() {
    var inp  = document.getElementById('password');
    var icon = document.getElementById('pwIcon');
    if (inp.type === 'password') {
      inp.type = 'text';
      icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
      inp.type = 'password';
      icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
  });
</script>

</body>
</html>
