<?php

function setFlash($type, $message) {
    // Simpan pesan ke session dengan key 'flash'
    $_SESSION['flash'] = [
        'type' => $type,        // success atau error
        'message' => $message   // isi pesan
    ];
}

/**
 * Ambil pesan flash dari session
 * Setelah diambil, pesan akan dihapus dari session
 * 
 * @return array|null - Array berisi type dan message, atau null jika tidak ada
 */
function getFlash() {
    // Cek apakah ada flash message di session
    if (isset($_SESSION['flash'])) {
        // Simpan flash message ke variable
        $flash = $_SESSION['flash'];
        
        // Hapus flash message dari session
        // Agar tidak muncul lagi saat refresh
        unset($_SESSION['flash']);
        
        // Kembalikan flash message
        return $flash;
    }
    
    // Jika tidak ada flash message, return null
    return null;
}

/**
 * Tampilkan flash message dalam bentuk HTML
 * Fungsi ini langsung echo HTML, tidak return
 */
function showFlash() {
    // Ambil flash message
    $flash = getFlash();
    
    // Jika ada flash message
    if ($flash) {
        // Tentukan class CSS berdasarkan tipe
        if ($flash['type'] === 'success') {
            $class = 'alert-success';  // Hijau untuk sukses
        } else {
            $class = 'alert-danger';   // Merah untuk error
        }
        
        // Tampilkan HTML alert Bootstrap
        echo "<div class='alert {$class} alert-dismissible fade show' role='alert'>
                {$flash['message']}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
    }
}

// ============================================
// FUNGSI UPLOAD FILE
// ============================================

/**
 * Upload file ke server
 * 
 * @param array $file - Array $_FILES['nama_input']
 * @param string $directory - Folder tujuan upload (contoh: 'admin/uploads/buses')
 * @param array $allowedTypes - Ekstensi file yang diperbolehkan
 * @return array - Array berisi success (true/false), filename, path, atau message error
 */
function uploadFile($file, $directory, $allowedTypes = ['jpg', 'jpeg', 'png', 'webp']) {
    // Cek apakah ada error saat upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false, 
            'message' => 'Error saat upload file'
        ];
    }
    
    // Ambil ekstensi file (jpg, png, dll)
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Cek apakah ekstensi file diperbolehkan
    if (!in_array($extension, $allowedTypes)) {
        return [
            'success' => false, 
            'message' => 'Tipe file tidak diperbolehkan. Hanya: ' . implode(', ', $allowedTypes)
        ];
    }
    
    // Buat nama file unik agar tidak bentrok
    // Format: uniqueid_timestamp.extension
    // Contoh: 5f3a2b1c_1640000000.jpg
    $filename = uniqid() . '_' . time() . '.' . $extension;
    
    // Path lengkap untuk menyimpan file
    $uploadPath = $directory . '/' . $filename;
    
    // Pindahkan file dari temporary ke folder tujuan
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Jika berhasil
        return [
            'success' => true, 
            'filename' => $filename,    // Nama file saja
            'path' => $uploadPath       // Path lengkap
        ];
    }
    
    // Jika gagal pindahkan file
    return [
        'success' => false, 
        'message' => 'Gagal memindahkan file'
    ];
}

// ============================================
// FUNGSI FORMAT MATA UANG
// ============================================

/**
 * Format angka menjadi format Rupiah
 * 
 * @param int|float $amount - Jumlah uang
 * @return string - String format Rupiah (contoh: Rp 100.000)
 */
