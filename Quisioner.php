<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
    <title>GoGoTrans</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/questionnaire.css">
    
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
        <a href ="index.php">
        <img href="index.php" class="logo" src="assets/images/Travel.png">
        </a>
    </div>
            <ul class="navigation">
             <li><a href="FAQ.php">FAQ</a></li>
                <li><a>Quisioner</a></li>
                <li class="button-only"><a href="login.php" class="btn-grad">Login</a></li>
            </ul>
        </nav>
    </div>
    <div class="text-running">
        <span>Information</span>
        <marquee behavior="" direction ="left" onmouseover="this.stop();" onmouseout="this.start();">Selamat Datang di Website GoGoTrans, Travel Terbaik di Pulau Bali, Siap mengantar anda sampai tujuan dengan Selamat, Aman, Nyaman dan Tepat Waktu Tentunya, Jika ada Kendala dan Hal lain sebagainya yang merugikan anda, silahkan laporkan ke Staff Kami yang Siap Sedia Membantu anda, Terima Kasih dan Selamat Datang</marquee> 
    </div>
    </div>
    <div class="armada-banner">
  <h1>Quisioner</h1>

  <div class="banner-image">
    <img src="assets/images/logo2.jpg" alt="Quisioner">
  </div>
</div>
<div class="container">
        <h1>Kirimkan Pesan kepada Kami</h1>
        
        <form id="contactForm">
            <div class="form-group">
                <label>
                    Nama lengkap <span class="required">(Required)</span>
                </label>
                <input type="text" name="name" placeholder="Full Name" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>
                        Email <span class="required">(Required)</span>
                    </label>
                    <input type="email" name="email" placeholder="Email" required>
                </div>

                <div class="form-group">
                    <label>
                        Nomor Telepon <span class="required">(Required)</span>
                    </label>
                    <input type="tel" name="phone" placeholder="Phone Number" required>
                </div>
            </div>

            <div class="form-group">
                <label>
                    Subyek <span class="required">(Required)</span>
                </label>
                <input type="text" name="subject" placeholder="Subject" required>
            </div>

            <div class="form-group">
                <label>Isi Pesan</label>
                <textarea name="message" placeholder="Tulis pesan Anda di sini..."></textarea>
            </div>

            <button type="submit" class="submit-btn">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                </svg>
                Kirim Pesan
            </button>
        </form>

        <div class="success-message" id="successMessage">
            ✓ Pesan Anda berhasil dikirim!
        </div>
    </div>
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
            <a href="home.php">Home</a>
            <a href="about.php">About</a>
            <a href="armada.php">Armada</a>
            <a href="FAQ.php">FAQ</a>
            <a href="Quisioner.php">Quisioner</a>
        </div>
    </div>

    <script>
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const name = formData.get('name');
            const email = formData.get('email');
            const phone = formData.get('phone');
            const subject = formData.get('subject');
            const message = formData.get('message');
            
            // Tampilkan pesan sukses
            document.getElementById('successMessage').style.display = 'block';
            
            // Reset form setelah 2 detik
            setTimeout(() => {
                this.reset();
                document.getElementById('successMessage').style.display = 'none';
            }, 2000);
        });
    </script>
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
</footer>
</body>
</html>
