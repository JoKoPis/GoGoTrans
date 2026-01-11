<?php
/**
 * User - Profile
 * View and edit user profile
 */

$pageTitle = 'Profil Saya';
require_once 'includes/header.php';

$userId = $_SESSION['user_id'];
$pdo = getDB();

// Get user data
// PERUBAHAN: users->tb_user, id->user_id
$stmt = $pdo->prepare("SELECT * FROM tb_user WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $username = trim($_POST['username']);
        
        // Check username uniqueness
        if (!empty($username) && $username !== $user['username']) {
            $check = $pdo->prepare("SELECT user_id FROM tb_user WHERE username = ? AND user_id != ?");
            $check->execute([$username, $userId]);
            if ($check->fetch()) {
                setFlash('error', 'Username sudah digunakan!');
                header('Location: profile.php');
                exit;
            }
        } else {
            $username = $user['username']; // Keep old username if empty or unchanged
        }
        
        try {
            // PERUBAHAN: users->tb_user, id->user_id
            $sql = "UPDATE tb_user 
                    SET name = ?, username = ?, phone = ?, address = ? 
                    WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $username, $phone, $address, $userId]);
            
            $_SESSION['nama'] = $name; // Update session
            setFlash('success', 'Profil berhasil diupdate!');
        } catch (PDOException $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
        
        header('Location: profile.php');
        exit;
    }
    
    if ($action === 'change_password') {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // PERUBAHAN: password_hash -> pass
        if (!password_verify($currentPassword, $user['pass'])) {
            setFlash('error', 'Password saat ini salah!');
        } elseif ($newPassword !== $confirmPassword) {
            setFlash('error', 'Konfirmasi password tidak cocok!');
        } elseif (strlen($newPassword) < 6) {
            setFlash('error', 'Password minimal 6 karakter!');
        } else {
            try {
                // PERUBAHAN: users->tb_user, password_hash->pass, id->user_id
                $sql = "UPDATE tb_user SET pass = ? WHERE user_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
                
                setFlash('success', 'Password berhasil diubah!');
            } catch (PDOException $e) {
                setFlash('error', 'Error: ' . $e->getMessage());
            }
        }
        
        header('Location: profile.php');
        exit;
    }
}

// Refresh user data
$stmt = $pdo->prepare("SELECT * FROM tb_user WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Helper untuk role label
function getRoleLabel($roleId) {
    if ($roleId == 1) return 'Admin';
    if ($roleId == 2) return 'Customer';
    return 'Unknown';
}
?>

<?php showFlash(); ?>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fa-solid fa-user-circle fa-5x text-primary"></i>
                </div>
                <h4><?php echo htmlspecialchars($user['name']); ?></h4>
                <p class="text-muted mb-1">@<?php echo htmlspecialchars($user['username'] ?? 'user'); ?></p>
                <span class="badge bg-info"><?php echo getRoleLabel($user['role_id']); ?></span>
                
                <hr>
                
                <div class="text-start">
                    <p class="mb-2">
                        <i class="fa-solid fa-envelope me-2 text-muted"></i>
                        <?php echo htmlspecialchars($user['email']); ?>
                    </p>
                    <p class="mb-2">
                        <i class="fa-solid fa-phone me-2 text-muted"></i>
                        <?php echo $user['phone'] ?: '-'; ?>
                    </p>
                    <p class="mb-0">
                        <i class="fa-solid fa-calendar me-2 text-muted"></i>
                        Member sejak <?php echo formatDate($user['created_at'], 'd M Y'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <!-- Edit Profile -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa-solid fa-user-edit me-2"></i>Edit Profil</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="name" class="form-control" required
                                       value="<?php echo htmlspecialchars($user['name']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control"
                                       value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>"
                                       placeholder="opsional">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                <small class="text-muted">Email tidak dapat diubah</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">No. Telepon</label>
                                <input type="text" name="phone" class="form-control"
                                       value="<?php echo htmlspecialchars($user['phone'] ?: ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($user['address'] ?: ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save me-1"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Change Password -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa-solid fa-key me-2"></i>Ubah Password</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="mb-3">
                        <label class="form-label">Password Saat Ini</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="new_password" class="form-control" required minlength="6">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Konfirmasi Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-warning">
                        <i class="fa-solid fa-key me-1"></i> Ubah Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
