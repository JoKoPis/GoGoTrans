<?php
/**
 * Jadwal Keberangkatan (Public)
 * Menampilkan jadwal yang tersedia, user harus login untuk pesan
 */

session_start();
require_once 'admin/config/database.php';
require_once 'admin/templates/functions.php';

$isLoggedIn = isset($_SESSION['user_id']);

// Get available schedules
$pdo = getDB();
$filterRoute = $_GET['route'] ?? '';
$filterDate = $_GET['date'] ?? '';
$filterBusType = $_GET['bus_type'] ?? '';

// Query untuk ambil jadwal yang tersedia
// PERUBAHAN: schedules->tb_schedule, buses->tb_buss, routes->tb_route
// PERUBAHAN: bus_name->model, plate_number->bus_number, total_seats->seat_capacity
// PERUBAHAN: origin_city->departure_city, destination_city->arrival_city
// PERUBAHAN: departure_datetime->departure_time
$sql = "
    SELECT s.*, b.model, b.bus_number, b.bus_type, b.seat_capacity, b.facilities,
           r.departure_city, r.arrival_city, r.estimated_duration
    FROM tb_schedule s
    LEFT JOIN tb_buss b ON s.bus_id = b.bus_id
    LEFT JOIN tb_route r ON s.route_id = r.route_id
    WHERE s.status = 'available' AND s.departure_time > NOW()
";

$params = [];

if ($filterDate) {
    // PERUBAHAN: departure_datetime -> departure_time
    $sql .= " AND DATE(s.departure_time) = ?";
    $params[] = $filterDate;
}

if ($filterBusType) {
    $sql .= " AND b.bus_type = ?";
    $params[] = $filterBusType;
}

// PERUBAHAN: departure_datetime -> departure_time
$sql .= " ORDER BY s.departure_time ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$schedules = $stmt->fetchAll();

