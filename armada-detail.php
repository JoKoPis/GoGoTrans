<?php
// Load database and helper functions
require_once 'admin/config/database.php';
require_once 'admin/templates/functions.php';

// Get ID param
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Redirect if no ID
if (!$id) {
    header('Location: armada.php');
    exit;
}

// Get armada from database
$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM tb_buss WHERE bus_id = ?");
$stmt->execute([$id]);
$armada = $stmt->fetch();

// Redirect if not found
if (!$armada) {
    header('Location: armada.php');
    exit;
}

// Get armada image URL using helper
$imageUrl = getArmadaImage($armada['image_url']);

// Parse facilities
$fasilitas = $armada['facilities'] ? array_map('trim', explode(',', $armada['facilities'])) : [];
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GoGoTrans - <?php echo htmlspecialchars($armada['model']); ?></title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/armada-detail.css">
</head>
<body>
    <div id="preloader">
        <img src="assets/images/Loading.gif" 
             alt="Loading..." 
             class="loader-gif">
    </div>  
    <div class="fContainer">
        <nav class="wrapper">
            <ul class="navigation">
                <li><a href="index.php">Home</a></li>
                <li><a href="armada.php">Armada</a></li>
                <li><a href="about.php">About</a></li>
            </ul>
            <div class="brand">
                <a href="index.php">
                    <img class="logo" src="assets/images/Travel.png">
                </a>
            </div>
            <ul class="navigation">
                <li><a href="FAQ.php">FAQ</a></li>
                <li><a href="Quisioner.php">Quisioner</a></li>
                <li class="button-only"><a href="login.php" class="btn-grad">Login</a></li>
            </ul>
        </nav>
    </div>
    
    <div class="text-running">
        <span>Information</span>
        <marquee behavior="" direction="left" onmouseover="this.stop();" onmouseout="this.start();">Selamat Datang di Website GoGoTrans, Travel Terbaik di Pulau Bali, Siap mengantar anda sampai tujuan dengan Selamat, Aman, Nyaman dan Tepat Waktu Tentunya.</marquee> 
    </div>
    
    <!-- Breadcrumb -->
    <div class="breadcrumb-container">
        <a href="index.php">Home</a> <span>></span>
        <a href="armada.php">Armada</a> <span>></span>
        <span class="current"><?php echo htmlspecialchars($armada['model']); ?></span>
    </div>
    
    <!-- Detail Armada -->
    <section class="armada-detail">
        <div class="detail-header">
            <div class="detail-image">
                <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($armada['model']); ?>">
            </div>
            <div class="detail-title">
                <h1><?php echo htmlspecialchars($armada['model']); ?></h1>
                <p class="tipe"><?php echo getBusTypeLabel($armada['bus_type']); ?> | <?php echo $armada['bus_number']; ?></p>
            </div>
        </div>
        
        <div class="detail-container">
            <div class="detail-info">
                <div class="info-card">
                    <h3><i class="fa-solid fa-users"></i> Kapasitas</h3>
                    <p><?php echo $armada['seat_capacity']; ?> Penumpang</p>
                </div>
                
                <div class="info-card">
                    <h3><i class="fa-solid fa-bus"></i> Tipe</h3>
                    <p><?php echo getBusTypeLabel($armada['bus_type']); ?></p>
                </div>
                
                <?php if (!empty($fasilitas)): ?>
                <div class="info-card fasilitas-card">
                    <h3><i class="fa-solid fa-star"></i> Fasilitas</h3>
                    <ul class="fasilitas-list">
                        <?php foreach ($fasilitas as $f): ?>
                            <li><i class="fa-solid fa-check"></i> <?php echo htmlspecialchars($f); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="detail-description">
                <h3>Deskripsi</h3>
                <p><?php echo nl2br(htmlspecialchars($armada['description'] ?: 'Armada berkualitas dengan pelayanan terbaik untuk perjalanan wisata Anda.')); ?></p>
                
                <div class="action-buttons">
                    <a href="https://wa.me/6281246028884?text=Halo, saya tertarik untuk menyewa <?php echo urlencode($armada['model']); ?>" class="btn-wa" target="_blank">
                        <i class="fa-brands fa-whatsapp"></i> Hubungi via WhatsApp
                    </a>
                    <a href="jadwal.php?bus_type=<?php echo $armada['bus_type']; ?>" class="btn-jadwal">
                        <i class="fa-solid fa-ticket"></i> Lihat Jadwal
                    </a>
                    <a href="armada.php" class="btn-back">
                        <i class="fa-solid fa-arrow-left"></i> Kembali ke Armada
                    </a>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-top">
           <div class="footer-left">
                <h2>GoGoTrans Travel</h2>
                <p>GoGoTrans Travel merupakan perusahaan yang bergerak dibidang layanan jasa transportasi dan perjalanan wisata. Didirikan pada tahun 2025 dengan brand "Buat Perjalanan Kalian, Lebih Berkesan".</p>
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
