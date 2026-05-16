<?php
require_once '../includes/config.php';
requireLogin();

// Handler POST untuk ganti password sendiri (self-service)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/dashboard.php");
    exit();
}

$uid       = (int)$_SESSION['admin_id'];
$pw_lama   = $_POST['pw_lama']    ?? '';
$pw_baru   = $_POST['pw_baru']    ?? '';
$pw_konfirm = $_POST['pw_konfirm'] ?? '';

// Ambil data user saat ini
$stmt = $conn->prepare("SELECT password FROM admins WHERE id=?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Validasi
if (!$user || !password_verify($pw_lama, $user['password'])) {
    // Coba plain text fallback
    if (!$user || $pw_lama !== $user['password']) {
        $_SESSION['pw_error'] = "Password saat ini salah.";
        header("Location: " . BASE_URL . "/dashboard.php");
        exit();
    }
}

if (strlen($pw_baru) < 6) {
    $_SESSION['pw_error'] = "Password baru minimal 6 karakter.";
    header("Location: " . BASE_URL . "/dashboard.php");
    exit();
}

if ($pw_baru !== $pw_konfirm) {
    $_SESSION['pw_error'] = "Konfirmasi password tidak cocok.";
    header("Location: " . BASE_URL . "/dashboard.php");
    exit();
}

// Simpan password baru
$hashed = password_hash($pw_baru, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE admins SET password=? WHERE id=?");
$stmt->bind_param("si", $hashed, $uid);
$stmt->execute();
$stmt->close();

// Log aktivitas
$log = "Mengganti password akun sendiri";
$sl = $conn->prepare("INSERT INTO activity_logs (admin_id, admin_name, action) VALUES (?,?,?)");
$sl->bind_param("iss", $_SESSION['admin_id'], $_SESSION['admin_name'], $log);
$sl->execute();
$sl->close();

$_SESSION['pw_success'] = "Password berhasil diperbarui!";

// Redirect ke halaman asal
$referer = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . "/dashboard.php");
header("Location: $referer");
exit();
