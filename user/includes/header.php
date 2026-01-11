<?php

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Redirect admin away
if ($_SESSION['role'] === 'admin') {
    header('Location: ../admin/dashboard.php');
    exit;
}

require_once __DIR__ . '/../../admin/config/database.php';
require_once __DIR__ . '/../../admin/templates/functions.php';

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$userId = $_SESSION['user_id'];
$nama = $_SESSION['nama'] ?? 'User';
$role = $_SESSION['role'] ?? 'customer';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Dashboard'; ?> - GoGoTrans</title>
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/user.css">
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">
            <img src="../assets/images/Travel.png" alt="GoGoTrans">
        </a>
        <div class="navbar-menu">
            <a href="dashboard.php" <?php echo $currentPage === 'dashboard' ? 'class="active"' : ''; ?>>Home</a>
            <a href="../armada.php">Armada</a>
            <a href="bookings.php" <?php echo $currentPage === 'bookings' ? 'class="active"' : ''; ?>>Tiket Saya</a>
            <a href="profile.php" <?php echo $currentPage === 'profile' ? 'class="active"' : ''; ?>>Profil</a>
        </div>
        <div class="navbar-user">
            <div class="user-info">
                <div class="name"><?php echo htmlspecialchars($nama); ?></div>
                <div class="role"><?php echo htmlspecialchars($role); ?></div>
            </div>
            <a href="../logout.php" class="btn-logout">
                <i class="fa-solid fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>
    
    <div class="dashboard-container">
