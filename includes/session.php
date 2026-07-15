<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isContributor() {
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'contributor' || $_SESSION['role'] === 'admin');
}

function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit;
}
?>