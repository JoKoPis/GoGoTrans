<?php
/**
 * User - Booking Detail
 * View booking detail and upload payment proof
 */

$pageTitle = 'Detail Booking';
require_once 'includes/header.php';

$bookingId = $_GET['id'] ?? null;
$userId = $_SESSION['user_id'];

if (!$bookingId) {
    header('Location: bookings.php');
    exit;
}

$pdo = getDB();

// Process payment upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'upload_proof') {
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            $result = uploadFile($_FILES['payment_proof'], __DIR__ . '/../admin/uploads');
            if ($result['success']) {
                // Update payment record
                // PERUBAHAN: payments -> tb_payment_method, booking_id -> booking_id
                $stmt = $pdo->prepare("UPDATE tb_payment_method SET payment_proof = ? WHERE booking_id = ?");
                $stmt->execute(['uploads/' . $result['filename'], $bookingId]);
                setFlash('success', 'Bukti pembayaran berhasil diupload! Menunggu validasi admin.');
            } else {
                setFlash('error', 'Gagal upload: ' . $result['message']);
            }
        }
        header('Location: booking-detail.php?id=' . $bookingId);
        exit;
    }
}
$stmt = $pdo->prepare("
    SELECT bk.*, s.departure_time, s.arrival_time, s.price,
           b.model, b.bus_number, b.bus_type, b.facilities,
           r.departure_city, r.arrival_city, r.estimated_duration
    FROM tb_booking bk
    LEFT JOIN tb_schedule s ON bk.schedule_id = s.schedule_id
    LEFT JOIN tb_buss b ON s.bus_id = b.bus_id
    LEFT JOIN tb_route r ON s.route_id = r.route_id
    WHERE bk.booking_id = ? AND bk.user_id = ?
");
$stmt->execute([$bookingId, $userId]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: bookings.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM tb_passengers WHERE booking_id = ? ORDER BY passenger_id ASC");
$stmt->execute([$bookingId]);
$passengers = $stmt->fetchAll();

// Get seats
// PERUBAHAN: booking_seats -> tb_booking_details, seats -> tb_seat
$seatsStmt = $pdo->prepare("
    SELECT bd.*, st.seat_number 
    FROM tb_booking_details bd
    LEFT JOIN tb_seat st ON bd.seat_id = st.seat_id
    WHERE bd.booking_id = ?
");
$seatsStmt->execute([$bookingId]);
$seats = $seatsStmt->fetchAll();

// Get payment
// PERUBAHAN: payments -> tb_payment_method
$paymentStmt = $pdo->prepare("SELECT * FROM tb_payment_method WHERE booking_id = ? ORDER BY payment_id DESC LIMIT 1");
$paymentStmt->execute([$bookingId]);
$payment = $paymentStmt->fetch();
?>

<?php showFlash(); ?>

<div class="row">
    <div class="col-md-8">
        <!-- Ticket Card -->
        <div class="card mb-4" style="background: linear-gradient(135deg, #0259de 0%, #093fb4 100%); color: white; border-radius: 20px;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-white text-primary mb-2"><?php echo htmlspecialchars($booking['booking_code']); ?></span>
                        <!-- PERUBAHAN: origin_city->departure_city, destination_city->arrival_city -->
                        <h3 class="mb-0"><?php echo htmlspecialchars($booking['departure_city']); ?> → <?php echo htmlspecialchars($booking['arrival_city']); ?></h3>
                    </div>
                    <?php 
                    $statusClass = match($booking['status']) {
                        'paid' => 'bg-success',
                        'pending' => 'bg-warning text-dark',
                        'cancelled' => 'bg-danger',
                        default => 'bg-secondary'
                    };
                    ?>
                    <span class="badge <?php echo $statusClass; ?> fs-6"><?php echo ucfirst($booking['status']); ?></span>
                </div>
                
                <div class="row mt-4">
                    <div class="col-6">
                        <p class="mb-1 opacity-75">Berangkat</p>
                        <!-- PERUBAHAN: departure_datetime->departure_time -->
                        <h4 class="mb-0"><?php echo formatDate($booking['departure_time'], 'H:i'); ?></h4>
                        <p class="mb-0"><?php echo formatDate($booking['departure_time'], 'd M Y'); ?></p>
                    </div>
                    <div class="col-6 text-end">
                        <p class="mb-1 opacity-75">Tiba</p>
                        <!-- PERUBAHAN: arrival_datetime->arrival_time -->
                        <h4 class="mb-0"><?php echo formatDate($booking['arrival_time'], 'H:i'); ?></h4>
                        <p class="mb-0"><?php echo formatDate($booking['arrival_time'], 'd M Y'); ?></p>
                    </div>
                </div>
                
                <hr style="border-color: rgba(255,255,255,0.3);">
                
                <div class="row">
                    <div class="col-6">
                        <p class="mb-1 opacity-75"><i class="fa-solid fa-bus me-1"></i> Armada</p>
                        <!-- PERUBAHAN: bus_name->model, plate_number->bus_number -->
                        <p class="mb-0"><strong><?php echo htmlspecialchars($booking['model'] ?: $booking['bus_number']); ?></strong></p>
                        <small><?php echo getBusTypeLabel($booking['bus_type']); ?></small>
                    </div>
                    <div class="col-6 text-end">
                        <p class="mb-1 opacity-75"><i class="fa-solid fa-clock me-1"></i> Durasi</p>
                        <p class="mb-0"><strong><?php echo $booking['estimated_duration'] ?: '-'; ?></strong></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Passengers -->
        <?php if (!empty($passengers)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa-solid fa-users me-2"></i>Data Penumpang</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr><th>#</th><th>Nama</th><th>No. Identitas</th><th>Telepon</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($passengers as $i => $p): ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><?php echo htmlspecialchars($p['name']); ?></td>
                            <td><?php echo $p['identity_number'] ?: '-'; ?></td>
                            <td><?php echo $p['phone'] ?: '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Seats -->
        <?php if (!empty($seats)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa-solid fa-chair me-2"></i>Kursi</h5>
            </div>
            <div class="card-body">
                <?php foreach ($seats as $seat): ?>
                <span class="badge bg-primary me-1 mb-1 fs-6"><?php echo htmlspecialchars($seat['seat_number']); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Facilities -->
        <?php if ($booking['facilities']): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa-solid fa-star me-2"></i>Fasilitas</h5>
            </div>
            <div class="card-body">
                <?php 
                $facilities = explode(',', $booking['facilities']);
                foreach ($facilities as $f): ?>
                <span class="badge bg-light text-dark me-1 mb-1"><?php echo trim($f); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-4">
        <!-- Payment Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa-solid fa-credit-card me-2"></i>Pembayaran</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <!-- PERUBAHAN: total_price -> total_amount -->
                    <h3 class="text-primary"><?php echo formatRupiah($booking['total_amount']); ?></h3>
                </div>
                
                <?php if ($payment): ?>
                <!-- PERUBAHAN: payment_method -> method -->
                <p><strong>Metode:</strong> <?php echo ucfirst($payment['method'] ?? 'Transfer'); ?></p>
                <p><strong>Status:</strong> <?php echo getStatusBadge($payment['payment_status']); ?></p>
                
                <?php if ($payment['payment_proof']): ?>
                <p><strong>Bukti Pembayaran:</strong></p>
                <!-- PERUBAHAN: path image -->
                <img src="../admin/<?php echo htmlspecialchars($payment['payment_proof']); ?>" class="img-fluid rounded mb-3" alt="Bukti bayar">
                <?php endif; ?>
                
                <?php if ($payment['payment_status'] === 'pending' && !$payment['payment_proof']): ?>
                <hr>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload_proof">
                    <div class="mb-3">
                        <label class="form-label">Upload Bukti Pembayaran</label>
                        <input type="file" name="payment_proof" class="form-control" accept="image/*" required>
                        <small class="text-muted">Format: JPG, PNG. Max: 2MB</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa-solid fa-upload me-1"></i> Upload
                    </button>
                </form>
                <?php endif; ?>
                
                <?php if ($payment['payment_status'] === 'success'): ?>
                <div class="alert alert-success mb-0">
                    <i class="fa-solid fa-check-circle me-1"></i> Pembayaran sudah dikonfirmasi
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <p class="text-muted">Belum ada data pembayaran</p>
                <?php endif; ?>
            </div>
        </div>
        
        <a href="bookings.php" class="btn btn-secondary w-100">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
