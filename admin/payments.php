<?php
/**
 * ============================================
 * ADMIN - KELOLA PEMBAYARAN
 * ============================================
 * View and validate payments dengan tb_payment_method
 */

$pageTitle = 'Kelola Pembayaran';
$pageSubtitle = 'Validate customer payments';

require_once 'templates/functions.php';
require_once 'templates/header.php';

$pdo = getDB();

// ============================================
// PROSES ACTIONS
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    
    if ($postAction === 'approve' || $postAction === 'reject') {
        try {
            $newStatus = $postAction === 'approve' ? 'success' : 'failed';
            $paidAt = $postAction === 'approve' ? date('Y-m-d H:i:s') : null;
            $payment_id = $_POST['id'];
            
            // Update status pembayaran
            // PERUBAHAN: payments -> tb_payment_method
            $sql = "UPDATE tb_payment_method 
                    SET payment_status = ?, paid_at = ? 
                    WHERE payment_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$newStatus, $paidAt, $payment_id]);
            
            // Update booking status jika approved
            if ($postAction === 'approve') {
                // Ambil booking_id dari payment
                $stmt = $pdo->prepare("SELECT booking_id FROM tb_payment_method WHERE payment_id = ?");
                $stmt->execute([$payment_id]);
                $payment = $stmt->fetch();
                
                if ($payment) {
                    // Update status booking jadi 'paid'
                    // PERUBAHAN: bookings -> tb_booking
                    $sql = "UPDATE tb_booking SET status = 'paid' WHERE booking_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$payment['booking_id']]);
                }
            }
            
            $msg = $postAction === 'approve' ? 'Pembayaran berhasil dikonfirmasi!' : 'Pembayaran ditolak!';
            setFlash('success', $msg);
        } catch (PDOException $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
        header('Location: payments.php');
        exit;
    }
}

// ============================================
// AMBIL DAFTAR PEMBAYARAN
// ============================================
$statusFilter = $_GET['status'] ?? '';
$where = $statusFilter ? "WHERE p.payment_status = '$statusFilter'" : '';

// PERUBAHAN: payments->tb_payment_method, bookings->tb_booking, users->tb_user
// PERUBAHAN: id->payment_id, payment_method->method, total_price->total_amount
$sql = "SELECT p.*, bk.booking_code, bk.total_amount as booking_total,
               u.name as customer_name, u.email as customer_email
        FROM tb_payment_method p
        LEFT JOIN tb_booking bk ON p.booking_id = bk.booking_id
        LEFT JOIN tb_user u ON bk.user_id = u.user_id
        $where
        ORDER BY p.payment_id DESC";

$payments = $pdo->query($sql)->fetchAll();
?>

<?php showFlash(); ?>

<div class="card">
    <div class="card-header">
        <h2><i class="fa-solid fa-credit-card me-2"></i>Daftar Pembayaran</h2>
        <div class="btn-group mt-2" role="group">
            <a href="payments.php" class="btn btn-sm <?php echo !$statusFilter ? 'btn-primary' : 'btn-outline-primary'; ?>">Semua</a>
            <a href="?status=pending" class="btn btn-sm <?php echo $statusFilter === 'pending' ? 'btn-warning' : 'btn-outline-warning'; ?>">Pending</a>
            <a href="?status=success" class="btn btn-sm <?php echo $statusFilter === 'success' ? 'btn-success' : 'btn-outline-success'; ?>">Success</a>
            <a href="?status=failed" class="btn btn-sm <?php echo $statusFilter === 'failed' ? 'btn-danger' : 'btn-outline-danger'; ?>">Failed</a>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Booking</th>
                    <th>Customer</th>
                    <th>Metode</th>
                    <th>Jumlah</th>
                    <th>Bukti</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">Belum ada pembayaran</td>
                </tr>
                <?php else: ?>
                <?php foreach ($payments as $i => $p): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><strong><?php echo htmlspecialchars($p['booking_code'] ?? ''); ?></strong></td>
                    <td>
                        <?php echo htmlspecialchars($p['customer_name'] ?? ''); ?>
                        <br><small class="text-muted"><?php echo htmlspecialchars($p['customer_email'] ?? ''); ?></small>
                    </td>
                    <td>
                        <!-- PERUBAHAN: payment_method -> method -->
                        <i class="fa-solid fa-<?php echo ($p['method'] ?? '') === 'ewallet' ? 'wallet' : (($p['method'] ?? '') === 'transfer' ? 'building-columns' : 'money-bill'); ?> me-1"></i>
                        <?php echo ucfirst($p['method'] ?? 'Transfer'); ?>
                    </td>
                    <td><strong><?php echo formatRupiah($p['amount']); ?></strong></td>
                    <td>
                        <?php if ($p['payment_proof']): ?>
                        <a href="<?php echo htmlspecialchars($p['payment_proof']); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                            <i class="fa-solid fa-image"></i> Lihat
                        </a>
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo getStatusBadge($p['payment_status']); ?></td>
                    <td>
                        <?php if ($p['payment_status'] === 'pending'): ?>
                        <form method="POST" style="display:inline;">
                            <!-- PERUBAHAN: id -> payment_id -->
                            <input type="hidden" name="id" value="<?php echo $p['payment_id']; ?>">
                            <button type="submit" name="action" value="approve" class="btn btn-sm btn-success" 
                                    onclick="return confirm('Konfirmasi pembayaran ini?')">
                                <i class="fa-solid fa-check"></i>
                            </button>
                            <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Tolak pembayaran ini?')">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </form>
                        <?php elseif ($p['paid_at']): ?>
                        <small class="text-muted"><?php echo formatDate($p['paid_at']); ?></small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