function formatRupiah($amount) {
    // number_format(angka, desimal, pemisah_desimal, pemisah_ribuan)
    // Contoh: 100000 -> 100.000
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// ============================================
// FUNGSI FORMAT TANGGAL
// ============================================

/**
 * Format tanggal dari database menjadi format yang lebih mudah dibaca
 * 
 * @param string $date - Tanggal dari database (format: Y-m-d H:i:s)
 * @param string $format - Format output yang diinginkan
 * @return string - Tanggal terformat
 */
function formatDate($date, $format = 'd M Y H:i') {
    // strtotime() mengubah string tanggal jadi timestamp
    // date() mengubah timestamp jadi format yang diinginkan
    // Contoh: '2024-01-15 10:30:00' -> '15 Jan 2024 10:30'
    return date($format, strtotime($date));
}

// ============================================
// FUNGSI LABEL TIPE BUS
// ============================================

/**
 * Ubah kode tipe bus menjadi label yang mudah dibaca
 * 
 * @param string $type - Kode tipe bus dari database
 * @return string - Label tipe bus dalam Bahasa Indonesia
 */
function getBusTypeLabel($type) {
    // Array mapping dari kode ke label
    $labels = [
        'big_bus' => 'Bus Pariwisata',
        'mini_bus' => 'Mini Bus',
        'hiace' => 'Toyota Hiace',
    ];
    
    // Cek apakah tipe ada di array
    if (isset($labels[$type])) {
        return $labels[$type];
    } else {
        // Jika tidak ada, return type aslinya
        return $type;
    }
}

// ============================================
// FUNGSI STATUS BADGE
// ============================================

/**
 * Buat HTML badge untuk status
 * Badge adalah label berwarna untuk menampilkan status
 * 
 * @param string $status - Status dari database
 * @return string - HTML badge Bootstrap
 */
function getStatusBadge($status) {
    // Array mapping status ke HTML badge
    $badges = [
        'active' => '<span class="badge bg-success">Active</span>',
        'inactive' => '<span class="badge bg-secondary">Inactive</span>',
        'maintenance' => '<span class="badge bg-warning">Maintenance</span>',
        'pending' => '<span class="badge bg-warning">Pending</span>',
        'paid' => '<span class="badge bg-success">Paid</span>',
        'success' => '<span class="badge bg-success">Success</span>',
        'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
        'failed' => '<span class="badge bg-danger">Failed</span>',
        'expired' => '<span class="badge bg-secondary">Expired</span>',
        'available' => '<span class="badge bg-success">Available</span>',
        'finished' => '<span class="badge bg-info">Finished</span>',
    ];
    
    // Cek apakah status ada di array
    if (isset($badges[$status])) {
        return $badges[$status];
    } else {
        // Jika tidak ada, buat badge default
        return "<span class='badge bg-secondary'>{$status}</span>";
    }
}

// ============================================
// FUNGSI URL UPLOAD
// ============================================

/**
 * Buat URL lengkap untuk file yang diupload
 * Fungsi ini mendeteksi dari folder mana file dipanggil
 * 
 * @param string $filename - Nama file
 * @param string $folder - Folder dalam uploads (default: 'buses')
 * @return string|null - URL lengkap atau null jika filename kosong
 */
function getUploadUrl($filename, $folder = 'buses') {
    // Jika filename kosong, return null
    if (empty($filename)) {
        return null;
    }
    
    // Ambil path script yang sedang berjalan
    // Contoh: /admin/dashboard.php atau /user/bookings.php atau /index.php
    $scriptPath = $_SERVER['SCRIPT_NAME'];
    
    // Variable untuk menyimpan base path
    $basePath = '';
    
    // Deteksi dari folder mana file dipanggil
    if (strpos($scriptPath, '/admin/') !== false) {
        // Jika dipanggil dari folder admin, base path kosong
        $basePath = '';
    } else if (strpos($scriptPath, '/user/') !== false) {
        // Jika dipanggil dari folder user, naik 1 level ke admin
        $basePath = '../admin/';
    } else {
        // Jika dipanggil dari root, masuk ke folder admin
        $basePath = 'admin/';
    }
    
    // Cek apakah filename sudah punya path lengkap
    if (strpos($filename, 'uploads/') === 0) {
        // Jika sudah ada 'uploads/', langsung gabung dengan base path
        return $basePath . $filename;
    }
    
    // Jika belum, buat path lengkap
    return $basePath . 'uploads/' . $folder . '/' . $filename;
}

// ============================================
// FUNGSI GAMBAR ARMADA
// ============================================

/**
 * Ambil URL gambar armada dengan fallback ke default
 * 
 * @param string $imageUrl - URL gambar dari database
 * @param string $size - Ukuran gambar (tidak dipakai, untuk kompatibilitas)
 * @return string - URL gambar atau default dari Unsplash
 */
function getArmadaImage($imageUrl, $size = 'medium') {
    // URL gambar default dari Unsplash (gambar bus)
    $default = 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=400&h=300&fit=crop';
    
    // Jika imageUrl kosong, pakai default
    if (empty($imageUrl)) {
        return $default;
    }
    
    // Buat URL lengkap untuk file upload
    $uploadPath = getUploadUrl($imageUrl, 'buses');
    
    // Jika uploadPath ada, return. Jika tidak, return default
    if ($uploadPath) {
        return $uploadPath;
    } else {
        return $default;
    }
}

/**
 * Ambil URL banner armada untuk homepage
 * Banner biasanya lebih besar (aspect ratio 16:9)
 * 
 * @param string $imageBanner - URL banner dari database
 * @param string $imageUrl - URL gambar biasa (fallback)
 * @return string - URL banner atau default
 */
function getArmadaBanner($imageBanner, $imageUrl = null) {
    // URL default untuk banner (lebih besar)
    $default = 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=800&h=450&fit=crop';
    
    // Prioritas: banner -> image_url -> default
    
    // Cek banner dulu
    if (!empty($imageBanner)) {
        return getUploadUrl($imageBanner, 'buses');
    }
    
    // Jika banner kosong, cek image_url
    if (!empty($imageUrl)) {
        return getUploadUrl($imageUrl, 'buses');
    }
    
    // Jika semua kosong, pakai default
    return $default;
}

/**
 * ============================================
 * CATATAN UNTUK PEMULA
 * ============================================
 * 
 * 1. Kenapa Pakai Fungsi?
 *    - Agar code tidak diulang-ulang
 *    - Lebih mudah maintenance
 *    - Jika ada perubahan, cukup ubah di 1 tempat
 * 
 * 2. Parameter vs Argument:
 *    - Parameter: variable di definisi fungsi ($type, $message)
 *    - Argument: nilai yang dikirim saat panggil fungsi ('success', 'Data berhasil disimpan')
 * 
 * 3. Return vs Echo:
 *    - Return: mengembalikan nilai untuk diproses lagi
 *    - Echo: langsung tampilkan ke browser
 * 
 * 4. Cara Pakai Fungsi-fungsi Ini:
 *    
 *    // Flash message
 *    setFlash('success', 'Data berhasil disimpan!');
 *    showFlash();  // Tampilkan pesan
 *    
 *    // Format rupiah
 *    $harga = 150000;
 *    echo formatRupiah($harga);  // Output: Rp 150.000
 *    
 *    // Format tanggal
 *    $tanggal = '2024-01-15 10:30:00';
 *    echo formatDate($tanggal);  // Output: 15 Jan 2024 10:30
 *    
 *    // Upload file
 *    $result = uploadFile($_FILES['foto'], 'admin/uploads/buses');
 *    if ($result['success']) {
 *        echo "File berhasil diupload: " . $result['filename'];
 *    }
 */
