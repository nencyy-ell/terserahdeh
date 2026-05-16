<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if (!isLoggedIn() || !in_array($_SESSION['admin_role'], ['gudang', 'superadmin'])) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit;
}

$nama = sanitize($conn, $_POST['nama'] ?? '');
$satuan = sanitize($conn, $_POST['satuan'] ?? '');

if (!$nama || !$satuan) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

$sql = "INSERT INTO materials (nama, satuan, stok_tersedia, stok_minimum, harga_terakhir) VALUES ('$nama', '$satuan', 0, 0, 0)";
if ($conn->query($sql)) {
    $new_id = $conn->insert_id;
    
    // Log
    $action = "Menambah jenis material baru via AJAX: $nama";
    $stmt_log = $conn->prepare("INSERT INTO activity_logs (admin_id, admin_name, action) VALUES (?,?,?)");
    $stmt_log->bind_param("iss", $_SESSION['admin_id'], $_SESSION['admin_name'], $action);
    $stmt_log->execute();
    
    echo json_encode(['success' => true, 'id' => $new_id]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
