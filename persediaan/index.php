<?php
require_once '../includes/config.php';
requireLogin();
requireRoleAccess('persediaan');
$currentPage = 'persediaan';

$msg = '';

// Update stok
if (isset($_POST['update_stok'])) {
    foreach ($_POST['stok'] as $id => $val) {
        $id = (int)$id;
        $stok = floatval($val);
        $harga = floatval($_POST['harga'][$id] ?? 0);
        $conn->query("UPDATE materials SET stok_tersedia=$stok, harga_terakhir=$harga WHERE id=$id");
    }
    $msg = 'Stok berhasil diupdate!';
}

// Tambah material baru
if (isset($_POST['tambah_material'])) {
    // Restrict to gudang only
    if ($_SESSION['admin_role'] !== 'gudang') {
        die("Akses ditolak!");
    }
    $nama   = sanitize($conn, $_POST['nama_material']);
    $satuan = sanitize($conn, $_POST['satuan']);
    $stok   = floatval($_POST['stok_awal']);
    $min    = floatval($_POST['stok_min']);
    $harga  = floatval($_POST['harga_awal']);
    if ($nama && $satuan) {
        $conn->query("INSERT INTO materials (nama,satuan,stok_tersedia,stok_minimum,harga_terakhir) VALUES ('$nama','$satuan',$stok,$min,$harga)");
        $msg = 'Material berhasil ditambahkan!';
    }
}

$materials = $conn->query("SELECT * FROM materials ORDER BY id");
$permintaan = $conn->query("SELECT pm.*, m.nama as nama_material, m.satuan FROM permintaan_material pm JOIN materials m ON pm.material_id=m.id ORDER BY pm.tanggal DESC LIMIT 10");

