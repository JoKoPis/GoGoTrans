<?php
/**
 * ============================================
 * ADMIN - KELOLA USERS
 * ============================================
 * CRUD Operations for users dengan tb_user dan tb_role
 */

$pageTitle = 'Kelola Users';
$pageSubtitle = 'Manage system users';

require_once 'templates/functions.php';
require_once 'templates/header.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Initialize Database Connection
$pdo = getDB();

// ============================================
// PROSES FORM SUBMISSIONS
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    // $pdo initialization removed from here
    
    // CREATE atau UPDATE USER
    if ($postAction === 'create' || $postAction === 'update') {
        $username = trim($_POST['username']) ?: null;
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $role_id = (int)$_POST['role_id']; // 1=admin, 2=customer
        
        try {
            if ($postAction === 'create') {
                // CREATE - Insert user baru
                if (empty($_POST['password'])) {
                    throw new Exception('Password wajib diisi untuk user baru!');
                }
                
                $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                
                // PERUBAHAN: INSERT ke tb_user dengan role_id
                $sql = "INSERT INTO tb_user (role_id, username, name, email, phone, address, pass, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$role_id, $username, $name, $email, $phone, $address, $password_hash]);
                
                setFlash('success', 'User berhasil ditambahkan!');
            } else {
                // UPDATE - Update user existing
                $user_id = $_POST['id'];
                
                if (!empty($_POST['password'])) {
                    // Jika password diisi, update dengan password baru
                    $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $sql = "UPDATE tb_user 
                            SET username = ?, name = ?, email = ?, phone = ?, address = ?, role_id = ?, pass = ? 
                            WHERE user_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$username, $name, $email, $phone, $address, $role_id, $password_hash, $user_id]);
                } else {
                    // Jika password kosong, update tanpa password
                    $sql = "UPDATE tb_user 
                            SET username = ?, name = ?, email = ?, phone = ?, address = ?, role_id = ? 
                            WHERE user_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$username, $name, $email, $phone, $address, $role_id, $user_id]);
                }
                
                setFlash('success', 'User berhasil diupdate!');
            }
        } catch (Exception $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
        
        header('Location: users.php');
        exit;
    }
    
    // DELETE USER
    if ($postAction === 'delete') {
        try {
            $sql = "DELETE FROM tb_user WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_POST['id']]);
            setFlash('success', 'User berhasil dihapus!');
        } catch (PDOException $e) {
            setFlash('error', 'Error: User tidak dapat dihapus (mungkin masih memiliki booking)');
        }
        header('Location: users.php');
        exit;
    }
}

// ============================================
// AMBIL DATA USER UNTUK EDIT
// ============================================
$user = null;
if ($action === 'edit' && $id) {
    // PERUBAHAN: SELECT dari tb_user dengan JOIN tb_role
    $sql = "SELECT u.*, r.role_name 
            FROM tb_user u 
            LEFT JOIN tb_role r ON u.role_id = r.role_id
            WHERE u.user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: users.php');
        exit;
    }
}

// ============================================
// AMBIL DAFTAR USERS
// ============================================
$roleFilter = $_GET['role'] ?? '';

// PERUBAHAN: SELECT dari tb_user dengan JOIN tb_role
if ($roleFilter) {
    // Filter berdasarkan role_name
    $sql = "SELECT u.*, r.role_name 
            FROM tb_user u 
            LEFT JOIN tb_role r ON u.role_id = r.role_id
            WHERE r.role_name = ?
            ORDER BY u.user_id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$roleFilter]);
    $users = $stmt->fetchAll();
} else {
    // Ambil semua users
    $sql = "SELECT u.*, r.role_name 
            FROM tb_user u 
            LEFT JOIN tb_role r ON u.role_id = r.role_id
            ORDER BY u.user_id DESC";
    $users = $pdo->query($sql)->fetchAll();
}
?>

<?php showFlash(); ?>

<?php if ($action === 'list'): ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fa-solid fa-users me-2"></i>Daftar Users</h2>
            <div class="btn-group mt-2" role="group">
                <a href="users.php" class="btn btn-sm <?php echo !$roleFilter ? 'btn-primary' : 'btn-outline-primary'; ?>">Semua</a>
                <a href="?role=admin" class="btn btn-sm <?php echo $roleFilter === 'admin' ? 'btn-primary' : 'btn-outline-primary'; ?>">Admin</a>
                <!-- PERUBAHAN: Hapus operator, hanya admin dan customer -->
                <a href="?role=customer" class="btn btn-sm <?php echo $roleFilter === 'customer' ? 'btn-primary' : 'btn-outline-primary'; ?>">Customer</a>
            </div>
        </div>
        <a href="?action=add" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Tambah User
        </a>
    </div>
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Belum ada data user</td>
                </tr>
                <?php else: ?>
                <?php foreach ($users as $i => $u): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($u['name']); ?></strong>
                        <?php if ($u['username']): ?>
                        <br><small class="text-muted">@<?php echo htmlspecialchars($u['username']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><?php echo $u['phone'] ?: '-'; ?></td>
                    <td>
                        <!-- PERUBAHAN: role_name dari JOIN -->
                        <span class="badge bg-<?php echo $u['role_name'] === 'admin' ? 'danger' : 'info'; ?>">
                            <?php echo ucfirst($u['role_name']); ?>
                        </span>
                    </td>
                    <td>
                        <!-- PERUBAHAN: id -> user_id -->
                        <a href="?action=edit&id=<?php echo $u['user_id']; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-edit"></i>
                        </a>
                        <?php if ($u['user_id'] != $_SESSION['user_id']): ?>
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="confirmDelete(<?php echo $u['user_id']; ?>, '<?php echo htmlspecialchars($u['name']); ?>')">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Delete -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Yakin ingin menghapus user <strong id="deleteName"></strong>?</p>
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
<!-- FORM ADD/EDIT -->
<div class="card">
    <div class="card-header">
        <h2>
            <i class="fa-solid fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?> me-2"></i>
            <?php echo $action === 'add' ? 'Tambah User Baru' : 'Edit User'; ?>
        </h2>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" value="<?php echo $action === 'add' ? 'create' : 'update'; ?>">
            <?php if ($user): ?>
            <!-- PERUBAHAN: id -> user_id -->
            <input type="hidden" name="id" value="<?php echo $user['user_id']; ?>">
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required
                               value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control"
                               value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>"
                               placeholder="">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required
                               value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">No. Telepon</label>
                        <input type="text" name="phone" class="form-control"
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Password <?php echo $action === 'add' ? '<span class="text-danger">*</span>' : ''; ?></label>
                        <input type="password" name="password" class="form-control" 
                               <?php echo $action === 'add' ? 'required' : ''; ?>>
                        <?php if ($action === 'edit'): ?>
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role_id" class="form-select" required>
                            <!-- PERUBAHAN: Pakai role_id (1=admin, 2=customer) -->
                            <option value="2" <?php echo ($user['role_id'] ?? 2) == 2 ? 'selected' : ''; ?>>Customer</option>
                            <option value="1" <?php echo ($user['role_id'] ?? '') == 1 ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save me-1"></i> Simpan
                </button>
                <a href="users.php" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require_once 'templates/footer.php'; ?>
