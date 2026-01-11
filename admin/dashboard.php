<?php


$pageTitle = 'Dashboard';
$pageSubtitle = '';

require_once 'templates/functions.php';
require_once 'templates/header.php';

$pdo = getDB();

$stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_user");
$totalUsers = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_buss");
$totalBuses = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_booking");
$totalBookings = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_payment_method WHERE payment_status = 'pending'");
$pendingPayments = $stmt->fetch()['total'];

$revenueStmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM tb_booking WHERE status = 'paid'");
$revenue = $revenueStmt->fetch()['total'];


$recentBookings = $pdo->query("
    SELECT bk.*, u.name as customer_name, 
           b.model, b.bus_number, b.bus_type,
           r.departure_city, r.arrival_city,
           s.departure_time
    FROM tb_booking bk
    LEFT JOIN tb_user u ON bk.user_id = u.user_id
    LEFT JOIN tb_schedule s ON bk.schedule_id = s.schedule_id
    LEFT JOIN tb_buss b ON s.bus_id = b.bus_id
    LEFT JOIN tb_route r ON s.route_id = r.route_id
    ORDER BY bk.created_at DESC
    LIMIT 5
")->fetchAll();


$pendingPaymentsList = $pdo->query("
    SELECT p.*, bk.booking_code, u.name as customer_name
    FROM tb_payment_method p
    LEFT JOIN tb_booking bk ON p.booking_id = bk.booking_id
    LEFT JOIN tb_user u ON bk.user_id = u.user_id
    WHERE p.payment_status = 'pending'
    ORDER BY p.payment_id DESC
    LIMIT 5
")->fetchAll();
?>

<p class="text-muted mb-4">Selamat datang kembali, <?php echo htmlspecialchars($nama); ?>!</p>

    <!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-icon">
            <i class="fa-solid fa-users"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($totalUsers); ?></h3>
            <p>Total Users</p>
        </div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon">
            <i class="fa-solid fa-bus"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($totalBuses); ?></h3>
            <p>Total Armada</p>
        </div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon">
            <i class="fa-solid fa-ticket"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($totalBookings); ?></h3>
            <p>Total Booking</p>
        </div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon">
            <i class="fa-solid fa-money-bill"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo formatRupiah($revenue); ?></h3>
            <p>Pendapatan</p>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Bookings -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa-solid fa-ticket me-2"></i>Booking Terbaru</h5>
                <a href="bookings.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Customer</th>
                            <th>Rute</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentBookings)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">Belum ada booking</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($recentBookings as $bk): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($bk['booking_code']); ?></strong></td>
                            <td><?php echo htmlspecialchars($bk['customer_name']); ?></td>
                            <td>
                                <!-- PERUBAHAN: origin_city -> departure_city, destination_city -> arrival_city -->
                                <?php echo htmlspecialchars($bk['departure_city']); ?> → <?php echo htmlspecialchars($bk['arrival_city']); ?>
                            </td>
                            <td><?php echo getStatusBadge($bk['status']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Pending Payments -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa-solid fa-clock me-2"></i>Menunggu Validasi</h5>
                <span class="badge bg-warning"><?php echo $pendingPayments; ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($pendingPaymentsList)): ?>
                <p class="text-muted text-center mb-0">Tidak ada pembayaran pending</p>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($pendingPaymentsList as $pp): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            <strong><?php echo htmlspecialchars($pp['booking_code']); ?></strong>
                            <br><small class="text-muted"><?php echo htmlspecialchars($pp['customer_name']); ?></small>
                        </div>
                        <strong><?php echo formatRupiah($pp['amount']); ?></strong>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <a href="payments.php?status=pending" class="btn btn-warning btn-sm w-100 mt-3">
                    Validasi Sekarang
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Links -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa-solid fa-bolt me-2"></i>Aksi Cepat</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="armada.php?action=add" class="btn btn-outline-primary">
                        <i class="fa-solid fa-plus me-1"></i> Tambah Armada
                    </a>
                    <a href="schedules.php?action=add" class="btn btn-outline-success">
                        <i class="fa-solid fa-calendar-plus me-1"></i> Buat Jadwal
                    </a>
                    <a href="routes.php?action=add" class="btn btn-outline-info">
                        <i class="fa-solid fa-route me-1"></i> Tambah Rute
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