// Get all routes for filter
// PERUBAHAN: routes->tb_route, origin_city->departure_city, destination_city->arrival_city
$routes = $pdo->query("SELECT DISTINCT departure_city, arrival_city FROM tb_route WHERE is_active = 1")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal & Pesan Tiket - GoGoTrans</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <div id="preloader">
        <img src="assets/images/Loading.gif" 
             alt="Loading..." 
             class="loader-gif">
    </div>  

    <!-- Navbar -->
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
                <?php if ($isLoggedIn): ?>
                <li class="button-only"><a href="user/dashboard.php" class="btn-grad">Dash</a></li>
                <?php else: ?>
                <li class="button-only"><a href="login.php" class="btn-grad">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <!-- Hero -->
    <section class="hero-section">
        <h1><i class="fa-solid fa-ticket me-2"></i>Pesan Tiket</h1>
        <p class="lead mb-0">Pilih jadwal keberangkatan dan pesan tiket Anda sekarang!</p>
    </section>

    <!-- Content -->
    <div class="container pb-5 page-content">
        <!-- Filters -->
        <div class="filter-card">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label"><i class="fa-solid fa-calendar me-1"></i> Tanggal Berangkat</label>
                    <input type="date" name="date" class="form-control" value="<?php echo $filterDate; ?>" 
                           min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label"><i class="fa-solid fa-bus me-1"></i> Tipe Armada</label>
                    <select name="bus_type" class="form-select">
                        <option value="">Semua Tipe</option>
                        <option value="big_bus" <?php echo $filterBusType === 'big_bus' ? 'selected' : ''; ?>>Bus Pariwisata</option>
                        <option value="mini_bus" <?php echo $filterBusType === 'mini_bus' ? 'selected' : ''; ?>>Mini Bus</option>
                        <option value="hiace" <?php echo $filterBusType === 'hiace' ? 'selected' : ''; ?>>Toyota Hiace</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa-solid fa-search me-1"></i> Cari Jadwal
                    </button>
                </div>
            </form>
        </div>

        <!-- Schedule List -->
        <?php if (empty($schedules)): ?>
        <div class="text-center py-5">
            <i class="fa-solid fa-calendar-xmark fa-4x text-muted mb-3"></i>
            <h4>Tidak ada jadwal tersedia</h4>
            <p class="text-muted">Coba ubah filter atau cek kembali nanti</p>
        </div>
        <?php else: ?>
        <div class="row">
            <?php foreach ($schedules as $s): ?>
            <div class="col-md-6 mb-4">
                <div class="ticket-card">
                    <!-- Ticket Top -->
                    <div class="ticket-top">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="bus-badge">
                                <i class="fa-solid fa-bus me-1"></i> <?php echo getBusTypeLabel($s['bus_type']); ?>
                            </span>
                            <?php if ($s['estimated_duration']): ?>
                            <span class="duration-badge">
                                <i class="fa-regular fa-clock me-1"></i><?php echo $s['estimated_duration']; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="route-display">
                            <div class="city-info">
                                <!-- PERUBAHAN: departure_datetime -> departure_time -->
                                <div class="city-time"><?php echo date('H:i', strtotime($s['departure_time'])); ?></div>
                                <!-- PERUBAHAN: origin_city -> departure_city -->
                                <p class="city-name"><?php echo htmlspecialchars($s['departure_city']); ?></p>
                            </div>
                            <div class="route-connector">
                                <div class="route-line-svg">
                                    <div class="plane-icon">
                                        <i class="fa-solid fa-bus"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="city-info">
                                <!-- PERUBAHAN: arrival_datetime -> arrival_time -->
                                <div class="city-time"><?php echo date('H:i', strtotime($s['arrival_time'])); ?></div>
                                <!-- PERUBAHAN: destination_city -> arrival_city -->
                                <p class="city-name"><?php echo htmlspecialchars($s['arrival_city']); ?></p>
                            </div>
                        </div>
                        
                        <div class="date-display">
                            <i class="fa-regular fa-calendar me-1"></i>
                            <!-- PERUBAHAN: departure_datetime -> departure_time -->
                            <?php echo formatDate($s['departure_time'], 'l, d F Y'); ?>
                        </div>
                    </div>
                    
                    <!-- Ticket Bottom -->
                    <div class="ticket-bottom">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fa-solid fa-bus"></i>
                                </div>
                                <div class="info-text">
                                    <small>Armada</small>
                                    <!-- PERUBAHAN: bus_name -> model, plate_number -> bus_number -->
                                    <strong><?php echo htmlspecialchars($s['model'] ?: $s['bus_number']); ?></strong>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fa-solid fa-chair"></i>
                                </div>
                                <div class="info-text">
                                    <small>Kapasitas</small>
                                    <!-- PERUBAHAN: total_seats -> seat_capacity -->
                                    <strong><?php echo $s['seat_capacity']; ?> Kursi</strong>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($s['facilities']): ?>
                        <div class="facilities-tags">
                            <?php 
                            $facilities = array_slice(explode(',', $s['facilities']), 0, 4);
                            foreach ($facilities as $f): ?>
                            <span class="facility-tag"><?php echo trim($f); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="ticket-footer">
                            <div class="price-section">
                                <div class="price-label">Harga per orang</div>
                                <!-- PERUBAHAN: base_price -> price -->
                                <div class="price-amount"><?php echo formatRupiah($s['price']); ?></div>
                            </div>
                            <?php if ($isLoggedIn): ?>
                            <!-- PERUBAHAN: id -> schedule_id -->
                            <a href="pesan-tiket.php?schedule=<?php echo $s['schedule_id']; ?>" class="book-btn">
                                <i class="fa-solid fa-ticket"></i> Pesan Sekarang
                            </a>
                            <?php else: ?>
                            <a href="login.php?redirect=jadwal.php" class="book-btn book-btn-outline">
                                <i class="fa-solid fa-sign-in-alt"></i> Login
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
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
