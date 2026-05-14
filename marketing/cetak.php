<?php
require_once '../includes/config.php';
requireLogin();
$currentPage = 'marketing';

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('/marketing/index.php');

$r = $conn->query("SELECT * FROM marketing_reports WHERE id = $id")->fetch_assoc();
if (!$r) redirect('/marketing/index.php');

$no_dokumen = 'MKT-' . date('Y', strtotime($r['tanggal'])) . '-' . str_pad($id, 3, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Marketing <?= $no_dokumen ?> - Sistem Prambanan</title>
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
        .info-grid div { background: #f9fafb !important; border: 1px solid #eee !important; }
        .signature-area { page-break-inside: avoid; }
        .status-verified { color: #16a34a !important; }
        .status-pending { color: #d97706 !important; }
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
                <a href="index.php" class="btn-back no-print"><i class="fas fa-arrow-left"></i> Kembali ke Marketing</a>
            </div>
            <div>
                <h1 style="font-size:22px;font-weight:900;color:var(--green-dark);">Laporan Aktivitas Marketing</h1>
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
                <h3 style="color:#0a4b49; text-decoration:underline; font-size:18px; margin:0;">LAPORAN MARKETING</h3>
                <h3 style="color:#0a4b49; font-style:italic; font-size:18px; margin:0;">MARKETING REPORT</h3>
            </div>

            <div style="display:flex; justify-content:space-between; gap:20px; margin-bottom:20px;">
                <!-- Identitas Box -->
                <div style="flex:1;">
                    <div style="background:#0a4b49; color:#fff; padding:6px 10px; font-size:13px;">Identitas Marketing :</div>
                    <div style="padding:10px 0; border:1px solid #fff;">
                        <div style="font-weight:800; font-size:14px; text-transform:uppercase; color:#000;"><?= htmlspecialchars($r['nama_marketing']) ?></div>
                    </div>
                </div>
                <!-- 2x2 Grid -->
                <div style="flex:1;">
                    <table style="width:100%; border-collapse:collapse; border:2px solid #000; text-align:center;">
                        <tr>
                            <td style="padding:8px; border-right:2px dashed #000; border-bottom:2px dashed #000;">
                                <div style="font-weight:800; font-size:12px; color:#000;">Tanggal Laporan</div>
                                <div style="font-size:12px; font-weight:700; font-style:italic; margin-top:4px; color:#000;"><?= date('d M Y', strtotime($r['tanggal'])) ?></div>
                            </td>
                            <td style="padding:8px; border-bottom:2px dashed #000;">
                                <div style="font-weight:800; font-size:12px; color:#000;">Nomor Dokumen</div>
                                <div style="font-size:12px; font-weight:700; font-style:italic; margin-top:4px; color:#000;"><?= $no_dokumen ?></div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:8px; border-right:2px dashed #000;">
                                <div style="font-weight:800; font-size:12px; color:#000;">Status Proyek</div>
                                <div style="font-size:12px; font-weight:700; font-style:italic; margin-top:4px; color:<?= $r['is_verified'] ? '#16a34a' : '#d97706' ?>;">
                                    <?= $r['is_verified'] ? '✅ Verified' : htmlspecialchars($r['status_proyek']) ?>
                                </div>
                            </td>
                            <td style="padding:8px;">
                                <div style="font-weight:800; font-size:12px; color:#000;">Jenis Aktivitas</div>
                                <div style="font-size:12px; font-weight:700; font-style:italic; margin-top:4px; color:#000;"><?= htmlspecialchars($r['jenis_aktivitas']) ?></div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Removed old info block to prevent duplication with new grid -->

            <!-- GRID INFO LAPANGAN -->
            <div class="info-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
                <div style="background:#f9fafb;border-radius:8px;padding:14px 18px;border:1px solid #eee;">
                    <p style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Jenis Aktivitas</p>
                    <p style="font-weight:700;font-size:15px;margin:0;"><?= htmlspecialchars($r['jenis_aktivitas']) ?></p>
                </div>
                <div style="background:#f9fafb;border-radius:8px;padding:14px 18px;border:1px solid #eee;">
                    <p style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Waktu Aktivitas</p>
                    <p style="font-weight:700;font-size:15px;margin:0;">
                        <?= $r['jam_mulai'] ? substr($r['jam_mulai'], 0, 5) : '--' ?> 
                        — 
                        <?= $r['jam_selesai'] ? substr($r['jam_selesai'], 0, 5) : '--' ?> WIB
                    </p>
                </div>
                <div style="background:#f9fafb;border-radius:8px;padding:14px 18px;border:1px solid #eee;">
                    <p style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Lokasi / Wilayah</p>
                    <p style="font-weight:700;font-size:15px;margin:0;"><?= htmlspecialchars($r['wilayah'] ?: $r['alamat_proyek'] ?? '-') ?></p>
                </div>
                <div style="background:#f9fafb;border-radius:8px;padding:14px 18px;border:1px solid #eee;">
                    <p style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Nama Proyek / Klien</p>
                    <p style="font-weight:700;font-size:15px;margin:0;"><?= htmlspecialchars($r['nama_proyek'] ?? '-') ?></p>
                </div>
                <div style="background:#f9fafb;border-radius:8px;padding:14px 18px;border:1px solid #eee;">
                    <p style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Status Proyek (Tambahan)</p>
                    <p style="font-weight:700;font-size:15px;margin:0;color:<?= $r['is_verified'] ? '#16a34a' : '#d97706' ?>;">
                        <?= $r['is_verified'] ? '✅ Verified' : htmlspecialchars($r['status_proyek']) ?>
                    </p>
                </div>
                <?php if (!empty($r['volume_m3'])): ?>
                <div style="background:#f9fafb;border-radius:8px;padding:14px 18px;border:1px solid #eee;">
                    <p style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Volume Estimasi</p>
                    <p style="font-weight:700;font-size:15px;margin:0;"><?= number_format($r['volume_m3'], 0, ',', '.') ?> m³</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- HASIL PROMOSI -->
            <div style="background:#f9fafb;border:1px solid #eee;border-radius:8px;padding:16px 20px;margin-bottom:24px;">
                <p style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">HASIL KUNJUNGAN / PROMOSI</p>
                <p style="font-size:14px;color:#333;line-height:1.8;margin:0;"><?= nl2br(htmlspecialchars($r['hasil_promosi'] ?? '-')) ?></p>
            </div>

            <!-- TINDAK LANJUT -->
            <?php if (!empty($r['tindak_lanjut'])): ?>
            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:16px 20px;margin-bottom:24px;">
                <p style="font-size:11px;color:#92400e;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">📋 RENCANA TINDAK LANJUT</p>
                <p style="font-size:14px;color:#78350f;margin:0;"><?= nl2br(htmlspecialchars($r['tindak_lanjut'])) ?></p>
            </div>
            <?php endif; ?>

            <!-- TANDA TANGAN -->
            <div class="signature-area" style="display:grid;grid-template-columns:1fr 1fr;gap:40px;margin-top:40px;text-align:center;">
                <div>
                    <p style="font-size:12px;font-weight:700;margin-bottom:60px;">Marketing</p>
                    <div style="border-top:1px solid #333;padding-top:6px;">
                        <p style="font-size:12px;margin:0;"><?= htmlspecialchars($r['nama_marketing']) ?></p>
                    </div>
                </div>
                <div>
                    <p style="font-size:12px;font-weight:700;margin-bottom:60px;">Supervisor Marketing</p>
                    <div style="border-top:1px solid #333;padding-top:6px;">
                        <p style="font-size:12px;margin:0;color:#aaa;">( ________________________ )</p>
                    </div>
                </div>
            </div>

            <!-- FOOTER -->
            <div style="text-align:center;margin-top:32px;padding-top:16px;border-top:1px solid #eee;">
                <p style="font-size:12px;color:#1a4731;">PT. Prambanan Beton — Laporan Aktivitas Marketing Resmi</p>
                <p style="font-size:11px;color:#aaa;margin-top:2px;">Dicetak: <?= date('d/m/Y H:i') ?> — Dokumen ini sah dan diakui oleh perusahaan</p>
            </div>
        </div>
    </main>
</div>
</body>
</html>
