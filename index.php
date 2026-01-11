<?php

require_once 'admin/config/database.php';


require_once 'admin/templates/functions.php';

$pdo = getDB();

// Buat query SQL untuk ambil semua bus yang aktif
// Tabel: tb_buss (bukan buses)
// Kolom: bus_id, bus_number, model, seat_capacity, dll
$sql = "SELECT * FROM tb_buss WHERE status = 'active' ORDER BY bus_id ASC";

// Siapkan statement
$stmt = $pdo->prepare($sql);

// Jalankan query
$stmt->execute();

// Ambil semua hasil query dalam bentuk array
// fetchAll() mengambil semua baris hasil query
$armadaData = $stmt->fetchAll();

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
    <title>GoGoTrans</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    
</head>
<body>
     <div id="preloader">
        <img src="assets/images/Loader.gif" 
             alt="Loading..." 
             class="loader-gif">
    </div>
    <div class="fContainer">
        <nav class="wrapper">
            <!-- Menu Kiri -->
            <ul class="navigation">
                <li><a>Home</a></li>
                <li><a href="armada.php">Armada</a></li>
                <li><a href="about.php">About</a></li>
            </ul>
            
            <!-- Logo Tengah -->
            <div class="brand">
                <img class="logo" src="assets/images/Travel.png">
            </div>
            
            <!-- Menu Kanan -->
            <ul class="navigation">
             <li><a href="FAQ.php">FAQ</a></li>
                <li><a href="Quisioner.php">Quisioner</a></li>
                <li class="button-only"><a href="login.php" class="btn-grad">Login</a></li>
            </ul>
        </nav>
    </div>
    
    <!-- ============================================ -->
    <!-- RUNNING TEXT / MARQUEE -->
    <!-- ============================================ -->
    <div class="text-running">
        <span>Information</span>
        <!-- Marquee = teks berjalan -->
        <!-- onmouseover="this.stop()" = berhenti saat mouse di atas -->
        <!-- onmouseout="this.start()" = jalan lagi saat mouse keluar -->
        <marquee behavior="" direction ="left" onmouseover="this.stop();" onmouseout="this.start();">
            Selamat Datang di Website GoGoTrans, Travel Terbaik di Pulau Bali, Siap mengantar anda sampai tujuan dengan Selamat, Aman, Nyaman dan Tepat Waktu Tentunya, Jika ada Kendala dan Hal lain sebagainya yang merugikan anda, silahkan laporkan ke Staff Kami yang Siap Sedia Membantu anda, Terima Kasih dan Selamat Datang
        </marquee> 
    </div>
    
    <!-- ============================================ -->
    <!-- SLIDER GAMBAR -->
    <!-- ============================================ -->
    <!-- Menggunakan Swiper.js untuk slider otomatis -->
    <div class="swiper mySwiper">
        <div class="swiper-wrapper">
            <div class="swiper-slide"><img class="imagetravel" src="assets/images/logo.jpeg"></div>
            <div class="swiper-slide"><img class="imagetravel2" src="assets/images/logo2.jpeg"></div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- INFO TIKET -->
    <!-- ============================================ -->
    <div class="ticket">
        <div class="logo-wrapper w-100 d-flex space-between">
            <img class="icon" src="assets/images/minibus.png">
            <h3 class="isi-ticket"> Untuk Mengecek Tiket Perjalanan Anda, Kunjungi Halaman Tiket Disamping, Terima Kasih</h3>
        </div>
        <div class="button-tiket flex "><a href="jadwal.php" class="btn-tiket">Cek Tiket</a></div>
    </div>

    <!-- ============================================ -->
    <!-- DAFTAR ARMADA -->
    <!-- ============================================ -->
    <?php 
    // Cek apakah ada data armada
    // empty() mengecek apakah array kosong
    if (!empty($armadaData)): 
    ?>
    <div class="scroller">
        <div class="group">
            <?php 
            
            foreach ($armadaData as $armada): 
               
                if (empty($armada['image_banner']) && empty($armada['image_url'])) {
                    continue; 
                }
                
                
                
                if (!empty($armada['image_banner'])) {
                    $gambar = getArmadaBanner($armada['image_banner'], $armada['image_url']);
                } else {
                    $gambar = getArmadaBanner($armada['image_banner'], $armada['image_url']);
                }
            ?>
            
            <a href="armada-detail.php?id=<?php echo $armada['bus_id']; ?>" class="card">
    
                <img src="<?php echo htmlspecialchars($gambar); ?>" 
                     alt="<?php echo htmlspecialchars($armada['model']); ?>">
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- ============================================ -->
    <!-- SECTION ABOUT -->
    <!-- ============================================ -->
    <section class="about">
        <div class="overlay"></div>

        <div class="about-content">
            <h4>Sekilas Tentang Kami</h4>
            <h1>GoGoTrans Travel</h1>
            <h3>
                Memberikan Keamanan dan Kenyamanan dalam Bepergian ke Mana Saja dan Kapan Saja
            </h3>

            <p>
                Saat ini, perjalanan wisata telah menjadi bagian dari gaya hidup modern.
                Banyak orang melakukan perjalanan wisata untuk menghilangkan kepenatan
                dari rutinitas sehari-hari.
            </p>

            <a href="about.php" class="btnabout">Profil Lengkap</a>
        </div>
    </section>
    
    <!-- ============================================ -->
    <!-- FOOTER -->
    <!-- ============================================ -->
    <footer class="footer">
        <div class="footer-top">
            <!-- Footer Kiri -->
           <div class="footer-left">
                <h2>GoGoTrans Travel</h2>
                <p>
                    GoGoTrans Travel merupakan perusahaan yang bergerak dibidang layanan jasa transportasi dan perjalanan wisata. Didirikan pada tahun 2023 dengan brand "Buat Perjalanan Kalian, Lebih Berkesan".
                </p>
            </div>

            <!-- Footer Kanan - Kontak -->
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
        
        <!-- Footer Bottom - Copyright -->
        <div class="footer-bottom">
            <span>© 2025 GoGoTrans. All Rights Reserved.</span>

            <div class="footer-menu">
                <a href="home.php">Home</a>
                <a href="about.php">About</a>
                <a href="armada.php">Armada</a>
                <a href="FAQ.php">FAQ</a>
                <a href="Quisioner.php">Quisioner</a>
            </div>
        </div>
    </footer>

    
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js"></script>
    
    <!-- Script untuk Swiper (slider) -->
    <script>
        // Inisialisasi Swiper
        var swiper = new Swiper(".mySwiper", {
            autoHeight: true,           // Tinggi otomatis menyesuaikan gambar
            autoplay: {
                delay: 3000,            // Ganti gambar setiap 3 detik
                disableOnInteraction: false,  // Tetap autoplay meski user interact
            },
            loop: true,                
        });
    </script>
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
