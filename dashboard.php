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

// Aktivitas terbaru
$aktivitas = $conn->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 6");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Prambanan</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/admin.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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

        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($_SESSION['error_msg']) ?>
            </div>
            <?php unset($_SESSION['error_msg']); ?>
        <?php endif; ?>

        <!-- STAT CARDS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label">Total Penjualan Bulan Ini</span>
                    <span class="stat-icon" style="color:#10b981;">📈</span>
                </div>
                <div class="stat-value"><?= formatRupiah($total_penjualan) ?></div>
                <div class="stat-sub">+15.3%</div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label">Pesanan Aktif</span>
                    <span class="stat-icon" style="color:#3b82f6;">🛒</span>
                </div>
                <div class="stat-value"><?= $pesanan_aktif ?></div>
                <div class="stat-sub">+<?= $pesanan_baru ?> pesanan</div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label">Stok Material</span>
                    <span class="stat-icon" style="color:#f97316;">📦</span>
                </div>
                <div class="stat-value"><?= $avg_stok ?>%</div>
                <div class="stat-sub"><?= $stok_status ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label">Pengguna Aktif</span>
                    <span class="stat-icon" style="color:#8b5cf6;">👥</span>
                </div>
                <div class="stat-value"><?= $pengguna_aktif ?></div>
                <div class="stat-sub">Online</div>
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
    </main>
</div>
</body>
</html>
