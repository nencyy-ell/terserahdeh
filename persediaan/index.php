<?php
require_once '../includes/config.php';
requireLogin();
requireRoleAccess('persediaan');
$currentPage = 'persediaan';

$msg = '';

// Tentukan hak edit DULU sebelum semua logika aksi
$can_edit = in_array($_SESSION['admin_role'], ['gudang', 'superadmin']);

// Update stok (Hanya Gudang & Superadmin)
if (isset($_POST['update_stok'])) {
    if (!$can_edit) {
        die("Akses ditolak!");
    }
    foreach ($_POST['stok'] as $id => $val) {
        $id = (int)$id;
        $stok = floatval($val);
        $harga = floatval($_POST['harga'][$id] ?? 0);
        $conn->query("UPDATE materials SET stok_tersedia=$stok, harga_terakhir=$harga WHERE id=$id");
        
        // Log aktivitas
        $m_info = $conn->query("SELECT nama FROM materials WHERE id=$id")->fetch_assoc();
        $action = "Update stok material: " . $m_info['nama'] . " menjadi $stok";
        $stmt_log = $conn->prepare("INSERT INTO activity_logs (admin_id, admin_name, action) VALUES (?,?,?)");
        $stmt_log->bind_param("iss", $_SESSION['admin_id'], $_SESSION['admin_name'], $action);
        $stmt_log->execute();
        $stmt_log->close();
    }
    $msg = 'Stok berhasil diupdate!';
}

// Tambah material baru
if (isset($_POST['tambah_material'])) {
    if (!$can_edit) {
        die("Akses ditolak!");
    }
    $nama   = sanitize($conn, $_POST['nama_material']);
    $satuan = sanitize($conn, $_POST['satuan']);
    $stok   = floatval($_POST['stok_awal']);
    $min    = floatval($_POST['stok_min']);
    $harga  = floatval($_POST['harga_awal']);
    if ($nama && $satuan) {
        $conn->query("INSERT INTO materials (nama,satuan,stok_tersedia,stok_minimum,harga_terakhir) VALUES ('$nama','$satuan',$stok,$min,$harga)");
        
        // Log aktivitas
        $action = "Menambah material baru: $nama";
        $stmt_log = $conn->prepare("INSERT INTO activity_logs (admin_id, admin_name, action) VALUES (?,?,?)");
        $stmt_log->bind_param("iss", $_SESSION['admin_id'], $_SESSION['admin_name'], $action);
        $stmt_log->execute();
        $stmt_log->close();

        $msg = 'Material berhasil ditambahkan!';
    }
}

// Proses Permintaan Material (Setujui / Tolak) — hanya untuk yang manual/lama (status Pending)
if (isset($_GET['aksi_permintaan']) && $can_edit) {
    $id_req = (int)$_GET['id_req'];
    $aksi = $_GET['aksi_permintaan'];
    
    $req = $conn->query("SELECT * FROM permintaan_material WHERE id=$id_req")->fetch_assoc();
    if ($req && $req['status'] === 'Pending') {
        if ($aksi === 'setujui') {
            $m_id = $req['material_id'];
            $qty = $req['jumlah'];
            
            // Kurangi stok
            $conn->query("UPDATE materials SET stok_tersedia = stok_tersedia - $qty WHERE id = $m_id");
            $conn->query("UPDATE permintaan_material SET status = 'Selesai' WHERE id = $id_req");
            
            // Log
            $m_info = $conn->query("SELECT nama FROM materials WHERE id=$m_id")->fetch_assoc();
            $action = "Menyetujui permintaan material: {$m_info['nama']} sebanyak $qty (Oleh {$req['diminta_oleh']})";
            $stmt_log = $conn->prepare("INSERT INTO activity_logs (admin_id, admin_name, action) VALUES (?,?,?)");
            $stmt_log->bind_param("iss", $_SESSION['admin_id'], $_SESSION['admin_name'], $action);
            $stmt_log->execute();
            $msg = "Permintaan material disetujui dan stok telah dikurangi.";
        } elseif ($aksi === 'tolak') {
            $conn->query("UPDATE permintaan_material SET status = 'Ditolak' WHERE id = $id_req");
            $msg = "Permintaan material ditolak.";
        }
    }
}

