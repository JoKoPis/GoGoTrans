<?php
/**
 * ============================================
 * ADMIN - KELOLA BOOKING
 * ============================================
 * View and manage bookings dengan tb_booking
 */

$pageTitle = 'Kelola Booking';
$pageSubtitle = 'Manage customer bookings';

require_once 'templates/functions.php';
require_once 'templates/header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$pdo = getDB();

// ============================================
// PROSES ACTIONS
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    
    if ($postAction === 'update_status') {
        try {
            // PERUBAHAN: bookings -> tb_booking, id -> booking_id
            $sql = "UPDATE tb_booking SET status = ? WHERE booking_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_POST['status'], $_POST['id']]);
            
            setFlash('success', 'Status booking berhasil diupdate!');
        } catch (PDOException $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
        header('Location: bookings.php');
        exit;
    }
}

// ============================================
// AMBIL DAFTAR BOOKING
// ============================================
$statusFilter = $_GET['status'] ?? '';
$where = $statusFilter ? "WHERE bk.status = ?" : "";
$params = $statusFilter ? [$statusFilter] : [];

$sql = "SELECT bk.*, u.username, u.email as customer_email,
               s.departure_time, s.arrival_time, s.price,
               b.model, b.bus_number,
               r.departure_city, r.arrival_city
        FROM tb_booking bk
        LEFT JOIN tb_user u ON bk.user_id = u.user_id
        LEFT JOIN tb_schedule s ON bk.schedule_id = s.schedule_id
        LEFT JOIN tb_buss b ON s.bus_id = b.bus_id
        LEFT JOIN tb_route r ON s.route_id = r.route_id
        $where
        ORDER BY bk.created_at DESC";

if ($params) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
} else {
    $stmt = $pdo->query($sql);
}
$bookings = $stmt->fetchAll();

$booking = null;
$passengers = [];
$seats = [];
$payment = null;

