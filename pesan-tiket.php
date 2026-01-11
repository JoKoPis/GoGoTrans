<?php
/**
 * Pesan Tiket / Booking Form
 * Requires login, creates new booking
 */

session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=jadwal.php');
    exit;
}

require_once 'admin/config/database.php';
require_once 'admin/templates/functions.php';

$userId = $_SESSION['user_id'];
$scheduleId = $_GET['schedule'] ?? null;

if (!$scheduleId) {
    header('Location: jadwal.php');
    exit;
}

// Get schedule detail
$pdo = getDB();
$stmt = $pdo->prepare("
    SELECT s.*, b.model, b.bus_number, b.bus_type, b.seat_capacity, b.facilities,
           r.departure_city, r.arrival_city, r.estimated_duration
    FROM tb_schedule s
    LEFT JOIN tb_buss b ON s.bus_id = b.bus_id
    LEFT JOIN tb_route r ON s.route_id = r.route_id
    WHERE s.schedule_id = ? AND s.status = 'available' AND s.departure_time > NOW()
");
$stmt->execute([$scheduleId]);
$schedule = $stmt->fetch();

if (!$schedule) {
    header('Location: jadwal.php');
    exit;
}

// Get all seats for the bus
$allSeatsStmt = $pdo->prepare("SELECT * FROM tb_seat WHERE bus_id = ? ORDER BY seat_number");
$allSeatsStmt->execute([$schedule['bus_id']]);
$allSeats = $allSeatsStmt->fetchAll();

// Get occupied seats for this schedule
$occupiedStmt = $pdo->prepare("
    SELECT bd.seat_id 
    FROM tb_booking_details bd
    JOIN tb_booking bk ON bd.booking_id = bk.booking_id
    WHERE bk.schedule_id = ? AND bk.status IN ('pending', 'paid', 'success')
");
$occupiedStmt->execute([$scheduleId]);
$occupiedSeats = array_column($occupiedStmt->fetchAll(), 'seat_id');

// Calculate available seats
$availableCount = count($allSeats) - count($occupiedSeats);

// Process booking
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passengerCount = (int)$_POST['passenger_count'];
    $selectedSeats = $_POST['seats'] ?? [];
    $paymentMethod = $_POST['payment_method'];
    
    if ($passengerCount < 1 || $passengerCount > 10) {
        $error = 'Jumlah penumpang harus 1-10 orang';
    } elseif (count($selectedSeats) !== $passengerCount) {
        $error = 'Pilih kursi sesuai jumlah penumpang (' . $passengerCount . ' kursi)';
    } else {
        try {
            $pdo->beginTransaction();
            
            $bookingCode = 'GGT-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
            $totalPrice = $schedule['price'] * $passengerCount;
            
            // Insert Booking
            $sql = "INSERT INTO tb_booking (user_id, schedule_id, booking_code, total_amount, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId, $scheduleId, $bookingCode, $totalPrice, 'pending']);
            $bookingId = $pdo->lastInsertId();
            
            // Insert Booking Seats
            $sql = "INSERT INTO tb_booking_details (booking_id, seat_id, price) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            foreach ($selectedSeats as $seatId) {
                $stmt->execute([$bookingId, $seatId, $schedule['price']]);
            }
            
            // Insert Passengers
            $sql = "INSERT INTO tb_passengers (booking_id, name, identity_number, phone) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            for ($i = 1; $i <= $passengerCount; $i++) {
                $stmt->execute([
                    $bookingId,
                    trim($_POST["passenger_name_$i"]),
                    trim($_POST["passenger_identity_$i"] ?? ''),
                    trim($_POST["passenger_phone_$i"] ?? '')
                ]);
            }
            
            // Insert Payment Method
            $sql = "INSERT INTO tb_payment_method (booking_id, method, amount, payment_status) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$bookingId, $paymentMethod, $totalPrice, 'pending']);
            
            $pdo->commit();
            header('Location: user/booking-detail.php?id=' . $bookingId . '&new=1');
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}

// Determine seat layout based on bus type
$seatsPerRow = 4; // Default for big bus
if ($schedule['bus_type'] === 'hiace') $seatsPerRow = 4;
elseif ($schedule['bus_type'] === 'innova' || $schedule['bus_type'] === 'avanza') $seatsPerRow = 3;
elseif ($schedule['bus_type'] === 'mini_bus') $seatsPerRow = 4;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Tiket - GoGoTrans</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/pesan-tiket.css">
    <style>
        .seats-container {
            grid-template-columns: repeat(<?php echo $seatsPerRow; ?>, 1fr);
        }
        <?php if ($seatsPerRow !== 4): ?>
        /* Override default aisle spacing if not 4 seats per row */
        .seats-container .seat:nth-child(4n-1) {
            margin-left: 0;
        }
        <?php endif; ?>
    </style>
</head>
<body>
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
                <li><a href="jadwal.php">Jadwal</a></li>
                <li class="button-only"><a href="user/dashboard.php" class="btn-grad">Dashboard</a></li>
            </ul>
        </nav>
    </div>

    <div class="booking-page">
        <div class="container booking-container">
            <!-- Trip Summary -->
            <div class="trip-summary">
                <div class="d-flex justify-content-between align-items-start flex-wrap">
                    <span class="badge bg-white text-primary px-3 py-2 rounded-pill">
                        <i class="fa-solid fa-bus me-1"></i> <?php echo getBusTypeLabel($schedule['bus_type']); ?>
                    </span>
                    <div class="text-end">
                        <div style="font-size: 1.8rem; font-weight: 700;"><?php echo formatRupiah($schedule['price']); ?></div>
                        <small style="opacity: 0.8;">/orang</small>
                    </div>
                </div>
                
                <div class="trip-route">
                    <div class="trip-city">
                        <div class="time"><?php echo date('H:i', strtotime($schedule['departure_time'])); ?></div>
                        <div class="city"><?php echo htmlspecialchars($schedule['departure_city']); ?></div>
                    </div>
                    <div class="trip-connector">
                        <div class="trip-line"></div>
                        <div class="trip-icon">
                            <i class="fa-solid fa-bus"></i>
                        </div>
                        <div class="trip-line"></div>
                    </div>
                    <div class="trip-city">
                        <div class="time"><?php echo date('H:i', strtotime($schedule['arrival_time'])); ?></div>
                        <div class="city"><?php echo htmlspecialchars($schedule['arrival_city']); ?></div>
                    </div>
                </div>
                
                <div class="trip-meta">
                    <div class="trip-meta-item">
                        <i class="fa-regular fa-calendar"></i>
                        <?php echo formatDate($schedule['departure_time'], 'l, d F Y'); ?>
                    </div>
                    <div class="trip-meta-item">
                        <i class="fa-solid fa-bus"></i>
                        <?php echo htmlspecialchars($schedule['model'] ?: $schedule['bus_number']); ?>
                    </div>
                    <?php if ($schedule['estimated_duration']): ?>
                    <div class="trip-meta-item">
                        <i class="fa-regular fa-clock"></i>
                        <?php echo $schedule['estimated_duration']; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($error): ?>
            <div class="alert-custom">
                <i class="fa-solid fa-exclamation-triangle me-2"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST" id="bookingForm">
                <!-- Step 1: Jumlah Penumpang -->
                <div class="step-card">
                    <div class="step-header">
                        <div class="step-number">1</div>
                        <div>
                            <h3 class="step-title">Jumlah Penumpang</h3>
                            <p class="step-desc">Pilih jumlah penumpang yang akan berangkat</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="count-selector">
                            <button type="button" class="count-btn" onclick="changeCount(-1)">−</button>
                            <div class="count-display" id="countDisplay">1</div>
                            <button type="button" class="count-btn" onclick="changeCount(1)">+</button>
                            <input type="hidden" name="passenger_count" id="passengerCount" value="1">
                        </div>
                        <div class="seat-info">
                            <i class="fa-solid fa-chair"></i>
                            <span><strong><?php echo $availableCount; ?></strong> kursi tersedia</span>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Pilih Kursi -->
                <div class="step-card">
                    <div class="step-header">
                        <div class="step-number">2</div>
                        <div>
                            <h3 class="step-title">Pilih Kursi</h3>
                            <p class="step-desc">Klik kursi yang tersedia untuk memilih</p>
                        </div>
                    </div>
                    
                    <div class="bus-layout">
                        <div class="bus-front">
                            <i class="fa-solid fa-user"></i> SUPIR
                        </div>
                        
                        <div class="seats-container">
                            <?php foreach ($allSeats as $seat): 
                                $isOccupied = in_array($seat['seat_id'], $occupiedSeats);
                            ?>
                            <div class="seat <?php echo $isOccupied ? 'occupied' : 'available'; ?>" 
                                 data-seat-id="<?php echo $seat['seat_id']; ?>"
                                 data-seat-number="<?php echo $seat['seat_number']; ?>"
                                 <?php echo $isOccupied ? '' : 'onclick="toggleSeat(this)"'; ?>>
                                <i class="fa-solid fa-couch"></i>
                                <span><?php echo $seat['seat_number']; ?></span>
                                <?php if (!$isOccupied): ?>
                                <input type="checkbox" name="seats[]" value="<?php echo $seat['seat_id']; ?>" style="display:none;">
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="seat-legend">
                            <div class="legend-item">
                                <div class="legend-dot available"></div>
                                <span>Tersedia</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-dot selected"></div>
                                <span>Dipilih</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-dot occupied"></div>
                                <span>Terisi</span>
                            </div>
                        </div>
                        
                        <div class="selected-seats" id="selectedSeatsDisplay" style="display: none;">
                            <i class="fa-solid fa-check-circle text-primary"></i>
                            <span>Kursi dipilih:</span>
                            <div id="selectedSeatTags"></div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Data Penumpang -->
                <div class="step-card">
                    <div class="step-header">
                        <div class="step-number">3</div>
                        <div>
                            <h3 class="step-title">Data Penumpang</h3>
                            <p class="step-desc">Isi data sesuai identitas penumpang</p>
                        </div>
                    </div>
                    
                    <div id="passengerForms">
                        <!-- Generated by JS -->
                    </div>
                </div>

                <!-- Step 4: Metode Pembayaran -->
                <div class="step-card">
                    <div class="step-header">
                        <div class="step-number">4</div>
                        <div>
                            <h3 class="step-title">Metode Pembayaran</h3>
                            <p class="step-desc">Pilih metode pembayaran yang Anda inginkan</p>
                        </div>
                    </div>
                    
                    <div class="payment-options">
                        <label class="payment-option selected" onclick="selectPayment(this)">
                            <input type="radio" name="payment_method" value="transfer" checked>
                            <i class="fa-solid fa-building-columns"></i>
                            <span>Transfer Bank</span>
                        </label>
                        <label class="payment-option" onclick="selectPayment(this)">
                            <input type="radio" name="payment_method" value="ewallet">
                            <i class="fa-solid fa-wallet"></i>
                            <span>E-Wallet</span>
                        </label>
                        <label class="payment-option" onclick="selectPayment(this)">
                            <input type="radio" name="payment_method" value="cash">
                            <i class="fa-solid fa-money-bill-wave"></i>
                            <span>Bayar di Tempat</span>
                        </label>
                    </div>
                </div>

                <!-- Price Summary & Submit -->
                <div class="step-card">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="price-summary">
                                <div class="price-row">
                                    <span>Harga per orang</span>
                                    <span><?php echo formatRupiah($schedule['price']); ?></span>
                                </div>
                                <div class="price-row">
                                    <span>Jumlah penumpang</span>
                                    <span id="summaryCount">1 orang</span>
                                </div>
                                <div class="price-row total">
                                    <span>Total Pembayaran</span>
                                    <span id="totalPrice"><?php echo formatRupiah($schedule['price']); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mt-4 mt-md-0">
                            <button type="submit" class="btn-submit">
                                <i class="fa-solid fa-check-circle me-2"></i> Konfirmasi & Bayar
                            </button>
                            <p class="text-center text-muted mt-3 mb-0">
                                <i class="fa-solid fa-shield-halved me-1"></i> Transaksi aman & terenkripsi
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        const basePrice = <?php echo $schedule['price']; ?>;
        const maxSeats = <?php echo $availableCount; ?>;
        let passengerCount = 1;
        let selectedSeats = [];
        
        function formatRupiah(num) {
            return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        
        function changeCount(delta) {
            const newCount = passengerCount + delta;
            if (newCount >= 1 && newCount <= Math.min(10, maxSeats)) {
                passengerCount = newCount;
                document.getElementById('countDisplay').textContent = passengerCount;
                document.getElementById('passengerCount').value = passengerCount;
                document.getElementById('summaryCount').textContent = passengerCount + ' orang';
                document.getElementById('totalPrice').textContent = formatRupiah(basePrice * passengerCount);
                
                // Reset seat selection if selected more than new count
                if (selectedSeats.length > passengerCount) {
                    resetSeats();
                }
                
                updatePassengerForms();
            }
        }
        
        function toggleSeat(el) {
            const seatId = el.dataset.seatId;
            const seatNumber = el.dataset.seatNumber;
            const checkbox = el.querySelector('input');
            
            if (el.classList.contains('selected')) {
                el.classList.remove('selected');
                el.classList.add('available');
                checkbox.checked = false;
                selectedSeats = selectedSeats.filter(s => s.id !== seatId);
            } else {
                if (selectedSeats.length < passengerCount) {
                    el.classList.remove('available');
                    el.classList.add('selected');
                    checkbox.checked = true;
                    selectedSeats.push({ id: seatId, number: seatNumber });
                } else {
                    alert('Anda hanya bisa memilih ' + passengerCount + ' kursi');
                    return;
                }
            }
            
            updateSelectedDisplay();
            updatePassengerForms();
        }
        
        function resetSeats() {
            document.querySelectorAll('.seat.selected').forEach(s => {
                s.classList.remove('selected');
                s.classList.add('available');
                s.querySelector('input').checked = false;
            });
            selectedSeats = [];
            updateSelectedDisplay();
        }
        
        function updateSelectedDisplay() {
            const display = document.getElementById('selectedSeatsDisplay');
            const tags = document.getElementById('selectedSeatTags');
            
            if (selectedSeats.length > 0) {
                display.style.display = 'flex';
                tags.innerHTML = selectedSeats.map(s => 
                    `<span class="selected-seat-tag">Kursi ${s.number}</span>`
                ).join('');
            } else {
                display.style.display = 'none';
            }
        }
        
        function updatePassengerForms() {
            const container = document.getElementById('passengerForms');
            let html = '';
            
            for (let i = 1; i <= passengerCount; i++) {
                const seatInfo = selectedSeats[i-1] ? `Kursi ${selectedSeats[i-1].number}` : 'Belum dipilih';
                html += `
                    <div class="passenger-card">
                        <div class="passenger-header">
                            <div class="passenger-badge">${i}</div>
                            <h4 class="passenger-title">Penumpang ${i}</h4>
                            <span class="passenger-seat"><i class="fa-solid fa-chair me-1"></i>${seatInfo}</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="passenger_name_${i}" class="form-control" placeholder="Sesuai KTP/SIM" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">No. Identitas (KTP/SIM)</label>
                                <input type="text" name="passenger_identity_${i}" class="form-control" placeholder="Opsional">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">No. Telepon</label>
                                <input type="tel" name="passenger_phone_${i}" class="form-control" placeholder="08xxxxxxxxxx">
                            </div>
                        </div>
                    </div>
                `;
            }
            
            container.innerHTML = html;
        }
        
        function selectPayment(el) {
            document.querySelectorAll('.payment-option').forEach(p => p.classList.remove('selected'));
            el.classList.add('selected');
            el.querySelector('input').checked = true;
        }
        
        // Init
        updatePassengerForms();
    </script>
</body>
</html>
