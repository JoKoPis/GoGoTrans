<?php
/**
 * Admin Header Include
 * Contains common header, sidebar, and CSS
 */

session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header('Location: ../user/dashboard.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$nama = $_SESSION['nama'] ?? 'Admin';
$role = $_SESSION['role'] ?? 'admin';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin Panel'; ?> - GoGoTrans</title>
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/images/Travel2.png" alt="GoGoTrans" class="sidebar-logo">
                <h3>Admin Panel</h3>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-dashboard"></i>
                    <span>Dashboard</span>
                </a>
                <a href="armada.php" class="nav-item <?php echo $currentPage === 'armada' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-bus"></i>
                    <span>Kelola Armada</span>
                </a>
                <a href="routes.php" class="nav-item <?php echo $currentPage === 'routes' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-route"></i>
                    <span>Kelola Rute</span>
                </a>
                <a href="schedules.php" class="nav-item <?php echo $currentPage === 'schedules' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-calendar"></i>
                    <span>Jadwal</span>
                </a>
                <a href="users.php" class="nav-item <?php echo $currentPage === 'users' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-users"></i>
                    <span>Kelola Users</span>
                </a>
                <a href="bookings.php" class="nav-item <?php echo $currentPage === 'bookings' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-ticket"></i>
                    <span>Booking</span>
                </a>
                <a href="payments.php" class="nav-item <?php echo $currentPage === 'payments' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-credit-card"></i>
                    <span>Pembayaran</span>
                </a>
            </nav>
            <div class="sidebar-footer">
                <a href="../logout.php" class="btn-logout">
                    <i class="fa-solid fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-bar">
                <div class="page-title">
                    <h1><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
                    <p><?php echo $pageSubtitle ?? ''; ?></p>
                </div>
                <div class="user-menu">
                    <div class="user-avatar">
                        <i class="fa-solid fa-user-circle"></i>
                    </div>
                    <div class="user-info">
                        <span class="name"><?php echo htmlspecialchars($nama); ?></span>
                        <span class="role"><?php echo htmlspecialchars($role); ?></span>
                    </div>
                </div>
            </header>

            <div class="content-area">
        