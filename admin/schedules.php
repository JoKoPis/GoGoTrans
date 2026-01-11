<?php
/**
 * ============================================
 * ADMIN - KELOLA JADWAL
 * ============================================
 * CRUD Operations for schedules dengan tb_schedule
 */

$pageTitle = 'Kelola Jadwal';
$pageSubtitle = 'Manage bus schedules';

require_once 'templates/functions.php';
require_once 'templates/header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$pdo = getDB();

// ============================================
// PROSES FORM SUBMISSIONS
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    
    // CREATE atau UPDATE JADWAL
    if ($postAction === 'create' || $postAction === 'update') {
        $bus_id = (int)$_POST['bus_id'];
        $route_id = (int)$_POST['route_id'];
        // PERUBAHAN: departure_datetime -> departure_time, arrival_datetime -> arrival_time
        $departure_time = $_POST['departure_date'] . ' ' . $_POST['departure_time'];
        $arrival_time = $_POST['arrival_date'] . ' ' . $_POST['arrival_time'];
        // PERUBAHAN: base_price -> price
        $price = (float)str_replace(['.', ','], ['', '.'], $_POST['base_price']);
        $status = $_POST['status'];
        
        try {
            if ($postAction === 'create') {
                // CREATE - Insert jadwal baru ke tb_schedule
                $sql = "INSERT INTO tb_schedule 
                        (route_id, bus_id, departure_time, arrival_time, price, status) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$route_id, $bus_id, $departure_time, $arrival_time, $price, $status]);
                
                setFlash('success', 'Jadwal berhasil ditambahkan!');
            } else {
                // UPDATE - Update jadwal existing
                $schedule_id = $_POST['id'];
                $sql = "UPDATE tb_schedule 
                        SET route_id = ?, bus_id = ?, departure_time = ?, arrival_time = ?, price = ?, status = ? 
                        WHERE schedule_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$route_id, $bus_id, $departure_time, $arrival_time, $price, $status, $schedule_id]);
                
                setFlash('success', 'Jadwal berhasil diupdate!');
            }
        } catch (PDOException $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
        
        header('Location: schedules.php');
        exit;
    }
    
    // DELETE JADWAL
    if ($postAction === 'delete') {
        try {
            $sql = "DELETE FROM tb_schedule WHERE schedule_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_POST['id']]);
            setFlash('success', 'Jadwal berhasil dihapus!');
        } catch (PDOException $e) {
            setFlash('error', 'Error: Jadwal tidak dapat dihapus (mungkin masih ada booking)');
        }
        header('Location: schedules.php');
        exit;
    }
}

// ============================================
// AMBIL DATA JADWAL UNTUK EDIT
// ============================================
$schedule = null;
if ($action === 'edit' && $id) {
    $sql = "SELECT * FROM tb_schedule WHERE schedule_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $schedule = $stmt->fetch();
    
    if (!$schedule) {
        header('Location: schedules.php');
        exit;
    }
}

