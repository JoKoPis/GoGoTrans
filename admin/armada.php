<?php

$pageTitle = 'Kelola Armada';
$pageSubtitle = 'Manage bus fleet / armada';

require_once 'templates/functions.php';
require_once 'templates/header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$pdo = getDB();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    
    if ($postAction === 'create' || $postAction === 'update') {
        
        $bus_number = trim($_POST['plate_number']);
        $model = trim($_POST['bus_name']);
        $bus_type = $_POST['bus_type'];
        $description = trim($_POST['description']);
        $facilities = trim($_POST['facilities']);
        $seat_capacity = (int)$_POST['total_seats'];
        $status = $_POST['status'];
        
        $image_url = null;
        $image_banner = null;
        
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $result = uploadFile($_FILES['image'], __DIR__ . '/uploads');
            if ($result['success']) {
                $image_url = 'uploads/' . $result['filename'];
            }
        }
        
    
        if (isset($_FILES['image_banner']) && $_FILES['image_banner']['error'] === UPLOAD_ERR_OK) {
            $result = uploadFile($_FILES['image_banner'], __DIR__ . '/uploads');
            if ($result['success']) {
                $image_banner = 'uploads/' . $result['filename'];
            }
        }
        
        try {
            if ($postAction === 'create') {
                // CREATE - Insert armada baru ke tb_buss
                $sql = "INSERT INTO tb_buss 
                        (bus_number, model, bus_type, description, seat_capacity, facilities, image_url, image_banner, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$bus_number, $model, $bus_type, $description, $seat_capacity, $facilities, $image_url, $image_banner, $status]);
                
                // Get the ID of the new bus
                $newBusId = $pdo->lastInsertId();
                
                // Generate seats automatically
                $seatSql = "INSERT INTO tb_seat (bus_id, seat_number) VALUES (?, ?)";
                $seatStmt = $pdo->prepare($seatSql);
                
                for ($i = 1; $i <= $seat_capacity; $i++) {
                    $seatStmt->execute([$newBusId, (string)$i]);
                }
                
                setFlash('success', 'Armada berhasil ditambahkan dan kursi digenerate otomatis!');
            } else {
                
                $bus_id = $_POST['id'];
                
    
                if ($image_url || $image_banner) {
                    if ($image_url && $image_banner) {
                        $sql = "UPDATE tb_buss 
                                SET bus_number = ?, model = ?, bus_type = ?, description = ?, 
                                    seat_capacity = ?, facilities = ?, image_url = ?, image_banner = ?, status = ? 
                                WHERE bus_id = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$bus_number, $model, $bus_type, $description, $seat_capacity, $facilities, $image_url, $image_banner, $status, $bus_id]);
                    } else if ($image_url) {
                        $sql = "UPDATE tb_buss 
                                SET bus_number = ?, model = ?, bus_type = ?, description = ?, 
                                    seat_capacity = ?, facilities = ?, image_url = ?, status = ? 
                                WHERE bus_id = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$bus_number, $model, $bus_type, $description, $seat_capacity, $facilities, $image_url, $status, $bus_id]);
                    } else {
                        $sql = "UPDATE tb_buss 
                                SET bus_number = ?, model = ?, bus_type = ?, description = ?, 
                                    seat_capacity = ?, facilities = ?, image_banner = ?, status = ? 
                                WHERE bus_id = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$bus_number, $model, $bus_type, $description, $seat_capacity, $facilities, $image_banner, $status, $bus_id]);
                    }
                } else {
                    // Update tanpa gambar
                    $sql = "UPDATE tb_buss 
                            SET bus_number = ?, model = ?, bus_type = ?, description = ?, 
                                seat_capacity = ?, facilities = ?, status = ? 
                            WHERE bus_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$bus_number, $model, $bus_type, $description, $seat_capacity, $facilities, $status, $bus_id]);
                }
                
                setFlash('success', 'Armada berhasil diupdate!');
            }
        } catch (PDOException $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
        
        header('Location: armada.php');
        exit;
    }
    
 
    if ($postAction === 'delete') {
        try {
            $sql = "DELETE FROM tb_buss WHERE bus_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_POST['id']]);
            setFlash('success', 'Armada berhasil dihapus!');
        } catch (PDOException $e) {
            setFlash('error', 'Error: Armada tidak dapat dihapus (mungkin masih terhubung dengan jadwal)');
        }
        header('Location: armada.php');
        exit;
    }
}

// ============================================
// AMBIL DATA ARMADA UNTUK EDIT
// ============================================
$bus = null;
if ($action === 'edit' && $id) {
    $sql = "SELECT * FROM tb_buss WHERE bus_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $bus = $stmt->fetch();
    
    if (!$bus) {
        header('Location: armada.php');
        exit;
    }
}

// ============================================
// AMBIL DAFTAR SEMUA ARMADA
// ============================================
$sql = "SELECT * FROM tb_buss ORDER BY bus_id DESC";
$buses = $pdo->query($sql)->fetchAll();
?>

<?php showFlash(); ?>

