<?php
// =============================================
// KONFIGURASI DATABASE - INTERNAL SYSTEM
// =============================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'prambanan_beton');

// =============================================
// INFORMASI PERUSAHAAN
// =============================================
define('SITE_NAME', 'PT Prambanan Beton');
define('SITE_TAGLINE', 'Solusi Beton Berkualitas untuk Proyek Konstruksi Anda');
define('SITE_PHONE', '0852-5998-2223');
define('SITE_EMAIL', 'PrambananID@gmail.com');
define('SITE_ADDRESS', 'Jl. Raya Lumajang - Jember, Gambirono Krajan, Gambirono, Kec. Bangsalsari, Kabupaten Jember, Jawa Timur 68154');
define('SITE_IG', 'PrambananBeton.ID');
define('SITE_TIKTOK', 'PrambananBeton.ID');
define('SITE_FB', 'PrambananBeton.ID');
define('SITE_MAPS_LAT', '-8.2845');
define('SITE_MAPS_LNG', '113.6043');

session_start();

// Koneksi database
try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset("utf8");
    
    date_default_timezone_set('Asia/Jakarta');
    $conn->query("SET time_zone = '+07:00'");
    
} catch (mysqli_sql_exception $e) {
    die("<div style='font-family:sans-serif;padding:40px;background:#fee2e2;color:#991b1b;border-radius:8px;margin:20px;text-align:center;'>
        <h2 style='margin-top:0;'>⚠ Koneksi Database Gagal</h2>
        <p>Gagal terhubung ke database. Beberapa kemungkinan penyebab:</p>
        <ul style='text-align:left; display:inline-block;'>
            <li>MySQL belum dinyalakan di Laragon/XAMPP.</li>
            <li>Database <strong>" . DB_NAME . "</strong> belum dibuat atau belum di-import dari <code>database.sql</code>.</li>
            <li>Password untuk user <strong>" . DB_USER . "</strong> salah.</li>
        </ul>
        <div style='background:#fef2f2; padding:15px; border-left:4px solid #dc2626; margin:20px 0; font-family:monospace; font-size:14px; text-align:left;'>
            <strong>Pesan Error Asli:</strong> " . $e->getMessage() . "
        </div>
        <p style='color:#666;'><em>Silakan periksa file <code>prambanan-beton-internal/includes/config.php</code> untuk mengatur kredensial database.</em></p>
    </div>");
}

// =============================================
// HELPER FUNCTIONS
// =============================================
// Menentukan BASE_URL otomatis
$is_https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$dir_path = str_replace('\\', '/', __DIR__);
// Point BASE_URL to the internal root (parent of includes/)
$internal_root = str_replace('\\', '/', dirname(__DIR__));
$base_path = str_replace($doc_root, '', $internal_root);
define('BASE_URL', $base_path);

function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "/login.php");
        exit();
    }
}

function hasRoleAccess($module) {
    if (!isset($_SESSION['admin_role'])) return false;
    $role = $_SESSION['admin_role'];
    
    // Superadmin dan Admin punya akses ke semua fitur
    if ($role === 'superadmin' || $role === 'admin') return true;
    
    // Semua role bisa akses dashboard KECUALI marketing
    if ($module === 'dashboard' && $role !== 'marketing') return true;
    
    // Role khusus
    if ($module === 'persediaan' && $role === 'gudang') return true;
    if ($module === 'marketing' && $role === 'marketing') return true;
    
    return false;
}

function requireRoleAccess($module) {
    if (!hasRoleAccess($module)) {
        $_SESSION['error_msg'] = "Akses ditolak! Anda tidak memiliki izin untuk fitur " . htmlspecialchars($module) . ".";
        header("Location: " . BASE_URL . "/dashboard.php");
        exit();
    }
}

function redirect($url) {
    if (strpos($url, '/') === 0) {
        // For internal system, redirect relative to BASE_URL
        // Remove any leading path segments and keep the internal path
        $clean_url = $url;
        // If path starts with /admin/, strip it for internal routing
        if (preg_match('#^(/[a-zA-Z0-9_-]+)*/admin/(.*)$#', $url, $m)) {
            $clean_url = '/' . $m[2];
        }
        $url = BASE_URL . $clean_url;
    }
    header("Location: $url");
    exit();
}

function sanitize($conn, $data) {
    return $conn->real_escape_string(trim($data));
}

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    if ($time < 60) return $time . ' detik lalu';
    if ($time < 3600) return floor($time/60) . ' menit lalu';
    if ($time < 86400) return floor($time/3600) . ' jam lalu';
    return floor($time/86400) . ' hari lalu';
}

// AUTO-UPDATE DATABASE SEKALI JALAN
if (!isset($_SESSION['mutu_updated_v3'])) {
    try {
        $mutu_data = [
            ['B0', 715000], ['K-125', 720000], ['K-150', 735000], ['K-175', 750000],
            ['K-200', 770000], ['K-225', 780000], ['K-250', 800000], ['K-275', 810000],
            ['K-300', 820000], ['K-350', 850000], ['K-400', 875000], ['K-450', 895000],
            ['K-475', 950000], ['K-500', 995000]
        ];

        // Matikan produk lama
        $conn->query("UPDATE products SET is_active = 0");
        
        // Cek apakah ada kolom 'nama'
        $has_nama = $conn->query("SHOW COLUMNS FROM products LIKE 'nama'")->num_rows > 0;

        foreach ($mutu_data as $item) {
            $kode = $conn->real_escape_string($item[0]);
            $harga = $item[1];
            $nama = $conn->real_escape_string("Beton " . $item[0]);
            
            $res = $conn->query("SELECT * FROM products WHERE kode = '$kode'");
            if ($res && $res->num_rows > 0) {
                // Update
                $conn->query("UPDATE products SET harga_per_m3 = $harga, is_active = 1 WHERE kode = '$kode'");
            } else {
                // Insert
                if ($has_nama) {
                    $conn->query("INSERT INTO products (kode, nama, harga_per_m3, is_active) VALUES ('$kode', '$nama', $harga, 1)");
                } else {
                    $conn->query("INSERT INTO products (kode, harga_per_m3, is_active) VALUES ('$kode', $harga, 1)");
                }
            }
        }
        $_SESSION['mutu_updated_v3'] = true;
    } catch (Exception $e) {
        // Abaikan error agar tidak merusak halaman utama
    }
}
?>
