<?php

$pageTitle = 'Tiket Saya';
require_once 'includes/header.php';

$statusFilter = $_GET['status'] ?? '';
$userId = $_SESSION['user_id'];

// Get user's bookings
$pdo = getDB();
$where = $statusFilter ? "AND bk.status = '$statusFilter'" : '';

$stmt = $pdo->prepare("
    SELECT bk.*, s.departure_time, s.arrival_time, s.price,
           b.model, b.bus_number, b.bus_type,
           r.departure_city, r.arrival_city
    FROM tb_booking bk
    LEFT JOIN tb_schedule s ON bk.schedule_id = s.schedule_id
    LEFT JOIN tb_buss b ON s.bus_id = b.bus_id
    LEFT JOIN tb_route r ON s.route_id = r.route_id
    WHERE bk.user_id = ? $where
    ORDER BY bk.created_at DESC
");
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();
?>

<?php showFlash(); ?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa-solid fa-ticket me-2"></i>Tiket Saya</h5>
        <div class="btn-group" role="group">
            <a href="bookings.php" class="btn btn-sm <?php echo !$statusFilter ? 'btn-primary' : 'btn-outline-primary'; ?>">Semua</a>
            <a href="?status=pending" class="btn btn-sm <?php echo $statusFilter === 'pending' ? 'btn-warning' : 'btn-outline-warning'; ?>">Pending</a>
            <a href="?status=paid" class="btn btn-sm <?php echo $statusFilter === 'paid' ? 'btn-success' : 'btn-outline-success'; ?>">Paid</a>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($bookings)): ?>
        <div class="text-center py-5">
            <i class="fa-solid fa-ticket fa-3x text-muted mb-3"></i>
            <p class="text-muted">Belum ada booking</p>
            <a href="../armada.php" class="btn btn-primary">Pesan Sekarang</a>
        </div>
        <?php else: ?>
        <?php foreach ($bookings as $bk): ?>
        <div class="card mb-3 <?php echo $bk['status'] === 'paid' ? 'border-success' : ($bk['status'] === 'pending' ? 'border-warning' : ''); ?>">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        <i class="fa-solid fa-bus fa-2x text-primary mb-2"></i>
                        <p class="mb-0 small"><?php echo getBusTypeLabel($bk['bus_type']); ?></p>
                    </div>
                    <div class="col-md-4">
                        <!-- PERUBAHAN: origin_city->departure_city, destination_city->arrival_city -->
                        <h5 class="mb-1"><?php echo htmlspecialchars($bk['departure_city']); ?> → <?php echo htmlspecialchars($bk['arrival_city']); ?></h5>
                        <p class="text-muted mb-1">
                            <i class="fa-regular fa-calendar me-1"></i>
                            <!-- PERUBAHAN: departure_datetime->departure_time -->
                            <?php echo formatDate($bk['departure_time'], 'd M Y'); ?>
                        </p>
                        <p class="text-muted mb-0">
                            <i class="fa-regular fa-clock me-1"></i>
                            <?php echo formatDate($bk['departure_time'], 'H:i'); ?> WIB
                        </p>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-1"><strong>Kode:</strong> <?php echo htmlspecialchars($bk['booking_code']); ?></p>
                        <!-- PERUBAHAN: bus_name->model, plate_number->bus_number -->
                        <p class="mb-0"><strong>Armada:</strong> <?php echo htmlspecialchars($bk['model'] ?: $bk['bus_number']); ?></p>
                    </div>
                    <div class="col-md-2 text-end">
                        <!-- PERUBAHAN: total_price->total_amount -->
                        <h5 class="text-primary mb-2"><?php echo formatRupiah($bk['total_amount']); ?></h5>
                        <?php echo getStatusBadge($bk['status']); ?>
                    </div>
                    <div class="col-md-1 text-end">
                        <!-- PERUBAHAN: id->booking_id -->
                        <a href="booking-detail.php?id=<?php echo $bk['booking_id']; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
