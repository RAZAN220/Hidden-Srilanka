<nav class="navbar" id="mainNav">
    <div class="container nav-container">
        <a href="<?= BASE_URL ?>" class="nav-brand">
            <i class="fas fa-leaf"></i> Hidden Sri Lanka
        </a>
        <button class="nav-toggle" id="navToggle"><i class="fas fa-bars"></i></button>
        <ul class="nav-links" id="navLinks">
            <li><a href="<?= BASE_URL ?>">Home</a></li>
            <li><a href="<?= BASE_URL ?>explore.php">Explore</a></li>
            <li><a href="<?= BASE_URL ?>about.php">About</a></li>
            <li><a href="<?= BASE_URL ?>contact.php">Contact</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="<?= BASE_URL ?>user/dashboard.php">Dashboard</a></li>
                <li><a href="<?= BASE_URL ?>logout.php">Logout</a></li>
            <?php else: ?>
                <li class="nav-auth">
                    <a href="<?= BASE_URL ?>login.php" class="btn btn-outline-light btn-sm">Login</a>
                    <a href="<?= BASE_URL ?>register.php" class="btn btn-primary btn-sm">Register</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>