<?php if ($action === 'list'): ?>
<!-- LIST VIEW -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2><i class="fa-solid fa-bus me-2"></i>Daftar Armada</h2>
        <a href="?action=add" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Tambah Armada
        </a>
    </div>
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Gambar</th>
                    <th>Plat Nomor</th>
                    <th>Nama</th>
                    <th>Tipe</th>
                    <th>Kursi</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($buses)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">Belum ada data armada</td>
                </tr>
                <?php else: ?>
                <?php foreach ($buses as $b): ?>
                <tr>
                    <td>
                        <?php if ($b['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($b['image_url']); ?>" alt="" style="width:60px;height:40px;object-fit:cover;border-radius:4px;">
                        <?php else: ?>
                        <div style="width:60px;height:40px;background:#eee;border-radius:4px;display:flex;align-items:center;justify-content:center;">
                            <i class="fa-solid fa-bus text-muted"></i>
                        </div>
                        <?php endif; ?>
                    </td>
                    
                    <td><strong><?php echo htmlspecialchars($b['bus_number']); ?></strong></td>
                   
                    <td><?php echo htmlspecialchars($b['model']); ?></td>
                    <td><?php echo getBusTypeLabel($b['bus_type']); ?></td>
                    
                    <td><?php echo $b['seat_capacity']; ?> kursi</td>
                    <td><?php echo getStatusBadge($b['status']); ?></td>
                    <td>
                        
                        <a href="?action=edit&id=<?php echo $b['bus_id']; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="confirmDelete(<?php echo $b['bus_id']; ?>, '<?php echo htmlspecialchars($b['model']); ?>')">
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
                <p>Yakin ingin menghapus armada <strong id="deleteName"></strong>?</p>
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
            <?php echo $action === 'add' ? 'Tambah Armada Baru' : 'Edit Armada'; ?>
        </h2>
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?php echo $action === 'add' ? 'create' : 'update'; ?>">
            <?php if ($bus): ?>
            <!-- PERUBAHAN: id -> bus_id -->
            <input type="hidden" name="id" value="<?php echo $bus['bus_id']; ?>">
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Plat Nomor <span class="text-danger">*</span></label>
                        <!-- PERUBAHAN: plate_number -> bus_number -->
                        <input type="text" name="plate_number" class="form-control" required
                               value="<?php echo htmlspecialchars($bus['bus_number'] ?? ''); ?>"
                               placeholder="DK 1234 AB">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nama Armada</label>
                        <!-- PERUBAHAN: bus_name -> model -->
                        <input type="text" name="bus_name" class="form-control"
                               value="<?php echo htmlspecialchars($bus['model'] ?? ''); ?>"
                               placeholder="Bus Pariwisata Premium">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Tipe Armada <span class="text-danger">*</span></label>
                        <select name="bus_type" class="form-select" required>
                            <option value="big_bus" <?php echo ($bus['bus_type'] ?? '') === 'big_bus' ? 'selected' : ''; ?>>Bus Pariwisata</option>
                            <option value="mini_bus" <?php echo ($bus['bus_type'] ?? '') === 'mini_bus' ? 'selected' : ''; ?>>Mini Bus</option>
                            <option value="hiace" <?php echo ($bus['bus_type'] ?? '') === 'hiace' ? 'selected' : ''; ?>>Toyota Hiace</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Jumlah Kursi <span class="text-danger">*</span></label>
                        <!-- PERUBAHAN: total_seats -> seat_capacity -->
                        <input type="number" name="total_seats" class="form-control" required min="1"
                               value="<?php echo $bus['seat_capacity'] ?? '40'; ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?php echo ($bus['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="maintenance" <?php echo ($bus['status'] ?? '') === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                            <option value="inactive" <?php echo ($bus['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Fasilitas</label>
                <input type="text" name="facilities" class="form-control"
                       value="<?php echo htmlspecialchars($bus['facilities'] ?? ''); ?>"
                       placeholder="AC, WiFi, TV LED, Toilet, USB Charger">
                <small class="text-muted">Pisahkan dengan koma</small>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="3"
                          placeholder="Deskripsi lengkap armada..."><?php echo htmlspecialchars($bus['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Gambar Armada</label>
                <?php if ($bus && $bus['image_url']): ?>
                <div class="mb-2">
                    <img src="<?php echo htmlspecialchars($bus['image_url']); ?>" alt="" style="max-width:200px;border-radius:8px;">
                </div>
                <?php endif; ?>
                <input type="file" name="image" class="form-control" accept="image/*">
                <small class="text-muted">Gambar untuk halaman Armada. Format: JPG, PNG, WebP. Max 2MB</small>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Gambar Banner (Homepage)</label>
                <?php if ($bus && $bus['image_banner']): ?>
                <div class="mb-2">
                    <img src="<?php echo htmlspecialchars($bus['image_banner']); ?>" alt="" style="max-width:300px;border-radius:8px;">
                    <span class="badge bg-info ms-2">16:9 Aspect Ratio</span>
                </div>
                <?php endif; ?>
                <input type="file" name="image_banner" class="form-control" accept="image/*">
                <small class="text-muted">Gambar untuk slider di Homepage. Rekomendasi: 800x450px (16:9 aspect ratio)</small>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save me-1"></i> Simpan
                </button>
                <a href="armada.php" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require_once 'templates/footer.php'; ?>
