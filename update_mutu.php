<?php
require_once 'includes/config.php';

$mutu_data = [
    ['B0', 715000],
    ['K-125', 720000],
    ['K-150', 735000],
    ['K-175', 750000],
    ['K-200', 770000],
    ['K-225', 780000],
    ['K-250', 800000],
    ['K-275', 810000],
    ['K-300', 820000],
    ['K-350', 850000],
    ['K-400', 875000],
    ['K-450', 895000],
    ['K-475', 950000],
    ['K-500', 995000]
];

try {
    // Nonaktifkan produk lama
    $conn->query("UPDATE products SET is_active = 0");

    $stmt_check = $conn->prepare("SELECT * FROM products WHERE kode = ?");
    $stmt_insert = $conn->prepare("INSERT INTO products (kode, nama, harga_per_m3, is_active) VALUES (?, ?, ?, 1)");
    $stmt_update = $conn->prepare("UPDATE products SET harga_per_m3 = ?, is_active = 1 WHERE kode = ?");

    foreach ($mutu_data as $item) {
        $kode = $item[0];
        $nama = "Beton " . $kode;
        $harga = $item[1];

        $stmt_check->bind_param("s", $kode);
        $stmt_check->execute();
        $res = $stmt_check->get_result();
        
        if ($res->num_rows > 0) {
            $stmt_update->bind_param("ds", $harga, $kode);
            $stmt_update->execute();
        } else {
            // Attempt insert with nama if it exists
            $stmt_insert->bind_param("ssd", $kode, $nama, $harga);
            $stmt_insert->execute();
        }
    }
    
    echo "<div style='font-family:sans-serif;text-align:center;margin-top:50px;'>";
    echo "<h1 style='color:green;'>✅ Berhasil!</h1>";
    echo "<p>Semua spesifikasi mutu beton terbaru (B0 sampai K-500) beserta harganya telah berhasil diupdate ke database.</p>";
    echo "<a href='penjualan/buat.php' style='display:inline-block;padding:10px 20px;background:#0a4b49;color:#fff;text-decoration:none;border-radius:5px;'>Kembali ke Form Pesanan</a>";
    echo "</div>";

} catch (Exception $e) {
    // Jika gagal (misalnya kolom nama tidak ada), coba mode fallback
    try {
        $conn->query("UPDATE products SET is_active = 0");
        $stmt_check = $conn->prepare("SELECT * FROM products WHERE kode = ?");
        $stmt_insert2 = $conn->prepare("INSERT INTO products (kode, harga_per_m3, is_active) VALUES (?, ?, 1)");
        $stmt_update = $conn->prepare("UPDATE products SET harga_per_m3 = ?, is_active = 1 WHERE kode = ?");
        
        foreach ($mutu_data as $item) {
            $kode = $item[0];
            $harga = $item[1];

            $stmt_check->bind_param("s", $kode);
            $stmt_check->execute();
            $res = $stmt_check->get_result();
            
            if ($res->num_rows > 0) {
                $stmt_update->bind_param("ds", $harga, $kode);
                $stmt_update->execute();
            } else {
                $stmt_insert2->bind_param("sd", $kode, $harga);
                $stmt_insert2->execute();
            }
        }
        echo "<div style='font-family:sans-serif;text-align:center;margin-top:50px;'>";
        echo "<h1 style='color:green;'>✅ Berhasil (Fallback Mode)!</h1>";
        echo "<p>Semua spesifikasi mutu beton terbaru telah berhasil ditambahkan.</p>";
        echo "<a href='penjualan/buat.php' style='display:inline-block;padding:10px 20px;background:#0a4b49;color:#fff;text-decoration:none;border-radius:5px;'>Kembali ke Form Pesanan</a>";
        echo "</div>";
    } catch (Exception $err) {
        echo "<div style='font-family:sans-serif;padding:20px;background:#fee2e2;color:#991b1b;'>";
        echo "<h1>❌ Terjadi Kesalahan Database</h1>";
        echo "<p>Pesan Error: " . htmlspecialchars($err->getMessage()) . "</p>";
        echo "</div>";
    }
}
?>
