<?php
require_once '../includes/config.php';
requireLogin();
$currentPage = 'penjualan';

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('/penjualan/index.php');

$p = $conn->query("SELECT * FROM pesanan WHERE id=$id")->fetch_assoc();
if (!$p) redirect('/penjualan/index.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?= htmlspecialchars($p['no_invoice']) ?> - Sistem Prambanan</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/admin.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
    @media print {
        @page {
            size: A4;
            margin: 20mm;
        }
        html, body {
            background: var(--white) !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-size: 12px;
            height: auto !important;
            min-height: auto !important;
            overflow: visible !important;
        }
        .sidebar,
        .main-header,
        .no-print {
            display: none !important;
        }
        .admin-layout,
        .main-content {
            display: block !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            height: auto !important;
            min-height: auto !important;
            overflow: visible !important;
        }
        .invoice-wrap {
            max-width: 780px;
            margin: auto;
            padding: 30px;
            background: var(--white) !important;
            box-shadow: none !important;
            border: none !important;
        }
        .invoice-header {
            border-bottom: 1px solid var(--border) !important;
        }

        .invoice-company h2 {
            color: var(--green-dark) !important;
        }
        .invoice-badge {
            background: var(--gold) !important;
            color: var(--active-text) !important;
            font-size: 18px;
            font-weight: 900;
            padding: 6px 16px;
            border-radius: 6px;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-table thead th {
            background: var(--green-dark) !important;
            color: var(--white) !important;
            padding: 10px;
        }
        .invoice-table td {
            padding: 10px;
            border-bottom: 1px solid var(--border);
        }
        .invoice-total {
            margin-top: 20px;
        }
        .invoice-total-row {
            display: flex;
            justify-content: flex-end;
            gap: 40px;
            padding: 6px 0;
            font-size: 13px;
        }
        .invoice-total-row.grand {
            background: var(--green-dark) !important;
            color: var(--white) !important;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 15px;
            margin-top: 8px;
        }
        .invoice-wrap div[style*="background:#f9fafb"] {
            background: #f9fafb !important;
            border: 1px solid var(--border) !important;
        }
        .invoice-wrap p,
        .invoice-wrap span,
        .invoice-wrap small {
            color: #000;
        }
        .invoice-total-row.grand,
        .invoice-total-row.grand span {
            color: var(--white) !important;
        }
        .invoice-total-row.sisa,
        .invoice-total-row.sisa span {
        color: #ef4444 !important;
        font-weight: 700;
}
    }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header-row no-print">
            <div style="display:flex;align-items:center;gap:12px;">
                <a href="index.php" class="btn-back no-print"><i class="fas fa-arrow-left"></i> Kembali ke Penjualan</a>
            </div>
            <div>
                <h1 style="font-size:22px;font-weight:900;color:var(--green-dark);">Preview Invoice</h1>
                <p style="color:#aaa;font-size:13px;"><?= htmlspecialchars($p['no_invoice']) ?></p>
            </div>
            <button onclick="window.print()" class="btn btn-green no-print">
                <i class="fas fa-print"></i> Cetak PDF
            </button>
        </div>

        <?php if (isset($_GET['new'])): ?>
        <div class="alert alert-success no-print">✅ Pesanan berhasil disimpan!</div>
        <?php endif; ?>

        <!-- INVOICE -->
        <div class="invoice-wrap">
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
                <h3 style="color:#0a4b49; text-decoration:underline; font-size:18px; margin:0;">FAKTUR PENJUALAN</h3>
                <h3 style="color:#0a4b49; font-style:italic; font-size:18px; margin:0;">INVOICE</h3>
            </div>

            <div style="display:flex; justify-content:space-between; gap:20px; margin-bottom:20px;">
                <!-- Kepada Box -->
                <div style="flex:1;">
                    <div style="background:#0a4b49; color:#fff; padding:6px 10px; font-size:13px;">Kepada :</div>
                    <div style="padding:10px 0; border:1px solid #fff;">
                        <div style="font-weight:800; font-size:14px; text-transform:uppercase; color:#000;"><?= htmlspecialchars($p['nama_pelanggan']) ?></div>
                        <div style="font-size:13px; text-transform:uppercase; margin-top:4px; color:#000;"><?= htmlspecialchars($p['nama_proyek']) ?></div>
                    </div>
                </div>
                <!-- 2x2 Grid -->
                <div style="flex:1;">
                    <table style="width:100%; border-collapse:collapse; border:2px solid #000; text-align:center;">
                        <tr>
                            <td style="padding:8px; border-right:2px dashed #000; border-bottom:2px dashed #000;">
                                <div style="font-weight:800; font-size:12px; color:#000;">Tanggal Invoice</div>
                                <div style="font-size:12px; font-weight:700; font-style:italic; margin-top:4px; color:#000;"><?= date('d M Y', strtotime($p['tanggal'])) ?></div>
                            </td>
                            <td style="padding:8px; border-bottom:2px dashed #000;">
                                <div style="font-weight:800; font-size:12px; color:#000;">Nomor</div>
                                <div style="font-size:12px; font-weight:700; font-style:italic; margin-top:4px; color:#000;"><?= htmlspecialchars($p['no_invoice']) ?></div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:8px; border-right:2px dashed #000;">
                                <div style="font-weight:800; font-size:12px; color:#000;">Syarat Pembayaran</div>
                                <div style="font-size:12px; font-weight:700; font-style:italic; margin-top:4px; color:#000;">Transfer / Cash</div>
                            </td>
                            <td style="padding:8px;">
                                <div style="font-weight:800; font-size:12px; color:#000;">Tanggal Pengiriman</div>
                                <div style="font-size:12px; font-weight:700; font-style:italic; margin-top:4px; color:#000;"><?= date('d M Y', strtotime($p['tanggal'])) ?></div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

<?php
$items_res = $conn->query("SELECT * FROM pesanan_items WHERE pesanan_id=$id");
$items = [];
while($it = $items_res->fetch_assoc()) $items[] = $it;

// Jika belum ada items di pesanan_items (order lama), gunakan data dari pesanan
if (empty($items)) {
    $items[] = [
        'tipe_beton' => $p['tipe_beton'],
        'volume' => $p['volume'],
        'harga_per_m3' => $p['harga_per_m3'],
        'subtotal' => $p['subtotal']
    ];
}
?>
            <table class="invoice-table" style="width:100%;border-collapse:collapse;margin-bottom:20px;">
                <thead>
                    <tr>
                        <th style="background:#0a4b49;color:#fff;padding:8px 10px;text-align:left;border:1px solid #000;">Deskripsi</th>
                        <th style="background:#0a4b49;color:#fff;padding:8px 10px;text-align:center;border:1px solid #000;">Volume</th>
                        <th style="background:#0a4b49;color:#fff;padding:8px 10px;text-align:right;border:1px solid #000;">Harga/m3</th>
                        <th style="background:#0a4b49;color:#fff;padding:8px 10px;text-align:right;border:1px solid #000;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items as $item): ?>
                    <tr>
                        <td style="padding:10px;border:1px solid #000;">
                            <strong>Beton Ready Mix <?= htmlspecialchars($item['tipe_beton']) ?></strong><br>
                        </td>
                        <td style="padding:10px;border:1px solid #000;text-align:center;"><?= number_format($item['volume'],0,',','.') ?> m3</td>
                        <td style="padding:10px;border:1px solid #000;text-align:right;"><?= formatRupiah($item['harga_per_m3']) ?></td>
                        <td style="padding:10px;border:1px solid #000;text-align:right;"><?= formatRupiah($item['subtotal']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="invoice-total">
                <div class="invoice-total-row">
                    <span>Subtotal:</span>
                    <span><?= formatRupiah($p['subtotal']) ?></span>
                </div>
                <?php if ($p['ppn_aktif']): ?>
                <div class="invoice-total-row" style="color:var(--gold);">
                    <span>PPN (<?= $p['ppn_persen'] ?>%):</span>
                    <span><?= formatRupiah($p['ppn_nominal']) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($p['uang_muka'] > 0): ?>
                <div class="invoice-total-row" style="color:#888;">
                    <span>Uang Muka / DP:</span>
                    <span>- <?= formatRupiah($p['uang_muka']) ?></span>
                </div>
                <?php endif; ?>
                <div class="invoice-total-row grand">
                    <span>TOTAL:</span>
                    <span><?= formatRupiah($p['total_tagihan']) ?></span>
                </div>
                <?php if ($p['sisa_tagihan'] > 0 && $p['uang_muka'] > 0): ?>
                <div class="invoice-total-row sisa">
                    <span>Sisa Tagihan:</span>
                    <span><?= formatRupiah($p['sisa_tagihan']) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div style="background:#f9fafb;border-radius:8px;padding:16px 20px;margin-top:24px;border:1px solid #eee;">
                <p style="font-weight:700;font-size:14px;margin-bottom:8px;">Informasi Pembayaran:</p>
                <p style="font-size:13px;color:#555;">Bank BCA - 1234567890</p>
                <p style="font-size:13px;color:#555;">a.n. PT. Prambanan Beton</p>
                <p style="font-size:13px;color:var(--gold);margin-top:6px;">Harap transfer sesuai jumlah total dan konfirmasi pembayaran ke WhatsApp: <?= SITE_PHONE ?></p>
            </div>

            <div class="invoice-ttd">
                <div class="ttd-box">
                    <p class="ttd-title">Penerima / Customer</p>
                    <div class="ttd-line"></div>
                    <p class="ttd-name"><?= htmlspecialchars($p['nama_pelanggan']) ?></p>
                </div>
                <div class="ttd-box">
                    <p class="ttd-title">Hormat Kami,</p>
                    <div class="ttd-line"></div>
                    <p class="ttd-name">PT. Prambanan Beton</p>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
