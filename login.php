<?php
session_start();


require_once 'admin/config/database.php';


$error = '';           
$success = '';         
$showRegister = false; 

$redirect = $_GET['redirect'] ?? '';


if (isset($_SESSION['user_id'])) {

    if ($redirect) {
        
        header('Location: ' . $redirect);
    } else if ($_SESSION['role'] === 'admin') {
        
        header('Location: admin/dashboard.php');
    } else {
        
        header('Location: user/dashboard.php');
    }
    exit; 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    

    if ($_POST['action'] === 'login') {
        
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        if (empty($username) || empty($password)) {
            $error = 'Username dan password harus diisi!';
        } else {
            
            $pdo = getDB();
            
            $sql = "SELECT u.*, r.role_name 
                    FROM tb_user u
                    LEFT JOIN tb_role r ON u.role_id = r.role_id
                    WHERE (u.username = ? OR u.email = ?)";
            
           
            $stmt = $pdo->prepare($sql);
            

            $stmt->execute([$username, $username]);
            
            // Ambil 1 baris hasil query
            // fetch() mengambil 1 baris, fetchAll() mengambil semua baris
            $foundUser = $stmt->fetch();
            
            // STEP 3: Verifikasi password
            // password_verify() membandingkan password yang diinput dengan hash di database
            // Fungsi ini aman dan otomatis handle salt
            // PERUBAHAN: password_hash -> pass
            if ($foundUser && password_verify($password, $foundUser['pass'])) {
                // Password benar! Simpan data user ke session
                
                // PERUBAHAN: id -> user_id, role -> role_name
                $_SESSION['user_id'] = $foundUser['user_id'];
                $_SESSION['username'] = $foundUser['username'] ?? $foundUser['email'];
                $_SESSION['nama'] = $foundUser['name'] ?? $foundUser['username'];
                $_SESSION['role'] = $foundUser['role_name']; // admin atau customer
                
                // STEP 4: Redirect ke dashboard
                if ($redirect) {
                    // Jika ada redirect, ke halaman tersebut
                    header('Location: ' . $redirect);
                } else if ($foundUser['role_name'] === 'admin') {
                    // Jika admin, ke dashboard admin
                    header('Location: admin/dashboard.php');
                } else {
                    // Jika customer, ke dashboard user
                    header('Location: user/dashboard.php');
                }
                exit;
            } else {
                // Password salah atau user tidak ditemukan
                $error = 'Username atau password salah!';
            }
        }
    }
    
    // ============================================
    // PROSES REGISTER
    // ============================================
    if ($_POST['action'] === 'register') {
        // Ambil data dari form register
        $username = trim($_POST['reg_username'] ?? '');
        $email = trim($_POST['reg_email'] ?? '');
        $password = trim($_POST['reg_password'] ?? '');
        
        // STEP 1: Validasi input
        if (empty($username) || empty($email) || empty($password)) {
            $error = 'Semua field harus diisi!';
            $showRegister = true; // Tetap tampilkan form register
        } else {
            // STEP 2: Cek apakah username/email sudah ada
            
            // Ambil koneksi database
            $pdo = getDB();
            
            // Query untuk cek username atau email
            // PERUBAHAN: users -> tb_user, id -> user_id
            $checkSql = "SELECT user_id FROM tb_user WHERE username = ? OR email = ?";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([$username, $email]);
            
            // Coba ambil 1 baris
            $exists = $checkStmt->fetch();
            
            if ($exists) {
                // Jika ada, berarti username/email sudah dipakai
                $error = 'Username atau email sudah terdaftar!';
                $showRegister = true;
            } else {
                // STEP 3: Hash password
                // password_hash() mengenkripsi password agar aman
                // PASSWORD_DEFAULT menggunakan algoritma terbaik saat ini (bcrypt)
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // STEP 4: Insert user baru ke database
                // PERUBAHAN: 
                // - Tabel users -> tb_user
                // - Kolom password_hash -> pass
                // - Tambah role_id (2 = customer)
                // - Hapus is_active (tidak ada di tb_user)
                $insertSql = "INSERT INTO tb_user 
                              (role_id, username, name, email, pass, created_at) 
                              VALUES (2, ?, ?, ?, ?, NOW())";
                
                $insertStmt = $pdo->prepare($insertSql);
                
                // Execute dengan data user
                // role_id = 2 untuk customer (1 = admin)
                // name kita isi dengan username dulu, bisa diubah nanti
                $insertStmt->execute([$username, $username, $email, $hashedPassword]);
                
                // Registrasi berhasil!
                $success = 'Registrasi berhasil! Silakan login dengan akun Anda.';
                $showRegister = false; // Kembali ke form login
            }
        }
    }
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - GoGoTrans</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    
</head>
<body>
    
   <div id="preloader">
        <img src="assets/images/Loader.gif" 
             alt="Loading..." 
             class="loader-gif">
    </div>

    <div class="container <?php echo $showRegister ? 'active' : ''; ?>">
        
        <!-- ============================================ -->
        <!-- FORM LOGIN -->
        <!-- ============================================ -->
        <div class="form-box login">
            <form action="login.php" method="POST">
                <!-- Hidden input untuk identifikasi action -->
                <input type="hidden" name="action" value="login">
                
                <h1>Login</h1>
                
                <!-- Tampilkan error jika ada (untuk form login) -->
                <?php if ($error && !$showRegister): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <!-- Tampilkan success jika ada -->
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <!-- Input Username/Email -->
                <div class="input-box">
                    <input type="text" name="username" placeholder="Username atau Email" required>
                    <i class="fa-solid fa-user"></i>
                </div>
                
                <!-- Input Password -->
                <div class="input-box">
                    <input type="password" name="password" placeholder="Password" required>
                    <i class="fa-solid fa-lock"></i>
                </div>
                
                <!-- Link Lupa Password -->
                <div class="forgot-link">
                    <a href="#">Lupa Password</a>
                </div>
                
                <!-- Button Submit -->
                <button type="submit" class="btn-login">Login</button>
            </form>
        </div>

        <!-- ============================================ -->
        <!-- FORM REGISTER -->
        <!-- ============================================ -->
        <div class="form-box register">
            <form action="login.php" method="POST">
                <!-- Hidden input untuk identifikasi action -->
                <input type="hidden" name="action" value="register">
                
                <h1>Registration</h1>
                
                <!-- Tampilkan error jika ada (untuk form register) -->
                <?php if ($error && $showRegister): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <!-- Input Username -->
                <div class="input-box">
                    <input type="text" name="reg_username" placeholder="Username" required>
                    <i class="fa-solid fa-user"></i>
                </div>
                
                <!-- Input Email -->
                <div class="input-box">
                    <input type="email" name="reg_email" placeholder="Email" required>
                    <i class="fa-solid fa-envelope"></i>
                </div>
                
                <!-- Input Password -->
                <div class="input-box">
                    <input type="password" name="reg_password" placeholder="Password" required>
                    <i class="fa-solid fa-lock"></i>
                </div>
                
                <!-- Button Submit -->
                <button type="submit" class="btn-daftar">Register</button>
            </form>
        </div>

        <!-- ============================================ -->
        <!-- TOGGLE BOX (PANEL SAMPING) -->
        <!-- ============================================ -->
        <div class="toggle-box">
            <!-- Panel Kiri (muncul saat di form register) -->
            <div class="toggle-panel toggle-left">
                <h1>Selamat Datang Kembali!</h1><br>
                <p>Belum Punya Akun?</p>
                <button class="btn register-btn" type="button">Register</button>
            </div>
            
            <!-- Panel Kanan (muncul saat di form login) -->
            <div class="toggle-panel toggle-right">
                <h1>Halo, Selamat Datang!</h1><br>
                <p>Sudah Punya Akun?</p>
                <button class="btn login-btn" type="button">Login</button>
            </div>
        </div>
    </div>
    
    <script src="assets/js/login.js"></script>
    <script>
        // Sembunyikan preloader setelah halaman selesai dimuat
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.getElementById('preloader').classList.add('hidden');
            }, 1500); // Delay 1 detik untuk demo
        });

        // Fungsi untuk demo menampilkan preloader lagi
        function showPreloader() {
            const preloader = document.getElementById('preloader');
            preloader.classList.remove('hidden');
            
            // Sembunyikan lagi setelah 2 detik
            setTimeout(function() {
                preloader.classList.add('hidden');
            }, 2000);
        }
    </script>
</body>
</html>