// Summary
$total_mat   = $conn->query("SELECT COUNT(*) as c FROM materials")->fetch_assoc()['c'] ?? 0;
$stok_rendah = $conn->query("SELECT COUNT(*) as c FROM materials WHERE stok_tersedia < stok_minimum")->fetch_assoc()['c'] ?? 0;
$permintaan_hari = $conn->query("SELECT COUNT(*) as c FROM permintaan_material WHERE tanggal=CURDATE()")->fetch_assoc()['c'] ?? 0;
$avg_stok = $conn->query("SELECT AVG((stok_tersedia/stok_minimum)*100) as avg FROM materials WHERE stok_minimum > 0")->fetch_assoc()['avg'] ?? 85;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persediaan - Sistem Prambanan</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/admin.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="admin-layout">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include '../includes/navbar.php'; ?>
        <div class="page-header-row">
            <h1>Persediaan</h1>
            <p>Kelola stok material dan bahan baku</p>
        </div>

        <?php if ($msg): ?><div class="alert alert-success">✅ <?= $msg ?></div><?php endif; ?>

        <!-- STAT CARDS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-top"><span class="stat-label">Total Material</span><span style="font-size:20px;">📦</span></div>
                <div class="stat-value"><?= $total_mat ?></div>
                <div class="stat-sub">Jenis material</div>
            </div>
            <div class="stat-card">
                <div class="stat-top"><span class="stat-label">Stok Rendah</span><span style="font-size:20px;">⚠️</span></div>
                <div class="stat-value" style="color:<?= $stok_rendah > 0 ? '#ef4444' : 'var(--green-mid)' ?>;"><?= $stok_rendah ?></div>
                <div class="stat-sub">Perlu pemesanan</div>
            </div>
            <div class="stat-card">
                <div class="stat-top"><span class="stat-label">Permintaan Hari Ini</span><span style="font-size:20px;color:#ef4444;">📉</span></div>
                <div class="stat-value"><?= $permintaan_hari ?></div>
                <div class="stat-sub">Permintaan material</div>
            </div>
            <div class="stat-card">
                <div class="stat-top"><span class="stat-label">Status Stok</span><span style="font-size:20px;color:#10b981;">📈</span></div>
                <div class="stat-value" style="color:var(--green-mid);"><?= round($avg_stok) ?>%</div>
                <div class="stat-sub">Rata-rata kecukupan</div>
            </div>
        </div>

        <!-- TABEL MATERIAL -->
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="margin-bottom:0;">Daftar Material &amp; Stok</h3>
                <?php if ($_SESSION['admin_role'] === 'gudang'): ?>
                <button onclick="document.getElementById('modalTambah').classList.add('open')" class="btn btn-green btn-sm">
                    <i class="fas fa-plus"></i> Tambah Material
                </button>
                <?php endif; ?>
            </div>
            <form method="POST">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Material</th>
                                <th>Stok Tersedia</th>
                                <th>Stok Minimum</th>
                                <th>Harga Terakhir</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($materials && $materials->num_rows > 0):
                                while ($m = $materials->fetch_assoc()):
                                    $is_rendah = $m['stok_tersedia'] < $m['stok_minimum'];
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($m['nama']) ?></strong></td>
                                <td>
                                    <input type="number" name="stok[<?= $m['id'] ?>]" value="<?= $m['stok_tersedia'] ?>"
                                        style="width:90px;padding:5px 8px;border:1px solid #ddd;border-radius:4px;font-size:13px;">
                                    m3
                                </td>
                                <td><?= $m['stok_minimum'] ?> m3</td>
                                <td>
                                    <input type="number" name="harga[<?= $m['id'] ?>]" value="<?= $m['harga_terakhir'] ?>"
                                        style="width:120px;padding:5px 8px;border:1px solid #ddd;border-radius:4px;font-size:13px;">
                                </td>
                                <td>
                                    <?php if ($is_rendah): ?>
                                    <span class="badge badge-rendah">Stok Rendah ⚠</span>
                                    <?php else: ?>
                                    <span class="badge badge-aman">Aman</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:16px;text-align:right;">
                    <button type="submit" name="update_stok" class="btn btn-green">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- PERMINTAAN MATERIAL -->
        <div class="card">
            <h3>Permintaan Material Terbaru</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Material</th><th>Jumlah</th><th>Diminta Oleh</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($permintaan && $permintaan->num_rows > 0):
                            while ($pm = $permintaan->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($pm['nama_material']) ?></td>
                            <td><?= $pm['jumlah'] ?> m3</td>
                            <td><?= htmlspecialchars($pm['diminta_oleh']) ?></td>
                            <td><?= date('d/m/Y', strtotime($pm['tanggal'])) ?></td>
                            <td><span class="badge badge-<?= strtolower($pm['status']) === 'pending' ? 'pending' : 'aman' ?>"><?= $pm['status'] ?></span></td>
                            <td style="white-space:nowrap;">
                                <a href="cetak.php?id=<?= $pm['id'] ?>" class="btn btn-sm btn-outline btn-icon" title="Cetak Bukti" target="_blank">🖨</a>
                            </td>
                        </tr>
                        <?php endwhile;
                        else: ?>
                        <tr><td colspan="5" style="text-align:center;color:#aaa;padding:20px;">Belum ada permintaan material.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php if ($_SESSION['admin_role'] === 'gudang'): ?>
<!-- MODAL TAMBAH MATERIAL -->
<div class="modal-backdrop" id="modalTambah" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal">
        <button class="modal-close" onclick="document.getElementById('modalTambah').classList.remove('open')">×</button>
        <h3>Tambah Material Baru</h3>
        <form method="POST">
            <div class="form-group">
                <label>Nama Material</label>
                <input type="text" name="nama_material" placeholder="Contoh: Semen Portland" required>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Satuan</label>
                    <input type="text" name="satuan" placeholder="ton / m&sup3; / liter" required>
                </div>
                <div class="form-group">
                    <label>Stok Awal</label>
                    <input type="number" name="stok_awal" placeholder="0" min="0">
                </div>
                <div class="form-group">
                    <label>Stok Minimum</label>
                    <input type="number" name="stok_min" placeholder="0" min="0">
                </div>
                <div class="form-group">
                    <label>Harga Terakhir (Rp)</label>
                    <input type="number" name="harga_awal" placeholder="0" min="0">
                </div>
            </div>
            <button type="submit" name="tambah_material" class="btn btn-green" style="width:100%;padding:12px;">Tambah Material</button>
        </form>
    </div>
</div>
<?php endif; ?>
</body>
</html>