$materials = $conn->query("SELECT * FROM materials ORDER BY id");
$permintaan = $conn->query("SELECT pm.*, m.nama as nama_material, m.satuan, p.no_invoice 
                            FROM permintaan_material pm 
                            JOIN materials m ON pm.material_id=m.id 
                            LEFT JOIN pesanan p ON pm.pesanan_id=p.id
                            ORDER BY pm.tanggal DESC LIMIT 20");

// Summary
$total_mat   = $conn->query("SELECT COUNT(*) as c FROM materials")->fetch_assoc()['c'] ?? 0;
$stok_rendah = $conn->query("SELECT COUNT(*) as c FROM materials WHERE stok_tersedia < stok_minimum")->fetch_assoc()['c'] ?? 0;
$permintaan_hari = $conn->query("SELECT COUNT(*) as c FROM permintaan_material WHERE tanggal=CURDATE()")->fetch_assoc()['c'] ?? 0;
$auto_hari = $conn->query("SELECT COUNT(*) as c FROM permintaan_material WHERE tanggal=CURDATE() AND diminta_oleh LIKE 'Otomatis%'")->fetch_assoc()['c'] ?? 0;
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

        <?php if ($auto_hari > 0): ?>
        <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:14px 18px; margin-bottom:20px; display:flex; align-items:center; gap:12px; color:#1e40af; font-size:14px;">
            <i class="fas fa-robot" style="font-size:18px;"></i>
            <div>
                <strong>Pemotongan Stok Otomatis Hari Ini:</strong>
                Stok material telah otomatis dikurangi sebanyak <strong><?= $auto_hari ?> kali</strong> berdasarkan pesanan penjualan yang masuk hari ini. Lihat detail di tabel permintaan di bawah.
            </div>
        </div>
        <?php endif; ?>

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
                <?php 
                if ($can_edit): ?>
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
                                    <?php if ($can_edit): ?>
                                        <input type="number" name="stok[<?= $m['id'] ?>]" value="<?= $m['stok_tersedia'] ?>"
                                            style="width:90px;padding:5px 8px;border:1px solid #ddd;border-radius:4px;font-size:13px;">
                                    <?php else: ?>
                                        <span style="font-weight:600;"><?= $m['stok_tersedia'] ?></span>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($m['satuan']) ?>
                                </td>
                                <td><?= $m['stok_minimum'] ?> <?= htmlspecialchars($m['satuan']) ?></td>
                                <td>
                                    <?php if ($can_edit): ?>
                                        <input type="number" name="harga[<?= $m['id'] ?>]" value="<?= $m['harga_terakhir'] ?>"
                                            style="width:120px;padding:5px 8px;border:1px solid #ddd;border-radius:4px;font-size:13px;">
                                    <?php else: ?>
                                        <span><?= formatRupiah($m['harga_terakhir']) ?></span>
                                    <?php endif; ?>
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
                <?php if ($can_edit): ?>
                <div style="margin-top:16px;text-align:right;">
                    <button type="submit" name="update_stok" class="btn btn-green">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- PERMINTAAN MATERIAL -->
        <div class="card">
            <h3>Permintaan Material Terbaru</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Material</th><th>Jumlah</th><th>Keterangan</th><th>Ref. Invoice</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($permintaan && $permintaan->num_rows > 0):
                            while ($pm = $permintaan->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($pm['nama_material']) ?></strong></td>
                            <td><?= number_format($pm['jumlah'], 2) ?> <?= htmlspecialchars($pm['satuan']) ?></td>
                            <td>
                                <?php if (strpos($pm['diminta_oleh'], 'Otomatis') === 0): ?>
                                    <span style="display:inline-flex; align-items:center; gap:5px; background:#eff6ff; color:#1d4ed8; padding:3px 8px; border-radius:4px; font-size:12px; font-weight:700;">
                                        <i class="fas fa-robot"></i> <?= htmlspecialchars($pm['diminta_oleh']) ?>
                                    </span>
                                <?php else: ?>
                                    <?= htmlspecialchars($pm['diminta_oleh']) ?>
                                <?php endif; ?>
                            </td>
                            <td><small><?= $pm['no_invoice'] ? '<strong>'.$pm['no_invoice'].'</strong>' : '-' ?></small></td>
                            <td><?= date('d/m/Y', strtotime($pm['tanggal'])) ?></td>
                            <td>
                                <?php
                                $is_otomatis = strpos($pm['diminta_oleh'], 'Otomatis') === 0;
                                if ($pm['status'] === 'Selesai' && $is_otomatis): ?>
                                    <span class="badge" style="background:#dbeafe; color:#1e40af;">✅ Otomatis</span>
                                <?php elseif ($pm['status'] === 'Selesai'): ?>
                                    <span class="badge badge-aman">Selesai</span>
                                <?php elseif ($pm['status'] === 'Pending'): ?>
                                    <span class="badge badge-pending">Pending</span>
                                <?php else: ?>
                                    <span class="badge badge-rendah"><?= $pm['status'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="white-space:nowrap;">
                                <a href="cetak.php?id=<?= $pm['id'] ?>" class="btn btn-sm btn-outline btn-icon" title="Cetak Bukti" target="_blank">🖨</a>
                                <?php if ($can_edit && $pm['status'] === 'Pending'): ?>
                                <a href="index.php?aksi_permintaan=setujui&id_req=<?= $pm['id'] ?>" class="btn btn-sm btn-green" style="padding:4px 8px; font-size:11px;" onclick="return confirm('Setujui permintaan ini? Stok akan otomatis berkurang.')">Setujui</a>
                                <a href="index.php?aksi_permintaan=tolak&id_req=<?= $pm['id'] ?>" class="btn btn-sm btn-danger" style="padding:4px 8px; font-size:11px;" onclick="return confirm('Tolak permintaan ini?')">Tolak</a>
                                <?php endif; ?>
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

<?php if ($can_edit): ?>
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
                    <select name="satuan" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                        <option value="">-- Pilih Satuan --</option>
                        <option value="T">T</option>
                        <option value="m³">m³</option>
                        <option value="L">L</option>
                    </select>
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
