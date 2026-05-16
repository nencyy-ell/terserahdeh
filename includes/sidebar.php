<?php
// Pastikan sudah di-include config
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/config.php';
}
requireLogin();
$currentPage = $currentPage ?? '';
?>
<!-- Mobile Toggle Button -->
<button class="mobile-nav-toggle" onclick="document.querySelector('.sidebar').classList.toggle('show'); document.querySelector('.mobile-overlay').classList.toggle('show');">
    <i class="fas fa-bars"></i>
</button>
<div class="mobile-overlay" onclick="document.querySelector('.sidebar').classList.remove('show'); this.classList.remove('show');"></div>

<aside class="sidebar">
    <div class="sidebar-header" style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding: 24px 16px;">
        <div class="sidebar-logo-icon" style="background:transparent; box-shadow:none; width: 100%; height: auto; display: block; margin-bottom: 12px;">
            <img src="/prambanan-beton-internal/assets/images/logo.png" style="width: 130px; height: auto; object-fit: contain; margin: 0 auto; display: block;" alt="Logo" onerror="this.src='../assets/images/logo-beton.png'">
        </div>
        <div style="text-align: center;">
            <div class="title" style="font-size: 16px;">PT. Prambanan Beton</div>
            <div class="subtitle" style="font-size: 11px; margin-top:2px;">Sistem Administrasi Terpadu</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php if (hasRoleAccess('dashboard')): ?>
            <a href="<?= BASE_URL ?>/dashboard.php" class="nav-item <?= $currentPage==='dashboard' ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
        <?php else: ?>
            <a href="#" onclick="alert('Akses Ditolak! Anda tidak memiliki izin untuk fitur Dashboard.'); return false;" class="nav-item <?= $currentPage==='dashboard' ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
        <?php endif; ?>
        
        <?php if (hasRoleAccess('penjualan')): ?>
            <a href="<?= BASE_URL ?>/penjualan/index.php" class="nav-item <?= $currentPage==='penjualan' ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart"></i> Penjualan
            </a>
        <?php else: ?>
            <a href="#" onclick="alert('Akses Ditolak! Anda tidak memiliki izin untuk fitur Penjualan.'); return false;" class="nav-item <?= $currentPage==='penjualan' ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart"></i> Penjualan
            </a>
        <?php endif; ?>

        <?php if (hasRoleAccess('persediaan')): ?>
            <a href="<?= BASE_URL ?>/persediaan/index.php" class="nav-item <?= $currentPage==='persediaan' ? 'active' : '' ?>">
                <i class="fas fa-cube"></i> Persediaan
            </a>
        <?php else: ?>
            <a href="#" onclick="alert('Akses Ditolak! Anda tidak memiliki izin untuk fitur Persediaan.'); return false;" class="nav-item <?= $currentPage==='persediaan' ? 'active' : '' ?>">
                <i class="fas fa-cube"></i> Persediaan
            </a>
        <?php endif; ?>

        <?php if (hasRoleAccess('marketing')): ?>
            <?php $mkt_url = ($_SESSION['admin_role'] === 'marketing') ? '/marketing/form.php' : '/marketing/index.php'; ?>
            <a href="<?= BASE_URL ?><?= $mkt_url ?>" class="nav-item <?= $currentPage==='marketing' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i> Marketing
            </a>
        <?php else: ?>
            <a href="#" onclick="alert('Akses Ditolak! Anda tidak memiliki izin untuk fitur Marketing.'); return false;" class="nav-item <?= $currentPage==='marketing' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i> Marketing
            </a>
        <?php endif; ?>

        <?php if (in_array($_SESSION['admin_role'] ?? '', ['superadmin', 'admin'])): ?>
        <div style="padding: 16px 18px 6px; font-size: 10px; font-weight: 700; color: rgba(255,255,255,0.3); letter-spacing: 1.5px; text-transform: uppercase; margin-top: 8px;">
            Pengaturan
        </div>
        <a href="<?= BASE_URL ?>/pengaturan/users.php" class="nav-item <?= $currentPage==='manage_users' ? 'active' : '' ?>">
            <i class="fas fa-users-cog"></i> Manajemen User
        </a>
        <a href="<?= BASE_URL ?>/pengaturan/mutu.php" class="nav-item <?= $currentPage==='kelola_mutu' ? 'active' : '' ?>">
            <i class="fas fa-cubes"></i> Kelola Harga & Mutu
        </a>
        <a href="<?= BASE_URL ?>/pengaturan/logs.php" class="nav-item <?= $currentPage==='activity_logs' ? 'active' : '' ?>">
            <i class="fas fa-clipboard-list"></i> Log Aktivitas
        </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">

        <a href="<?= BASE_URL ?>/logout.php" class="nav-item">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
        <div style="margin-top: 20px; text-align: center; color: rgba(255,255,255,0.4); font-size: 10px; font-weight: 500;">
            &copy; 2026 PT. Prambanan Beton Indonesia.<br>All rights reserved.
        </div>
    </div>
</aside>
