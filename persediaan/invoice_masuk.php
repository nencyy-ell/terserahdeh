<?php
require_once '../includes/config.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data stok masuk
$masuk = $conn->query("SELECT s.*, a.name as admin_name 
                       FROM stok_masuk s 
                       LEFT JOIN admins a ON s.admin_id = a.id 
                       WHERE s.id = $id")->fetch_assoc();

if (!$masuk) {
    die("Data tidak ditemukan.");
}

$items = $conn->query("SELECT si.*, m.nama as nama_material, m.satuan 
                       FROM stok_masuk_items si 
                       JOIN materials m ON si.material_id = m.id 
                       WHERE si.stok_masuk_id = $id");

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Penerimaan Material - <?= $masuk['no_surat_jalan'] ?></title>
    <style>
        body { font-family: 'Inter', sans-serif; color: #333; margin: 0; padding: 40px; background: #f0f0f0; }
        .invoice-box { max-width: 800px; margin: auto; padding: 40px; border: 1px solid #eee; background: #fff; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); border-radius: 8px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #f39c12; padding-bottom: 20px; margin-bottom: 20px; }
        .company-info h2 { color: #123f27; margin: 0; font-size: 24px; font-weight: 800; }
        .company-info p { margin: 5px 0; font-size: 12px; color: #666; }
        .invoice-title { text-align: right; }
        .invoice-title h1 { margin: 0; color: #333; font-size: 20px; text-transform: uppercase; }
        .invoice-title p { margin: 5px 0; font-weight: bold; color: #f39c12; }
        .details { display: flex; justify-content: space-between; margin-bottom: 30px; font-size: 14px; }
        .details-col { flex: 1; }
        table { width: 100%; line-height: inherit; text-align: left; border-collapse: collapse; }
        table th { background: #f8f9fa; padding: 12px; border-bottom: 2px solid #eee; font-size: 13px; text-transform: uppercase; }
        table td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; }
        .footer { margin-top: 50px; display: flex; justify-content: space-between; }
        .ttd { text-align: center; width: 200px; }
        .ttd-space { height: 80px; }
        .ttd-name { font-weight: bold; border-top: 1px solid #333; padding-top: 5px; }
        .no-print { margin-bottom: 20px; text-align: center; }
        .btn { padding: 10px 20px; background: #123f27; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold; cursor: pointer; border: none; }
        @media print {
            body { background: #fff; padding: 0; }
            .invoice-box { box-shadow: none; border: none; max-width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn">🖨 Cetak Bukti Penerimaan</button>
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
                <h1 style="margin:0; color:#333; font-size:16px; text-transform:uppercase; font-weight:900;">BUKTI PENERIMAAN</h1>
                <p style="margin:2px 0; font-weight:bold; color:#f39c12; font-size:14px;">SJ: <?= $masuk['no_surat_jalan'] ?></p>
            </div>
        </div>

        <div style="border-top:2px solid #f39c12; margin-bottom:20px;"></div>

        <div class="details">
            <div class="details-col">
                <strong>Supplier / Vendor:</strong><br>
                <?= htmlspecialchars($masuk['supplier']) ?>
            </div>
            <div class="details-col" style="text-align: right;">
                <strong>Tanggal Terima:</strong> <?= date('d/m/Y', strtotime($masuk['tanggal'])) ?><br>
                <strong>Admin Penerima:</strong> <?= htmlspecialchars($masuk['admin_name']) ?>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Material</th>
                    <th style="text-align: right;">Jumlah Masuk</th>
                    <th style="width: 100px;">Satuan</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1;
                while($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><strong><?= htmlspecialchars($item['nama_material']) ?></strong></td>
                    <td style="text-align: right; font-weight: 700;"><?= number_format($item['jumlah'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($item['satuan']) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div style="margin-top: 20px; font-size: 12px; font-style: italic; color: #666;">
            * Data material telah ditambahkan ke stok persediaan secara sistem.
        </div>

        <div class="footer">
            <div class="ttd">
                <p>Diterima Oleh,</p>
                <div class="ttd-space"></div>
                <div class="ttd-name"><?= htmlspecialchars($masuk['admin_name']) ?></div>
                <p style="font-size:10px; margin-top:2px;">Admin Gudang</p>
            </div>
            <div class="ttd">
                <p>Diserahkan Oleh,</p>
                <div class="ttd-space"></div>
                <div class="ttd-name">Sopir / Supplier</div>
            </div>
        </div>
    </div>

</body>
</html>
