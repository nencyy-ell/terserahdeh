<?php
require_once '../includes/config.php';
requireLogin();
requireRoleAccess('penjualan');
$currentPage = 'penjualan';

// Hapus pesanan
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $conn->query("DELETE FROM pesanan WHERE id=$id");
    redirect('/penjualan/index.php?deleted=1');
}

$pesanan = $conn->query("SELECT * FROM pesanan ORDER BY tanggal DESC, id DESC");

// Summary
$total_pesanan = $conn->query("SELECT COUNT(*) as c FROM pesanan")->fetch_assoc()['c'] ?? 0;
$total_volume  = $conn->query("SELECT SUM(volume) as v FROM pesanan")->fetch_assoc()['v'] ?? 0;
$total_nilai   = $conn->query("SELECT SUM(total_tagihan) as t FROM pesanan")->fetch_assoc()['t'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penjualan - Sistem Internal Prambanan</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/admin.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="admin-layout">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include '../includes/navbar.php'; ?>
        <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">✅ Pesanan berhasil dihapus.</div>
        <?php endif; ?>
        <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success">✅ Pesanan berhasil disimpan.</div>
        <?php endif; ?>

        <div class="page-header-row">
            <div class="page-header" style="margin-bottom:0;">
                <h1>Penjualan</h1>
                <p>Kelola pesanan dan transaksi penjualan</p>
            </div>
            <div style="display:flex;gap:10px;align-items:center;">
                <a href="kelola-dp.php" class="btn btn-outline"><i class="fas fa-dollar-sign"></i> Kelola DP</a>
                <a href="buat.php" class="btn btn-green"><i class="fas fa-plus"></i> Buat Pesanan Baru</a>
            </div>
        </div>

        <!-- SUMMARY CARDS -->
        <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-top:24px;">
            <div class="stat-card" style="border:1px solid var(--border);">
                <div class="stat-label">Total Pesanan</div>
                <div class="stat-value" style="margin-top:8px;"><?= $total_pesanan ?></div>
            </div>
            <div class="stat-card" style="border:2px solid var(--gold);">
                <div class="stat-label">Total Volume</div>
                <div class="stat-value" style="margin-top:8px;"><?= number_format($total_volume, 0, ',', '.') ?> m3</div>
            </div>
            <div class="stat-card" style="border:2px solid var(--green-mid);">
                <div class="stat-label">Total Nilai</div>
                <div class="stat-value" style="margin-top:8px; font-size:20px;"><?= formatRupiah($total_nilai) ?></div>
            </div>
        </div>

        <!-- TABEL PESANAN -->
        <div class="card" style="margin-top:0;">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No. Invoice</th>
                            <th>Pelanggan</th>
                            <th>Proyek</th>
                            <th>Tipe Beton</th>
                            <th>Volume (m3)</th>
                            <th>Total Harga</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($pesanan && $pesanan->num_rows > 0):
                            while ($p = $pesanan->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($p['no_invoice']) ?></strong></td>
                            <td><?= htmlspecialchars($p['nama_pelanggan']) ?></td>
                            <td><?= htmlspecialchars($p['nama_proyek']) ?></td>
                            <td><?= htmlspecialchars($p['tipe_beton']) ?></td>
                            <td><?= number_format($p['volume'], 0, ',', '.') ?></td>
                            <td><?= formatRupiah($p['total_tagihan']) ?></td>
                            <td>
                                <?php
                                $badge = ['Lunas'=>'badge-lunas','DP 50%'=>'badge-dp','Pending'=>'badge-pending'];
                                $cls = $badge[$p['status']] ?? 'badge-pending';
                                ?>
                                <span class="badge <?= $cls ?>"><?= $p['status'] ?></span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($p['tanggal'])) ?></td>
                            <td style="white-space:nowrap;">
                                <a href="invoice.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline btn-icon" title="Lihat Invoice">👁</a>
                                <a href="index.php?hapus=<?= $p['id'] ?>" class="btn btn-sm btn-danger btn-icon" title="Hapus" onclick="return confirm('Hapus pesanan ini?')">🗑</a>
                            </td>
                        </tr>
                        <?php endwhile;
                        else: ?>
                        <tr><td colspan="9" style="text-align:center;color:#aaa;padding:32px;">Belum ada pesanan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>
