<?php

$pageTitle = 'Kelola Rute';
$pageSubtitle = 'Manage travel routes';

require_once 'templates/functions.php';
require_once 'templates/header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    // $pdo initialization removed from here
    
    // CREATE atau UPDATE RUTE
    if ($postAction === 'create' || $postAction === 'update') {
        // PERUBAHAN: origin_city->departure_city, destination_city->arrival_city
        $departure_city = trim($_POST['origin_city']);
        $arrival_city = trim($_POST['destination_city']);
        $distance_km = (int)$_POST['distance_km'];
        $estimated_duration = trim($_POST['estimated_duration']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        try {
            if ($postAction === 'create') {
                // CREATE - Insert rute baru ke tb_route
                $sql = "INSERT INTO tb_route 
                        (departure_city, arrival_city, distance_km, estimated_duration, is_active) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$departure_city, $arrival_city, $distance_km, $estimated_duration, $is_active]);
                
                setFlash('success', 'Rute berhasil ditambahkan!');
            } else {
                // UPDATE - Update rute existing
                $route_id = $_POST['id'];
                $sql = "UPDATE tb_route 
                        SET departure_city = ?, arrival_city = ?, distance_km = ?, estimated_duration = ?, is_active = ? 
                        WHERE route_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$departure_city, $arrival_city, $distance_km, $estimated_duration, $is_active, $route_id]);
                
                setFlash('success', 'Rute berhasil diupdate!');
            }
        } catch (PDOException $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
        
        header('Location: routes.php');
        exit;
    }
    
    // DELETE RUTE
    if ($postAction === 'delete') {
        try {
            $sql = "DELETE FROM tb_route WHERE route_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_POST['id']]);
            setFlash('success', 'Rute berhasil dihapus!');
        } catch (PDOException $e) {
            setFlash('error', 'Error: Rute tidak dapat dihapus (mungkin masih digunakan di jadwal)');
        }
        header('Location: routes.php');
        exit;
    }
}

// ============================================
// AMBIL DATA RUTE UNTUK EDIT
// ============================================
$route = null;
if ($action === 'edit' && $id) {
    $sql = "SELECT * FROM tb_route WHERE route_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $route = $stmt->fetch();
    
    if (!$route) {
        header('Location: routes.php');
        exit;
    }
}

// ============================================
// AMBIL DAFTAR SEMUA RUTE
// ============================================
$sql = "SELECT * FROM tb_route ORDER BY route_id DESC";
$routes = $pdo->query($sql)->fetchAll();
?>

<?php showFlash(); ?>

<?php if ($action === 'list'): ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2><i class="fa-solid fa-route me-2"></i>Daftar Rute</h2>
        <a href="?action=add" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Tambah Rute
        </a>
    </div>
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Asal</th>
                    <th>Tujuan</th>
                    <th>Jarak</th>
                    <th>Durasi</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($routes)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">Belum ada data rute</td>
                </tr>
                <?php else: ?>
                <?php foreach ($routes as $i => $r): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <!-- PERUBAHAN: origin_city -> departure_city -->
                    <td><strong><?php echo htmlspecialchars($r['departure_city']); ?></strong></td>
                    <td>
                        <i class="fa-solid fa-arrow-right text-muted me-1"></i>
                        <!-- PERUBAHAN: destination_city -> arrival_city -->
                        <?php echo htmlspecialchars($r['arrival_city']); ?>
                    </td>
                    <td><?php echo $r['distance_km'] ? $r['distance_km'] . ' km' : '-'; ?></td>
                    <td><?php echo $r['estimated_duration'] ?: '-'; ?></td>
                    <td>
                        <?php if ($r['is_active']): ?>
                        <span class="badge bg-success">Active</span>
                        <?php else: ?>
                        <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <!-- PERUBAHAN: id -> route_id -->
                        <a href="?action=edit&id=<?php echo $r['route_id']; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="confirmDelete(<?php echo $r['route_id']; ?>, '<?php echo htmlspecialchars($r['departure_city'] . ' - ' . $r['arrival_city']); ?>')">
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
                <p>Yakin ingin menghapus rute <strong id="deleteName"></strong>?</p>
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
function confirmDelete(id, name) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php else: ?>
<!-- ADD/EDIT FORM -->
<div class="card">
    <div class="card-header">
        <h2>
            <i class="fa-solid fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?> me-2"></i>
            <?php echo $action === 'add' ? 'Tambah Rute Baru' : 'Edit Rute'; ?>
        </h2>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" value="<?php echo $action === 'add' ? 'create' : 'update'; ?>">
            <?php if ($route): ?>
            <!-- PERUBAHAN: id -> route_id -->
            <input type="hidden" name="id" value="<?php echo $route['route_id']; ?>">
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Kota Asal <span class="text-danger">*</span></label>
                        <!-- PERUBAHAN: origin_city -> departure_city -->
                        <input type="text" name="origin_city" class="form-control" required
                               value="<?php echo htmlspecialchars($route['departure_city'] ?? ''); ?>"
                               placeholder="Denpasar">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Kota Tujuan <span class="text-danger">*</span></label>
                        <!-- PERUBAHAN: destination_city -> arrival_city -->
                        <input type="text" name="destination_city" class="form-control" required
                               value="<?php echo htmlspecialchars($route['arrival_city'] ?? ''); ?>"
                               placeholder="Surabaya">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Jarak (km)</label>
                        <input type="number" name="distance_km" class="form-control" min="0"
                               value="<?php echo $route['distance_km'] ?? ''; ?>"
                               placeholder="350">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Estimasi Durasi</label>
                        <input type="text" name="estimated_duration" class="form-control"
                               value="<?php echo htmlspecialchars($route['estimated_duration'] ?? ''); ?>"
                               placeholder="8 jam">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" name="is_active" class="form-check-input" id="isActive"
                                   <?php echo ($route['is_active'] ?? 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="isActive">Aktif</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save me-1"></i> Simpan
                </button>
                <a href="routes.php" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require_once 'templates/footer.php'; ?>
