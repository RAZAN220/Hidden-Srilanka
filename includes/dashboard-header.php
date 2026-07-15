<?php
// Variables expected before including this file:
// $pageTitle  — string, shown in topbar
// $activePage — string, highlights the active sidebar link
// $dashType   — 'admin' or 'user'
$dashType = $dashType ?? 'user';
$avatarSrc = !empty($_SESSION['profile_image'])
    ? BASE_URL . 'uploads/' . htmlspecialchars($_SESSION['profile_image'])
    : BASE_URL . 'assets/images/default-avatar.svg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> — Hidden Sri Lanka</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="db-page">
<div class="db-wrapper">

  <!-- Sidebar -->
  <aside class="db-sidebar" id="dbSidebar">
    <div class="db-sidebar-brand">
      <a href="<?= BASE_URL ?>"><i class="fas fa-leaf"></i><span> Hidden Sri Lanka</span></a>
    </div>

    <div class="db-user-card">
      <img src="<?= $avatarSrc ?>" alt="Avatar" class="db-user-avatar">
      <div class="db-user-info">
        <p class="db-user-name"><?= htmlspecialchars($_SESSION['full_name']) ?></p>
        <span class="db-user-badge"><?= ucfirst($_SESSION['role']) ?></span>
      </div>
    </div>

    <nav class="db-nav">
      <p class="db-nav-label">Navigation</p>

      <?php if ($dashType === 'admin'): ?>
        <a href="<?= BASE_URL ?>admin/dashboard.php" class="db-nav-item <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
          <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
        </a>
        <a href="<?= BASE_URL ?>admin/places.php" class="db-nav-item <?= ($activePage ?? '') === 'places' ? 'active' : '' ?>">
          <i class="fas fa-map-marker-alt"></i><span>Manage Places</span>
        </a>
        <a href="<?= BASE_URL ?>admin/users.php" class="db-nav-item <?= ($activePage ?? '') === 'users' ? 'active' : '' ?>">
          <i class="fas fa-users"></i><span>Manage Users</span>
        </a>
        <a href="<?= BASE_URL ?>admin/categories.php" class="db-nav-item <?= ($activePage ?? '') === 'categories' ? 'active' : '' ?>">
          <i class="fas fa-tags"></i><span>Categories</span>
        </a>
        <p class="db-nav-label">Site</p>
        <a href="<?= BASE_URL ?>explore.php" class="db-nav-item">
          <i class="fas fa-compass"></i><span>Explore</span>
        </a>
        <a href="<?= BASE_URL ?>" class="db-nav-item">
          <i class="fas fa-home"></i><span>Home</span>
        </a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>user/dashboard.php" class="db-nav-item <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
          <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
        </a>
        <a href="<?= BASE_URL ?>user/add-place.php" class="db-nav-item <?= ($activePage ?? '') === 'add-place' ? 'active' : '' ?>">
          <i class="fas fa-plus-circle"></i><span>Add Place</span>
        </a>
        <a href="<?= BASE_URL ?>user/my-places.php" class="db-nav-item <?= ($activePage ?? '') === 'my-places' ? 'active' : '' ?>">
          <i class="fas fa-map-pin"></i><span>My Places</span>
        </a>
        <a href="<?= BASE_URL ?>user/favorites.php" class="db-nav-item <?= ($activePage ?? '') === 'favorites' ? 'active' : '' ?>">
          <i class="fas fa-heart"></i><span>Favorites</span>
        </a>
        <a href="<?= BASE_URL ?>user/profile.php" class="db-nav-item <?= ($activePage ?? '') === 'profile' ? 'active' : '' ?>">
          <i class="fas fa-user-circle"></i><span>Profile</span>
        </a>
        <p class="db-nav-label">Site</p>
        <a href="<?= BASE_URL ?>explore.php" class="db-nav-item">
          <i class="fas fa-compass"></i><span>Explore</span>
        </a>
        <a href="<?= BASE_URL ?>" class="db-nav-item">
          <i class="fas fa-home"></i><span>Home</span>
        </a>
      <?php endif; ?>

      <a href="<?= BASE_URL ?>logout.php" class="db-nav-item db-nav-logout">
        <i class="fas fa-sign-out-alt"></i><span>Logout</span>
      </a>
    </nav>
  </aside>

  <!-- Right side -->
  <div class="db-content-wrap" id="dbContentWrap">

    <!-- Top bar -->
    <header class="db-topbar">
      <div class="db-topbar-left">
        <button class="db-toggle-btn" id="dbToggleBtn" title="Toggle sidebar">
          <i class="fas fa-bars"></i>
        </button>
        <div class="db-topbar-title">
          <h1><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
          <nav class="db-breadcrumb">
            <a href="<?= BASE_URL ?>">Home</a>
            <i class="fas fa-chevron-right"></i>
            <span><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></span>
          </nav>
        </div>
      </div>
      <div class="db-topbar-right">
        <a href="<?= BASE_URL ?>explore.php" class="db-topbar-icon" title="Explore places">
          <i class="fas fa-compass"></i>
        </a>
        <div class="db-topbar-profile">
          <img src="<?= $avatarSrc ?>" alt="Avatar">
          <span><?= htmlspecialchars($_SESSION['full_name']) ?></span>
          <i class="fas fa-chevron-down"></i>
        </div>
      </div>
    </header>

    <!-- Page content -->
    <main class="db-main">
