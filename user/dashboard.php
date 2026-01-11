<?php

$pageTitle = 'Dashboard';
require_once 'includes/header.php';

// Get user stats
$pdo = getDB();
$userId = $_SESSION['user_id'];

// Total bookings
// PERUBAHAN: bookings -> tb_booking, user_id -> user_id
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_booking WHERE user_id = ?");
$stmt->execute([$userId]);
$totalBookings = $stmt->fetchColumn();

// Active bookings (pending or paid, not departed yet)
// PERUBAHAN: bookings->tb_booking, schedules->tb_schedule, departure_datetime->departure_time
$activeStmt = $pdo->prepare("
    SELECT COUNT(*) FROM tb_booking b
    JOIN tb_schedule s ON b.schedule_id = s.schedule_id
    WHERE b.user_id = ? AND b.status IN ('pending', 'paid') 
    AND s.departure_time > NOW()
");
$activeStmt->execute([$userId]);
$activeBookings = $activeStmt->fetchColumn();


$spentStmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM tb_booking WHERE user_id = ? AND status = 'paid'");
$spentStmt->execute([$userId]);
$totalSpent = $spentStmt->fetchColumn();

$recentStmt = $pdo->prepare("
    SELECT bk.*, s.departure_time, s.arrival_time, s.price,
           b.model, b.bus_number, b.bus_type,
           r.departure_city, r.arrival_city
    FROM tb_booking bk
    LEFT JOIN tb_schedule s ON bk.schedule_id = s.schedule_id
    LEFT JOIN tb_buss b ON s.bus_id = b.bus_id
    LEFT JOIN tb_route r ON s.route_id = r.route_id
    WHERE bk.user_id = ?
    ORDER BY bk.created_at DESC
    LIMIT 3
");
$recentStmt->execute([$userId]);
$recentBookings = $recentStmt->fetchAll();
?>

<div class="welcome-card">
    <div class="welcome-content">
        <h1>Selamat Datang, <?php echo htmlspecialchars($nama); ?>!</h1>
        <p>Kelola perjalanan wisata Anda dengan mudah bersama GoGoTrans</p>
    </div>
    <div class="welcome-image">
        <i class="fa-solid fa-bus-alt"></i>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <i class="fa-solid fa-ticket"></i>
        <div class="stat-info">
            <h3><?php echo $activeBookings; ?></h3>
            <p>Tiket Aktif</p>
        </div>
    </div>
    <div class="stat-card">
        <i class="fa-solid fa-bus"></i>
        <div class="stat-info">
            <h3><?php echo $totalBookings; ?></h3>
            <p>Total Perjalanan</p>
        </div>
    </div>
    <div class="stat-card">
        <i class="fa-solid fa-wallet"></i>
        <div class="stat-info">
            <h3><?php echo formatRupiah($totalSpent); ?></h3>
            <p>Total Transaksi</p>
        </div>
    </div>
    <div class="stat-card">
        <i class="fa-solid fa-star"></i>
        <div class="stat-info">
            <h3>Member</h3>
            <p>Status Anda</p>
        </div>
    </div>
</div>

<div class="content-grid">
    <div class="card">
        <div class="card-header">
            <h2><i class="fa-solid fa-ticket me-2"></i>Booking Terbaru</h2>
        </div>
        <div class="card-body">
            <?php if (empty($recentBookings)): ?>
            <p class="text-muted text-center py-4">Anda belum memiliki booking</p>
            <?php else: ?>
            <?php foreach ($recentBookings as $bk): ?>
            <div class="ticket-item">
                <div class="ticket-info">
                    <!-- PERUBAHAN: bus_name->model, plate_number->bus_number -->
                    <h4><?php echo htmlspecialchars($bk['model'] ?: $bk['bus_number']); ?></h4>
                    <!-- PERUBAHAN: departure_datetime->departure_time -->
                    <p><i class="fa-solid fa-calendar"></i> <?php echo formatDate($bk['departure_time'], 'd M Y, H:i'); ?></p>
                    <!-- PERUBAHAN: origin_city->departure_city, destination_city->arrival_city -->
                    <p><i class="fa-solid fa-map-marker-alt"></i> <?php echo htmlspecialchars($bk['departure_city'] . ' → ' . $bk['arrival_city']); ?></p>
                </div>
                <?php echo getStatusBadge($bk['status']); ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="card-footer">
            <a href="bookings.php" class="btn-link">Lihat Semua Tiket <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2><i class="fa-solid fa-bolt me-2"></i>Aksi Cepat</h2>
        </div>
        <div class="card-body">
            <div class="quick-actions">
                <a href="../jadwal.php" class="action-btn action-primary">
                    <i class="fa-solid fa-ticket"></i>
                    <span>Pesan Tiket</span>
                </a>
                <a href="../armada.php" class="action-btn">
                    <i class="fa-solid fa-bus"></i>
                    <span>Lihat Armada</span>
                </a>
                <a href="bookings.php" class="action-btn">
                    <i class="fa-solid fa-list-check"></i>
                    <span>Tiket Saya</span>
                </a>
                <a href="bookings.php?status=paid" class="action-btn">
                    <i class="fa-solid fa-history"></i>
                    <span>Riwayat</span>
                </a>
                <a href="profile.php" class="action-btn">
                    <i class="fa-solid fa-user-edit"></i>
                    <span>Edit Profil</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
