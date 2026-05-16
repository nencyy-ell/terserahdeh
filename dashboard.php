<?php
require_once 'includes/config.php';
requireLogin();
$currentPage = 'dashboard';

// Stats
$total_penjualan = $conn->query("SELECT SUM(total_tagihan) as total FROM pesanan WHERE MONTH(tanggal)=MONTH(NOW()) AND YEAR(tanggal)=YEAR(NOW())")->fetch_assoc()['total'] ?? 0;
$pesanan_aktif   = $conn->query("SELECT COUNT(*) as c FROM pesanan WHERE status != 'Lunas'")->fetch_assoc()['c'] ?? 0;
$pesanan_baru    = $conn->query("SELECT COUNT(*) as c FROM pesanan WHERE MONTH(tanggal)=MONTH(NOW()) AND status != 'Lunas'")->fetch_assoc()['c'] ?? 0;

// Stok material rata-rata
$stok_result = $conn->query("SELECT AVG((stok_tersedia/stok_minimum)*100) as avg_pct FROM materials WHERE stok_minimum > 0");
$avg_stok = round($stok_result->fetch_assoc()['avg_pct'] ?? 85);
$stok_status = $avg_stok >= 60 ? 'Aman' : 'Perlu Perhatian';

$pengguna_aktif = $conn->query("SELECT COUNT(*) as c FROM admins WHERE is_active=1")->fetch_assoc()['c'] ?? 0;

