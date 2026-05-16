<?php
require_once '../includes/config.php';
requireLogin();

$pesanan_id = isset($_GET['pesanan_id']) ? (int)$_GET['pesanan_id'] : 0;

// Ambil data pesanan
$pesanan = $conn->query("SELECT p.*, a.name as admin_buat 
                         FROM pesanan p 
                         LEFT JOIN admins a ON p.created_by = a.id 
                         WHERE p.id = $pesanan_id")->fetch_assoc();

if (!$pesanan) {
    die("Data pesanan tidak ditemukan.");
}

// Ambil semua material yang keluar untuk pesanan ini
$materials = $conn->query("SELECT pm.*, m.nama as nama_material, m.satuan 
                           FROM permintaan_material pm 
                           JOIN materials m ON pm.material_id = m.id 
                           WHERE pm.pesanan_id = $pesanan_id");

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Pengeluaran Material - <?= $pesanan['no_invoice'] ?></title>
    <style>
        body { font-family: 'Inter', sans-serif; color: #333; margin: 0; padding: 40px; background: #f0f0f0; }
        .invoice-box { max-width: 800px; margin: auto; padding: 40px; border: 1px solid #eee; background: #fff; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); border-radius: 8px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #2e7d32; padding-bottom: 20px; margin-bottom: 20px; }
        .company-info h2 { color: #2e7d32; margin: 0; font-size: 24px; font-weight: 800; }
        .company-info p { margin: 5px 0; font-size: 12px; color: #666; }
        .invoice-title { text-align: right; }
        .invoice-title h1 { margin: 0; color: #333; font-size: 20px; text-transform: uppercase; }
        .invoice-title p { margin: 5px 0; font-weight: bold; color: #2e7d32; }
        .details { display: flex; justify-content: space-between; margin-bottom: 30px; font-size: 14px; }
        .details-col { flex: 1; }
        table { width: 100%; line-height: inherit; text-align: left; border-collapse: collapse; }
        table th { background: #f8f9fa; padding: 12px; border-bottom: 2px solid #eee; font-size: 13px; text-transform: uppercase; }
        table td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; }
        .total-row { background: #f1f8e9; font-weight: bold; }
        .footer { margin-top: 50px; display: flex; justify-content: space-between; }
        .ttd { text-align: center; width: 200px; }
        .ttd-space { height: 80px; }
        .ttd-name { font-weight: bold; border-top: 1px solid #333; padding-top: 5px; }
        .no-print { margin-bottom: 20px; text-align: center; }
        .btn { padding: 10px 20px; background: #2e7d32; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold; cursor: pointer; border: none; }
        @media print {
            body { background: #fff; padding: 0; }
            .invoice-box { box-shadow: none; border: none; max-width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn">🖨 Cetak Bukti Pengeluaran</button>
        <a href="index.php" class="btn" style="background:#666;">Kembali</a>
    </div>

    <div class="invoice-box">
        <div class="header" style="display:flex; justify-content:space-between; align-items:center; border-bottom:none; margin-bottom:30px;">
            <div style="flex:0 0 20%;">
                <img src="<?= BASE_URL ?>/assets/images/logo.png" style="width:140px; height:auto;" alt="Logo" onerror="this.src='../assets/images/logo-beton.png'">
            </div>
            <div style="flex:1; text-align:center;">
                <h2 style="color:#0a4b49; font-style:italic; font-size:18px; margin:0; font-weight:800;">PT PRAMBANAN BETON INDONESIA</h2>
                <p style="font-size:11px; margin:2px 0; color:#333;">Jalan Moh Seruji No 331</p>
                <p style="font-size:11px; margin:2px 0; color:#333;">Kel. Gambirono Kec. Bangsalsari, Kabupaten Jember</p>
            </div>
            <div style="flex:0 0 20%; text-align:right;">
                <h1 style="margin:0; color:#333; font-size:16px; text-transform:uppercase; font-weight:900;">BUKTI PENGELUARAN</h1>
                <p style="margin:2px 0; font-weight:bold; color:#2e7d32; font-size:14px;">Ref: <?= $pesanan['no_invoice'] ?></p>
            </div>
        </div>

        <div style="border-top:2px solid #2e7d32; margin-bottom:20px;"></div>

        <div class="details">
            <div class="details-col">
                <strong>Pelanggan:</strong><br>
                <?= htmlspecialchars($pesanan['nama_pelanggan'] ?: '-') ?><br>
                <strong>Proyek:</strong><br>
                <?= htmlspecialchars($pesanan['nama_proyek'] ?: '-') ?>
            </div>
            <div class="details-col" style="text-align: right;">
                <strong>Tanggal Keluar:</strong> <?= date('d/m/Y', strtotime($pesanan['tanggal'])) ?><br>
                <strong>Tipe Beton:</strong> <?= htmlspecialchars($pesanan['tipe_beton']) ?><br>
                <strong>Volume:</strong> <?= number_format($pesanan['volume'], 2) ?> m&sup3;
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Material</th>
                    <th style="text-align: right;">Jumlah Keluar</th>
                    <th style="width: 100px;">Satuan</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1;
                while($m = $materials->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><strong><?= htmlspecialchars($m['nama_material']) ?></strong></td>
                    <td style="text-align: right; font-weight: 700;"><?= number_format($m['jumlah'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($m['satuan']) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div style="margin-top: 20px; font-size: 12px; font-style: italic; color: #666;">
            * Material dikurangi otomatis dari stok persediaan berdasarkan standar komposisi mutu beton.
        </div>

        <div class="footer">
            <div class="ttd">
                <p>Dikeluarkan Oleh,</p>
                <div class="ttd-space"></div>
                <div class="ttd-name">Admin Gudang</div>
            </div>
            <div class="ttd">
                <p>Diterima Oleh,</p>
                <div class="ttd-space"></div>
                <div class="ttd-name">Driver / Produksi</div>
            </div>
        </div>
    </div>

</body>
</html>