// ============================================
// AMBIL DAFTAR JADWAL DENGAN JOIN
// ============================================
// PERUBAHAN: schedules->tb_schedule, buses->tb_buss, routes->tb_route
// PERUBAHAN: bus_name->model, plate_number->bus_number
// PERUBAHAN: origin_city->departure_city, destination_city->arrival_city
$stmt = $pdo->query("
    SELECT s.*, b.model, b.bus_number, b.bus_type, 
           r.departure_city, r.arrival_city
    FROM tb_schedule s
    LEFT JOIN tb_buss b ON s.bus_id = b.bus_id
    LEFT JOIN tb_route r ON s.route_id = r.route_id
    ORDER BY s.departure_time DESC
");
$schedules = $stmt->fetchAll();

// Ambil buses dan routes untuk dropdown
$sql = "SELECT bus_id, bus_number, model, bus_type FROM tb_buss WHERE status = 'active'";
$buses = $pdo->query($sql)->fetchAll();

$sql = "SELECT route_id, departure_city, arrival_city FROM tb_route WHERE is_active = 1";
$routes = $pdo->query($sql)->fetchAll();
?>

<?php showFlash(); ?>

<?php if ($action === 'list'): ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2><i class="fa-solid fa-calendar me-2"></i>Daftar Jadwal</h2>
        <a href="?action=add" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Tambah Jadwal
        </a>
    </div>
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Armada</th>
                    <th>Rute</th>
                    <th>Berangkat</th>
                    <th>Tiba</th>
                    <th>Harga</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($schedules)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">Belum ada jadwal</td>
                </tr>
                <?php else: ?>
                <?php foreach ($schedules as $s): ?>
                <tr>
                    <td>
                        <!-- PERUBAHAN: bus_name->model, plate_number->bus_number -->
                        <strong><?php echo htmlspecialchars($s['model'] ?: $s['bus_number']); ?></strong>
                        <br><small class="text-muted"><?php echo getBusTypeLabel($s['bus_type']); ?></small>
                    </td>
                    <td>
                        <!-- PERUBAHAN: origin_city->departure_city, destination_city->arrival_city -->
                        <?php echo htmlspecialchars($s['departure_city']); ?>
                        <i class="fa-solid fa-arrow-right text-muted mx-1"></i>
                        <?php echo htmlspecialchars($s['arrival_city']); ?>
                    </td>
                    <!-- PERUBAHAN: departure_datetime->departure_time -->
                    <td><?php echo formatDate($s['departure_time']); ?></td>
                    <!-- PERUBAHAN: arrival_datetime->arrival_time -->
                    <td><?php echo formatDate($s['arrival_time']); ?></td>
                    <!-- PERUBAHAN: base_price->price -->
                    <td><strong><?php echo formatRupiah($s['price']); ?></strong></td>
                    <td><?php echo getStatusBadge($s['status']); ?></td>
                    <td>
                        <!-- PERUBAHAN: id->schedule_id -->
                        <a href="?action=edit&id=<?php echo $s['schedule_id']; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="confirmDelete(<?php echo $s['schedule_id']; ?>)">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Yakin ingin menghapus jadwal ini?</p>
            </div>
            <div class="modal-footer">
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteId">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    document.getElementById('deleteId').value = id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php else: ?>
<!-- ADD/EDIT FORM -->
<div class="card">
    <div class="card-header">
        <h2>
            <i class="fa-solid fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?> me-2"></i>
            <?php echo $action === 'add' ? 'Tambah Jadwal Baru' : 'Edit Jadwal'; ?>
        </h2>
    </div>
    <div class="card-body">
        <?php if (empty($buses) || empty($routes)): ?>
        <div class="alert alert-warning">
            <i class="fa-solid fa-exclamation-triangle me-2"></i>
            Pastikan sudah ada armada dan rute yang aktif sebelum menambah jadwal.
        </div>
        <?php else: ?>
        <form method="POST">
            <input type="hidden" name="action" value="<?php echo $action === 'add' ? 'create' : 'update'; ?>">
            <?php if ($schedule): ?>
            <!-- PERUBAHAN: id->schedule_id -->
            <input type="hidden" name="id" value="<?php echo $schedule['schedule_id']; ?>">
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Armada <span class="text-danger">*</span></label>
                        <select name="bus_id" class="form-select" required>
                            <option value="">-- Pilih Armada --</option>
                            <?php foreach ($buses as $b): ?>
                            <!-- PERUBAHAN: id->bus_id, bus_name->model, plate_number->bus_number -->
                            <option value="<?php echo $b['bus_id']; ?>" 
                                    <?php echo ($schedule['bus_id'] ?? '') == $b['bus_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($b['model'] ?: $b['bus_number']); ?> 
                                (<?php echo getBusTypeLabel($b['bus_type']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Rute <span class="text-danger">*</span></label>
                        <select name="route_id" class="form-select" required>
                            <option value="">-- Pilih Rute --</option>
                            <?php foreach ($routes as $r): ?>
                            <!-- PERUBAHAN: id->route_id, origin_city->departure_city, destination_city->arrival_city -->
                            <option value="<?php echo $r['route_id']; ?>" 
                                    <?php echo ($schedule['route_id'] ?? '') == $r['route_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($r['departure_city'] . ' → ' . $r['arrival_city']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Berangkat <span class="text-danger">*</span></label>
                        <!-- PERUBAHAN: departure_datetime->departure_time -->
                        <input type="date" name="departure_date" class="form-control" required
                               value="<?php echo $schedule ? date('Y-m-d', strtotime($schedule['departure_time'])) : ''; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Jam Berangkat <span class="text-danger">*</span></label>
                        <input type="time" name="departure_time" class="form-control" required
                               value="<?php echo $schedule ? date('H:i', strtotime($schedule['departure_time'])) : ''; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Tiba <span class="text-danger">*</span></label>
                        <!-- PERUBAHAN: arrival_datetime->arrival_time -->
                        <input type="date" name="arrival_date" class="form-control" required
                               value="<?php echo $schedule ? date('Y-m-d', strtotime($schedule['arrival_time'])) : ''; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Jam Tiba <span class="text-danger">*</span></label>
                        <input type="time" name="arrival_time" class="form-control" required
                               value="<?php echo $schedule ? date('H:i', strtotime($schedule['arrival_time'])) : ''; ?>">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Harga <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <!-- PERUBAHAN: base_price->price -->
                            <input type="text" name="base_price" class="form-control" required
                                   value="<?php echo $schedule ? number_format($schedule['price'], 0, ',', '.') : ''; ?>"
                                   placeholder="500.000">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="available" <?php echo ($schedule['status'] ?? 'available') === 'available' ? 'selected' : ''; ?>>Available</option>
                            <option value="cancelled" <?php echo ($schedule['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            <option value="finished" <?php echo ($schedule['status'] ?? '') === 'finished' ? 'selected' : ''; ?>>Finished</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save me-1"></i> Simpan
                </button>
                <a href="schedules.php" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once 'templates/footer.php'; ?>
