<?php
require_once '../includes/config.php';
requireLogin();
$currentPage = 'persediaan';

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('/persediaan/index.php');

$pm = $conn->query("SELECT pm.*, m.nama as nama_material, m.satuan, m.harga_terakhir 
                    FROM permintaan_material pm 
                    JOIN materials m ON pm.material_id = m.id 
                    WHERE pm.id = $id")->fetch_assoc();

if (!$pm) redirect('/persediaan/index.php');

$no_dokumen = 'PM-' . date('Y') . '-' . str_pad($id, 3, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Permintaan Material <?= $no_dokumen ?> - Sistem Prambanan</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/admin.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
    @media print {
        @page { size: A4; margin: 20mm; }
        html, body {
            background: #fff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-size: 12px;
            height: auto !important;
            min-height: auto !important;
            overflow: visible !important;
        }
        .sidebar, .no-print { display: none !important; }
        .admin-layout, .main-content { 
            display: block !important; 
            margin: 0 !important; 
            padding: 0 !important; 
            width: 100% !important; 
            height: auto !important;
            min-height: auto !important;
            overflow: visible !important;
        }
        .invoice-wrap { max-width: 780px; margin: auto; padding: 30px; background: #fff !important; box-shadow: none !important; border: none !important; }
        .invoice-header { border-bottom: 2px solid #1a4731 !important; }
        .doc-badge { background: #1a4731 !important; color: #fff !important; }
        .invoice-table { width: 100%; border-collapse: collapse; }
        .invoice-table thead th { background: #1a4731 !important; color: #fff !important; padding: 10px; }
        .invoice-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .status-box { background: #f0fdf4 !important; border: 1px solid #bbf7d0 !important; }
        .signature-area { page-break-inside: avoid; }
    }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <!-- HEADER BAR -->
        <div class="page-header-row no-print">
            <div style="display:flex;align-items:center;gap:12px;">
                <a href="index.php" class="btn-back no-print"><i class="fas fa-arrow-left"></i> Kembali ke Persediaan</a>
            </div>
            <div>
                <h1 style="font-size:22px;font-weight:900;color:var(--green-dark);">Bukti Permintaan Material</h1>
                <p style="color:#aaa;font-size:13px;"><?= $no_dokumen ?></p>
            </div>
            <button onclick="window.print()" class="btn btn-green no-print">
                <i class="fas fa-print"></i> Cetak PDF
            </button>
        </div>

        <!-- DOKUMEN CETAK -->
        <div class="invoice-wrap">
            <!-- KOP -->
            <div class="invoice-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:10px; border-bottom:none;">
                <div style="flex:0 0 25%;">
                    <img src="<?= BASE_URL ?>/assets/images/logo.png" style="width:160px; height:auto;" alt="Logo" onerror="this.src='../assets/images/logo-beton.png'">
                </div>
                <div style="flex:1; text-align:center;">
                    <h2 style="color:#0a4b49; font-style:italic; font-size:20px; margin:0; font-weight:800; white-space:nowrap;">PT. PRAMBANAN BETON INDONESIA</h2>
                    <p style="font-size:12px; margin:2px 0; color:#000;">Jl. Moch Seruji No. 331 Dusun Krajan</p>
                    <p style="font-size:12px; margin:2px 0; color:#000;">Desa Gambirono, Kecamatan Bangsalsari</p>
                    <p style="font-size:12px; margin:2px 0; color:#000;">Kab. Jember Jawa Timur 68154</p>
                    <p style="font-size:12px; margin:2px 0; color:#000;">Indonesia</p>
                </div>
                <div style="flex:0 0 25%; text-align:right;">
                    <!-- empty space for balance -->
                </div>
            </div>

            <div style="text-align:right; margin-bottom:20px;">
                <h3 style="color:#0a4b49; text-decoration:underline; font-size:18px; margin:0;">PERMINTAAN MATERIAL</h3>
                <h3 style="color:#0a4b49; font-style:italic; font-size:18px; margin:0;">MATERIAL REQUEST</h3>
            </div>

            <div style="display:flex; justify-content:space-between; gap:20px; margin-bottom:20px;">
                <!-- Pemohon Box -->
                <div style="flex:1;">
                    <div style="background:#0a4b49; color:#fff; padding:6px 10px; font-size:13px;">Diminta Oleh :</div>
                    <div style="padding:10px 0; border:1px solid #fff;">
                        <div style="font-weight:800; font-size:14px; text-transform:uppercase; color:#000;"><?= htmlspecialchars($pm['diminta_oleh']) ?></div>
                    </div>
                </div>
                <!-- 2x2 Grid -->
                <div style="flex:1;">
                    <table style="width:100%; border-collapse:collapse; border:2px solid #000; text-align:center;">
                        <tr>
                            <td style="padding:8px; border-right:2px dashed #000; border-bottom:2px dashed #000;">
                                <div style="font-weight:800; font-size:12px; color:#000;">Tanggal Request</div>
                                <div style="font-size:12px; font-weight:700; font-style:italic; margin-top:4px; color:#000;"><?= date('d M Y', strtotime($pm['tanggal'])) ?></div>
                            </td>
                            <td style="padding:8px; border-bottom:2px dashed #000;">
                                <div style="font-weight:800; font-size:12px; color:#000;">Nomor Dokumen</div>
                                <div style="font-size:12px; font-weight:700; font-style:italic; margin-top:4px; color:#000;"><?= $no_dokumen ?></div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:8px; border-right:2px dashed #000;">
                                <div style="font-weight:800; font-size:12px; color:#000;">Status Permintaan</div>
                                <div style="font-size:12px; font-weight:700; font-style:italic; margin-top:4px; color:<?= strtolower($pm['status']) === 'disetujui' ? '#16a34a' : (strtolower($pm['status']) === 'ditolak' ? '#dc2626' : '#d97706') ?>;">
                                    <?= htmlspecialchars($pm['status']) ?>
                                </div>
                            </td>
                            <td style="padding:8px;">
                                <!-- Kosong / Info tambahan -->
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Removed old info block to prevent duplication with new grid -->

            <!-- TABEL MATERIAL -->
            <table class="invoice-table" style="width:100%;border-collapse:collapse;margin-bottom:24px;">
                <thead>
                    <tr>
                        <th style="background:#0a4b49;color:#fff;padding:8px 10px;text-align:left;border:1px solid #000;">Nama Material</th>
                        <th style="background:#0a4b49;color:#fff;padding:8px 10px;text-align:center;border:1px solid #000;">Jumlah Diminta</th>
                        <th style="background:#0a4b49;color:#fff;padding:8px 10px;text-align:center;border:1px solid #000;">Satuan</th>
                        <th style="background:#0a4b49;color:#fff;padding:8px 10px;text-align:right;border:1px solid #000;">Harga Referensi</th>
                        <th style="background:#0a4b49;color:#fff;padding:8px 10px;text-align:right;border:1px solid #000;">Estimasi Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding:10px;border:1px solid #000;"><strong><?= htmlspecialchars($pm['nama_material']) ?></strong></td>
                        <td style="padding:10px;border:1px solid #000;text-align:center;"><?= number_format($pm['jumlah'], 0, ',', '.') ?></td>
                        <td style="padding:10px;border:1px solid #000;text-align:center;"><?= htmlspecialchars($pm['satuan']) ?></td>
                        <td style="padding:10px;border:1px solid #000;text-align:right;"><?= formatRupiah($pm['harga_terakhir']) ?></td>
                        <td style="padding:10px;border:1px solid #000;text-align:right;font-weight:700;"><?= formatRupiah($pm['jumlah'] * $pm['harga_terakhir']) ?></td>
                    </tr>
                </tbody>
            </table>

            <!-- CATATAN -->
            <?php if (!empty($pm['catatan'])): ?>
            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:14px 18px;margin-bottom:24px;">
                <p style="font-size:12px;color:#92400e;font-weight:700;margin-bottom:4px;">📝 Catatan:</p>
                <p style="font-size:13px;color:#78350f;margin:0;"><?= nl2br(htmlspecialchars($pm['catatan'])) ?></p>
            </div>
            <?php endif; ?>

            <!-- AREA TANDA TANGAN -->
            <div class="signature-area" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:24px;margin-top:36px;text-align:center;">
                <div>
                    <p style="font-size:12px;font-weight:700;margin-bottom:60px;">Pemohon</p>
                    <div style="border-top:1px solid #333;padding-top:6px;">
                        <p style="font-size:12px;margin:0;"><?= htmlspecialchars($pm['diminta_oleh']) ?></p>
                    </div>
                </div>
                <div>
                    <p style="font-size:12px;font-weight:700;margin-bottom:60px;">Kepala Gudang</p>
                    <div style="border-top:1px solid #333;padding-top:6px;">
                        <p style="font-size:12px;margin:0;color:#aaa;">( ________________________ )</p>
                    </div>
                </div>
                <div>
                    <p style="font-size:12px;font-weight:700;margin-bottom:60px;">Disetujui Oleh</p>
                    <div style="border-top:1px solid #333;padding-top:6px;">
                        <p style="font-size:12px;margin:0;color:#aaa;">( ________________________ )</p>
                    </div>
                </div>
            </div>

            <!-- FOOTER DOKUMEN -->
            <div style="text-align:center;margin-top:32px;padding-top:16px;border-top:1px solid #eee;">
                <p style="font-size:12px;color:#1a4731;">PT. Prambanan Beton — Dokumen Permintaan Material Resmi</p>
                <p style="font-size:11px;color:#aaa;margin-top:2px;">Dicetak: <?= date('d/m/Y H:i') ?> — Dokumen ini sah dan diakui oleh perusahaan</p>
            </div>
        </div>
    </main>
</div>
</body>
</html>