if ($action === 'view' && $id) {
    // Detail Booking
    $sql = "SELECT bk.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone, u.address as customer_address,
                   s.departure_time, s.arrival_time, s.price,
                   b.model, b.bus_number, b.bus_type,
                   r.departure_city, r.arrival_city
            FROM tb_booking bk
            LEFT JOIN tb_user u ON bk.user_id = u.user_id
            LEFT JOIN tb_schedule s ON bk.schedule_id = s.schedule_id
            LEFT JOIN tb_buss b ON s.bus_id = b.bus_id
            LEFT JOIN tb_route r ON s.route_id = r.route_id
            WHERE bk.booking_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $booking = $stmt->fetch();
    
    if ($booking) {
        // PERUBAHAN: passengers -> tb_passengers
        $stmt = $pdo->prepare("SELECT * FROM tb_passengers WHERE booking_id = ? ORDER BY passenger_id ASC");
        $stmt->execute([$id]);
        $passengers = $stmt->fetchAll();
        
        // PERUBAHAN: booking_seats -> tb_booking_details, seats -> tb_seat
        $seatsStmt = $pdo->prepare("
            SELECT bd.*, st.seat_number 
            FROM tb_booking_details bd
            LEFT JOIN tb_seat st ON bd.seat_id = st.seat_id
            WHERE bd.booking_id = ?
        ");
        $seatsStmt->execute([$id]);
        $seats = $seatsStmt->fetchAll();
        
        // PERUBAHAN: payments -> tb_payment_method
        $paymentStmt = $pdo->prepare("SELECT * FROM tb_payment_method WHERE booking_id = ? ORDER BY payment_id DESC LIMIT 1");
        $paymentStmt->execute([$id]);
        $payment = $paymentStmt->fetch();
    }
}
?>

<?php showFlash(); ?>

<?php if ($action === 'list'): ?>
<div class="card">
    <div class="card-header">
        <h2><i class="fa-solid fa-ticket me-2"></i>Daftar Booking</h2>
        <div class="btn-group mt-2" role="group">
            <a href="bookings.php" class="btn btn-sm <?php echo !$statusFilter ? 'btn-primary' : 'btn-outline-primary'; ?>">Semua</a>
            <a href="?status=pending" class="btn btn-sm <?php echo $statusFilter === 'pending' ? 'btn-warning' : 'btn-outline-warning'; ?>">Pending</a>
            <a href="?status=paid" class="btn btn-sm <?php echo $statusFilter === 'paid' ? 'btn-success' : 'btn-outline-success'; ?>">Paid</a>
            <a href="?status=cancelled" class="btn btn-sm <?php echo $statusFilter === 'cancelled' ? 'btn-danger' : 'btn-outline-danger'; ?>">Cancelled</a>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Customer</th>
                    <th>Rute</th>
                    <th>Berangkat</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bookings)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">Belum ada booking</td>
                </tr>
                <?php else: ?>
                <?php foreach ($bookings as $bk): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($bk['booking_code']); ?></strong></td>
                    <td>
                        <?php echo htmlspecialchars($bk['username'] ?: $bk['customer_email']); ?>
                        <br><small class="text-muted"><?php echo htmlspecialchars($bk['customer_email']); ?></small>
                    </td>
                    <td>
                        <!-- PERUBAHAN: origin_city -> departure_city, destination_city -> arrival_city -->
                        <?php echo htmlspecialchars($bk['departure_city']); ?> → <?php echo htmlspecialchars($bk['arrival_city']); ?>
                    </td>
                    <!-- PERUBAHAN: departure_datetime -> departure_time -->
                    <td><?php echo formatDate($bk['departure_time']); ?></td>
                    <!-- PERUBAHAN: total_price -> total_amount -->
                    <td><strong><?php echo formatRupiah($bk['total_amount']); ?></strong></td>
                    <td><?php echo getStatusBadge($bk['status']); ?></td>
                    <td>
                        <!-- PERUBAHAN: id -> booking_id -->
                        <a href="?action=view&id=<?php echo $bk['booking_id']; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-eye"></i> Detail
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($action === 'view' && $booking): ?>
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Booking: <?php echo htmlspecialchars($booking['booking_code']); ?></h5>
                <?php echo getStatusBadge($booking['status']); ?>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Rute Perjalanan</h6>
                        <p class="mb-1">
                            <!-- PERUBAHAN: origin_city -> departure_city -->
                            <strong><?php echo htmlspecialchars($booking['departure_city']); ?></strong>
                            <i class="fa-solid fa-arrow-right mx-2"></i>
                            <!-- PERUBAHAN: destination_city -> arrival_city -->
                            <strong><?php echo htmlspecialchars($booking['arrival_city']); ?></strong>
                        </p>
                        <p class="text-muted mb-0">
                            <!-- PERUBAHAN: bus_name -> model, plate_number -> bus_number -->
                            <?php echo htmlspecialchars($booking['model'] ?: $booking['bus_number']); ?>
                            (<?php echo getBusTypeLabel($booking['bus_type']); ?>)
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Waktu</h6>
                        <!-- PERUBAHAN: departure_datetime -> departure_time -->
                        <p class="mb-1"><strong>Berangkat:</strong> <?php echo formatDate($booking['departure_time']); ?></p>
                        <!-- PERUBAHAN: arrival_datetime -> arrival_time -->
                        <p class="mb-0"><strong>Tiba:</strong> <?php echo formatDate($booking['arrival_time']); ?></p>
                    </div>
                </div>
                
                <h6 class="text-muted">Data Penumpang</h6>
                <table class="table table-sm">
                    <thead>
                        <tr><th>#</th><th>Nama</th><th>No. Identitas</th><th>Telepon</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($passengers)): ?>
                        <tr><td colspan="4" class="text-center text-muted">Tidak ada data penumpang</td></tr>
                        <?php else: ?>
                        <?php foreach ($passengers as $i => $p): ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><?php echo htmlspecialchars($p['name']); ?></td>
                            <td><?php echo $p['identity_number'] ?: '-'; ?></td>
                            <td><?php echo $p['phone'] ?: '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <?php if (!empty($seats)): ?>
                <h6 class="text-muted mt-4">Kursi</h6>
                <p>
                    <?php foreach ($seats as $seat): ?>
                    <span class="badge bg-info me-1"><?php echo htmlspecialchars($seat['seat_number']); ?></span>
                    <?php endforeach; ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Customer</h6></div>
            <div class="card-body">
                <p class="mb-1"><strong><?php echo htmlspecialchars($booking['customer_name'] ?? 'Guest'); ?></strong></p>
                <p class="mb-1"><i class="fa-solid fa-envelope me-2"></i><?php echo htmlspecialchars($booking['customer_email']); ?></p>
                <p class="mb-1"><i class="fa-solid fa-phone me-2"></i><?php echo $booking['customer_phone'] ?: '-'; ?></p>
                <?php if ($booking['customer_address']): ?>
                <p class="mb-0"><i class="fa-solid fa-location-dot me-2"></i><?php echo htmlspecialchars($booking['customer_address']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Pembayaran</h6></div>
            <div class="card-body">
                <!-- PERUBAHAN: total_price -> total_amount -->
                <p class="h4 text-primary mb-3"><?php echo formatRupiah($booking['total_amount']); ?></p>
                <?php if ($payment): ?>
                <!-- PERUBAHAN: payment_method -> method (enum) -->
                <p class="mb-1"><strong>Metode:</strong> <?php echo ucfirst($payment['method'] ?? 'Transfer'); ?></p>
                <p class="mb-1"><strong>Status:</strong> <?php echo getStatusBadge($payment['payment_status']); ?></p>
                <?php if ($payment['payment_proof']): ?>
                <p class="mb-1"><strong>Bukti:</strong></p>
                <a href="<?php echo htmlspecialchars($payment['payment_proof']); ?>" target="_blank">
                    <img src="<?php echo htmlspecialchars($payment['payment_proof']); ?>" class="img-fluid rounded" style="max-height:200px;">
                </a>
                <?php endif; ?>
                <?php else: ?>
                <p class="text-muted">Belum ada pembayaran</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Update Status</h6></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_status">
                    <!-- PERUBAHAN: id -> booking_id -->
                    <input type="hidden" name="id" value="<?php echo $booking['booking_id']; ?>">
                    <select name="status" class="form-select mb-2">
                        <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="paid" <?php echo $booking['status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        <option value="expired" <?php echo $booking['status'] === 'expired' ? 'selected' : ''; ?>>Expired</option>
                    </select>
                    <button type="submit" class="btn btn-primary w-100">Update Status</button>
                </form>
            </div>
        </div>
        
        <a href="bookings.php" class="btn btn-secondary w-100 mt-3">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>
<?php endif; ?>

<?php require_once 'templates/footer.php'; ?>
