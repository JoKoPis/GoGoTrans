<?php

require_once 'admin/config/database.php';
require_once 'admin/templates/functions.php';

// ============================================
// AMBIL DATA ARMADA DARI DATABASE
// ============================================

// Ambil koneksi database
$pdo = getDB();

// Buat query untuk ambil semua bus yang aktif
// PERUBAHAN: buses -> tb_buss, id -> bus_id
$sql = "SELECT * FROM tb_buss WHERE status = 'active' ORDER BY bus_id ASC";

// Siapkan statement
$stmt = $pdo->prepare($sql);

// Jalankan query
$stmt->execute();

// Ambil semua hasil dalam bentuk array
$armadaData = $stmt->fetchAll();

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GoGoTrans - Armada</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/armada.css">
    
</head>
<body>
    <div id="preloader">
        <img src="assets/images/Loading.gif" 
             alt="Loading..." 
             class="loader-gif">
    </div>
    <!-- ============================================ -->
    <!-- NAVBAR -->
    <!-- ============================================ -->
    <div class="fContainer">
        <nav class="wrapper">
            <ul class="navigation">
                <li><a href="index.php">Home</a></li>
                <li><a>Armada</a></li>
                <li><a href="about.php">About</a></li>
            </ul>
            
            <div class="brand">
                <a href ="index.php">
                    <img href="index.php" class="logo" src="assets/images/Travel.png">
                </a>
            </div>
            
            <ul class="navigation">
             <li><a href="FAQ.php">FAQ</a></li>
                <li><a href="Quisioner.php">Quisioner</a></li>
                <li class="button-only"><a href="login.php" class="btn-grad">Login</a></li>
            </ul>
        </nav>
    </div>
    
    <!-- ============================================ -->
    <!-- RUNNING TEXT -->
    <!-- ============================================ -->
    <div class="text-running">
        <span>Information</span>
        <marquee behavior="" direction ="left" onmouseover="this.stop();" onmouseout="this.start();">
            Selamat Datang di Website GoGoTrans, Travel Terbaik di Pulau Bali, Siap mengantar anda sampai tujuan dengan Selamat, Aman, Nyaman dan Tepat Waktu Tentunya, Jika ada Kendala dan Hal lain sebagainya yang merugikan anda, silahkan laporkan ke Staff Kami yang Siap Sedia Membantu anda, Terima Kasih dan Selamat Datang
        </marquee> 
    </div>
    
    <!-- ============================================ -->
    <!-- BANNER ARMADA -->
    <!-- ============================================ -->
    <div class="armada-banner">
        <h1>ARMADA</h1>
        <div class="banner-image">
            <img src="assets/images/logo2.jpg" alt="Armada">
        </div>
    </div>

    <!-- ============================================ -->
    <!-- DAFTAR ARMADA -->
    <!-- ============================================ -->
    <section class="armada-list">
        <h2 class="section-title">Pilih Armada Kami</h2>
        
        <div class="armada-grid">
            <?php 
            // Loop semua data armada
            foreach ($armadaData as $item): 
                // Skip armada yang tidak punya gambar
                // empty() mengecek apakah variable kosong/null
                if (empty($item['image_url'])) {
                    continue; // Lanjut ke iterasi berikutnya
                }
            ?>
            
            <!-- Card Armada -->
            <!-- Link ke halaman detail dengan parameter ID -->
            <!-- PERUBAHAN: id -> bus_id -->
            <a href="armada-detail.php?id=<?php echo $item['bus_id']; ?>" class="armada-card">
                <!-- Gambar Armada -->
                <div class="armada-image">
                    <!-- getArmadaImage() mengambil URL gambar -->
                    <!-- htmlspecialchars() untuk keamanan -->
                    <img src="<?php echo htmlspecialchars(getArmadaImage($item['image_url'])); ?>" 
                         alt="<?php echo htmlspecialchars($item['model']); ?>">
                </div>
                
                <!-- Informasi Armada -->
                <div class="armada-content">
                    <!-- Nama Bus -->
                    <!-- PERUBAHAN: bus_name -> model -->
                    <h3><?php echo htmlspecialchars($item['model']); ?></h3>
                    
                    <!-- Kapasitas Penumpang -->
                    <!-- PERUBAHAN: total_seats -> seat_capacity -->
                    <p class="kapasitas">
                        <i class="fa-solid fa-users"></i> 
                        <?php echo $item['seat_capacity']; ?> Penumpang
                    </p>
                    
                    <!-- Tipe Bus -->
                    <!-- getBusTypeLabel() mengubah kode jadi label Indonesia -->
                    <p class="tipe"><?php echo getBusTypeLabel($item['bus_type']); ?></p>
                    
                    <!-- Button Detail -->
                    <span class="btn-detail">
                        Lihat Detail <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </div>
            </a>
            
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- FOOTER -->
    <!-- ============================================ -->
    <footer class="footer">
        <div class="footer-top">
           <div class="footer-left">
                <h2>GoGoTrans Travel</h2>
                <p>
                    GoGoTrans Travel merupakan perusahaan yang bergerak dibidang layanan jasa transportasi dan perjalanan wisata. Didirikan pada tahun 2025 dengan brand "Buat Perjalanan Kalian, Lebih Berkesan".
                </p>
            </div>
            
            <div class="footer-right">
                <h3>Kontak</h3>
                <ul class="contact-list">
                    <li><i class="fa-brands fa-whatsapp"></i> +62 812-460-28884 (Jul)</li>
                    <li><i class="fa-brands fa-whatsapp"></i> +62 822-368-11947 (Flo)</li>
                    <li><i class="fa-brands fa-whatsapp"></i> +62 878-416-25261 (Pontius)</li>
                    <li><i class="fa-brands fa-whatsapp"></i> +62 991-232-16637 (Vivi)</li>
                    <li><i class="fa-brands fa-whatsapp"></i> +62 112-465-77823 (Dena)</li>
                    <li><i class="fa-brands fa-whatsapp"></i> +62 432-211-64736 (Milan)</li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <span>© 2025 GoGoTrans. All Rights Reserved.</span>

            <div class="footer-menu">
                <a href="index.php">Home</a>
                <a href="about.php">About</a>
                <a href="armada.php">Armada</a>
                <a href="FAQ.php">FAQ</a>
                <a href="Quisioner.php">Quisioner</a>
            </div>
        </div>
    </footer>
    <script>
        // Sembunyikan preloader setelah halaman selesai dimuat
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.getElementById('preloader').classList.add('hidden');
            }, 900); // Delay 1 detik untuk demo
        });

        // Fungsi untuk demo menampilkan preloader lagi
        function showPreloader() {
            const preloader = document.getElementById('preloader');
            preloader.classList.remove('hidden');
            
            // Sembunyikan lagi setelah 2 detik
            setTimeout(function() {
                preloader.classList.add('hidden');
            }, 1000);
        }
    </script>
</body>
</html>