// Aktivitas terbaru (Ambil 50 untuk scrollable list)
$aktivitas = $conn->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 50");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Prambanan</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/admin.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .activity-scroll-container::-webkit-scrollbar { width: 6px; }
        .activity-scroll-container::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
        .activity-scroll-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .activity-scroll-container::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include 'includes/navbar.php'; ?>
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Ringkasan aktivitas PT. Prambanan Beton</p>
        </div>

        <?php
        // LOGIKA NOTIFIKASI BERDASARKAN ROLE
        $notifs = [];
        $role = $_SESSION['admin_role'];

        // 1. Notif Stok Rendah (KHUSUS GUDANG & SUPERADMIN)
        if (in_array($role, ['gudang', 'superadmin'])) {
            $low = $conn->query("SELECT nama, stok_tersedia, satuan FROM materials WHERE stok_tersedia < stok_minimum");
            while ($r = $low->fetch_assoc()) {
                $notifs[] = [
                    'type' => 'warning',
                    'icon' => 'fa-box-open',
                    'text' => "Stok <strong>{$r['nama']}</strong> kritis: sisa {$r['stok_tersedia']} {$r['satuan']}. Segera tambah material!",
                    'link' => BASE_URL . '/persediaan/index.php'
                ];
            }
        }

        // 2. Notif Proyek DEAL / ACC (KHUSUS ADMIN & SUPERADMIN)
        if (in_array($role, ['admin', 'superadmin'])) {
            // Ambil deal dalam 7 hari terakhir yang BELUM dibuatkan pesanan
            $deals = $conn->query("SELECT id, nama_proyek, nama_marketing FROM marketing_reports 
                                   WHERE status_proyek = 'Deal' 
                                   AND tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                                   AND id NOT IN (SELECT marketing_report_id FROM pesanan WHERE marketing_report_id IS NOT NULL)
                                   ORDER BY id DESC LIMIT 5");
            while ($d = $deals->fetch_assoc()) {
                $notifs[] = [
                    'type' => 'success',
                    'icon' => 'fa-handshake',
                    'text' => "Proyek <strong>{$d['nama_proyek']}</strong> (Mkt: {$d['nama_marketing']}) telah <strong>DEAL!</strong> Segera buatkan pesanan barunya.",
                    'link' => BASE_URL . '/penjualan/buat.php?report_id=' . $d['id']
                ];
            }
        }

        // 3. Notif Pesanan Pending (MARKETING, ADMIN, SUPERADMIN)
        if (in_array($role, ['marketing', 'admin', 'superadmin'])) {
            $pending = $conn->query("SELECT COUNT(*) as c FROM pesanan WHERE status = 'Pending'")->fetch_assoc()['c'];
            if ($pending > 0) {
                $notifs[] = [
                    'type' => 'info',
                    'icon' => 'fa-file-invoice-dollar',
                    'text' => "Ada <strong>$pending pesanan</strong> yang statusnya masih Pending. Segera tindak lanjuti pembayaran!",
                    'link' => BASE_URL . '/penjualan/kelola-dp.php'
                ];
            }
        }
        // 4. Notif Permintaan Material Baru (KHUSUS GUDANG & SUPERADMIN)
        if (in_array($role, ['gudang', 'superadmin'])) {
            $req_pending = $conn->query("SELECT COUNT(*) as c FROM permintaan_material WHERE status = 'Pending'")->fetch_assoc()['c'];
            if ($req_pending > 0) {
                $notifs[] = [
                    'type' => 'info',
                    'icon' => 'fa-dolly',
                    'text' => "Ada <strong>$req_pending permintaan material</strong> baru dari Produksi. Segera cek dan setujui!",
                    'link' => BASE_URL . '/persediaan/index.php'
                ];
            }
        }
        ?>

        <!-- NOTIFICATION AREA -->
        <?php if (!empty($notifs)): ?>
        <div class="dashboard-notifs" style="margin-bottom: 32px;">
            <?php foreach ($notifs as $n): ?>
            <a href="<?= $n['link'] ?>" class="notif-card <?= $n['type'] ?>" style="display: block; text-decoration: none; margin-bottom: 12px; transition: all 0.3s ease;">
                <div style="display: flex; align-items: center; gap: 20px; padding: 18px 24px; background: #fff; border-radius: 12px; border-left: 5px solid <?= $n['type']==='warning' ? '#f59e0b' : ($n['type']==='success' ? '#10b981' : '#3b82f6') ?>; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); position: relative; overflow: hidden;">
                    <!-- Subtle Background Accent -->
                    <div style="position: absolute; right: -20px; top: -20px; font-size: 80px; opacity: 0.03; color: var(--text-muted);">
                        <i class="fas <?= $n['icon'] ?>"></i>
                    </div>
                    
                    <div style="width: 44px; height: 44px; border-radius: 10px; background: <?= $n['type']==='warning' ? '#fffbeb' : ($n['type']==='success' ? '#f0fdf4' : '#eff6ff') ?>; display: flex; align-items: center; justify-content: center; color: <?= $n['type']==='warning' ? '#d97706' : ($n['type']==='success' ? '#059669' : '#2563eb') ?>;">
                        <i class="fas <?= $n['icon'] ?>" style="font-size: 18px;"></i>
                    </div>
                    
                    <div style="flex: 1;">
                        <div style="font-size: 14px; color: #334155; line-height: 1.5; font-weight: 500;">
                            <?= $n['text'] ?>
                        </div>
                    </div>
                    
                    <div style="color: #94a3b8; font-size: 14px;">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
            <style>
                .notif-card:hover { transform: translateY(-3px); }
                .notif-card:hover > div { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); border-left-width: 8px; }
            </style>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($_SESSION['error_msg']) ?>
            </div>
            <?php unset($_SESSION['error_msg']); ?>
        <?php endif; ?>

        <!-- STAT CARDS -->
        <style>
            .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 32px; }
            .premium-stat-card { background: #fff; border-radius: 16px; padding: 24px; border: 1px solid #e2e8f0; position: relative; overflow: hidden; transition: all 0.3s ease; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
            .premium-stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 20px -5px rgba(0,0,0,0.08); }
            .premium-stat-card .icon-box { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 20px; }
            .premium-stat-card .label { color: #64748b; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: block; }
            .premium-stat-card .value { color: #0f172a; font-size: 28px; font-weight: 800; margin-bottom: 4px; display: block; }
            .premium-stat-card .subtext { font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 4px; }
            .trend-up { color: #10b981; }
            .accent-blue { border-top: 4px solid #3b82f6; }
            .accent-green { border-top: 4px solid #10b981; }
            .accent-orange { border-top: 4px solid #f59e0b; }
            .accent-purple { border-top: 4px solid #8b5cf6; }
        </style>
        <div class="stats-grid">
            <!-- Total Penjualan -->
            <div class="premium-stat-card accent-blue">
                <div class="icon-box" style="background: #eff6ff; color: #3b82f6;">
                    <i class="fas fa-chart-line"></i>
                </div>
                <span class="label">Total Penjualan</span>
                <span class="value"><?= formatRupiah($total_penjualan) ?></span>
                <div class="subtext trend-up">
                    <i class="fas fa-arrow-trend-up"></i> <span>+15.3% bulan ini</span>
                </div>
            </div>

            <!-- Pesanan Aktif -->
            <div class="premium-stat-card accent-green">
                <div class="icon-box" style="background: #f0fdf4; color: #10b981;">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <span class="label">Pesanan Aktif</span>
                <span class="value"><?= $pesanan_aktif ?></span>
                <div class="subtext" style="color: #64748b;">
                    <i class="fas fa-plus"></i> <span><?= $pesanan_baru ?> pesanan baru</span>
                </div>
            </div>

            <!-- Stok Material -->
            <div class="premium-stat-card accent-orange">
                <div class="icon-box" style="background: #fffbeb; color: #f59e0b;">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
                <span class="label">Stok Material</span>
                <span class="value"><?= $avg_stok ?>%</span>
                <div class="subtext" style="color: <?= $avg_stok >= 60 ? '#10b981' : '#ef4444' ?>;">
                    <i class="fas <?= $avg_stok >= 60 ? 'fa-check-circle' : 'fa-triangle-exclamation' ?>"></i> 
                    <span><?= $stok_status ?></span>
                </div>
            </div>

            <!-- Pengguna Online -->
            <div class="premium-stat-card accent-purple">
                <div class="icon-box" style="background: #f5f3ff; color: #8b5cf6;">
                    <i class="fas fa-users"></i>
                </div>
                <span class="label">Pengguna Aktif</span>
                <span class="value"><?= $pengguna_aktif ?></span>
                <div class="subtext" style="color: #10b981;">
                    <i class="fas fa-circle" style="font-size: 8px;"></i> <span>Online saat ini</span>
                </div>
            </div>
        </div>

        <!-- AKSI CEPAT + AKTIVITAS -->
        <div class="two-col">
            <div class="card">
                <h3><i class="fas fa-bolt"></i> Aksi Cepat</h3>
                <?php if (hasRoleAccess('penjualan')): ?>
                    <a href="<?= BASE_URL ?>/penjualan/buat.php" class="quick-action green">
                        Buat Pesanan Baru <i class="fas fa-arrow-up-right-from-square"></i>
                    </a>
                    <a href="<?= BASE_URL ?>/penjualan/kelola-dp.php" class="quick-action gold">
                        Update Uang Muka <i class="fas fa-arrow-up-right-from-square"></i>
                    </a>
                <?php else: ?>
                    <a href="#" onclick="alert('Akses Ditolak! Anda tidak memiliki izin untuk Penjualan.'); return false;" class="quick-action green" style="opacity: 0.6;">
                        Buat Pesanan Baru <i class="fas fa-lock"></i>
                    </a>
                    <a href="#" onclick="alert('Akses Ditolak! Anda tidak memiliki izin untuk Penjualan.'); return false;" class="quick-action gold" style="opacity: 0.6;">
                        Update Uang Muka <i class="fas fa-lock"></i>
                    </a>
                <?php endif; ?>

                <?php if ($_SESSION['admin_role'] === 'marketing'): ?>
                    <a href="<?= BASE_URL ?>/marketing/form.php" class="quick-action light">
                        Input Laporan Marketing <i class="fas fa-arrow-up-right-from-square"></i>
                    </a>
                <?php elseif (in_array($_SESSION['admin_role'], ['superadmin', 'admin'])): ?>
                    <a href="<?= BASE_URL ?>/marketing/index.php" class="quick-action light">
                        Monitoring Marketing Tracking <i class="fas fa-map-marker-alt"></i>
                    </a>
                <?php else: ?>
                    <a href="#" onclick="alert('Akses Ditolak! Anda tidak memiliki izin untuk Marketing.'); return false;" class="quick-action light" style="opacity: 0.6;">
                        Input Laporan Marketing <i class="fas fa-lock"></i>
                    </a>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3><i class="fas fa-history"></i> Aktivitas Terakhir</h3>
                <div class="activity-scroll-container" style="max-height: 400px; overflow-y: auto; padding-right: 10px;">
                    <?php if ($aktivitas && $aktivitas->num_rows > 0):
                        while ($a = $aktivitas->fetch_assoc()): ?>
                    <div class="activity-item">
                        <div class="activity-dot"></div>
                        <div>
                            <div class="activity-text"><strong><?= htmlspecialchars($a['admin_name']) ?></strong> <?= htmlspecialchars($a['action']) ?></div>
                            <div class="activity-time"><?= timeAgo($a['created_at']) ?></div>
                        </div>
                    </div>
                    <?php endwhile;
                    else: ?>
                    <p style="color:#aaa;font-size:13px;">Belum ada aktivitas.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
