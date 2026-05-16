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
$permintaan = $conn->query("SELECT pm.pesanan_id, pm.tanggal, pm.status, pm.diminta_oleh, p.no_invoice, MIN(pm.id) as single_id,
                                   GROUP_CONCAT(CONCAT(m.nama, ' (', pm.jumlah, ' ', m.satuan, ')') SEPARATOR '<br>') as daftar_material
                            FROM permintaan_material pm 
                            JOIN materials m ON pm.material_id=m.id 
                            LEFT JOIN pesanan p ON pm.pesanan_id=p.id
                            GROUP BY pm.pesanan_id, pm.tanggal, pm.status, pm.diminta_oleh, p.no_invoice
                            ORDER BY pm.tanggal DESC LIMIT 20");

// Summary
$total_mat   = $conn->query("SELECT COUNT(*) as c FROM materials")->fetch_assoc()['c'] ?? 0;
$stok_rendah = $conn->query("SELECT COUNT(*) as c FROM materials WHERE stok_tersedia < stok_minimum")->fetch_assoc()['c'] ?? 0;
$permintaan_hari = $conn->query("SELECT COUNT(*) as c FROM permintaan_material WHERE tanggal=CURDATE()")->fetch_assoc()['c'] ?? 0;
$auto_hari = $conn->query("SELECT COUNT(*) as c FROM permintaan_material WHERE tanggal=CURDATE() AND diminta_oleh LIKE 'Otomatis%'")->fetch_assoc()['c'] ?? 0;
$avg_stok = $conn->query("SELECT AVG((stok_tersedia/stok_minimum)*100) as avg FROM materials WHERE stok_minimum > 0")->fetch_assoc()['avg'] ?? 85;

// Riwayat Stok Masuk
$riwayat_masuk = $conn->query("SELECT s.*, a.name as admin_penerima 
                               FROM stok_masuk s 
                               LEFT JOIN admins a ON s.admin_id = a.id 
                               ORDER BY s.tanggal DESC LIMIT 10");
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
        <div class="page-header">
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

        <style>
            .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 32px; }
            .premium-stat-card { background: #fff; border-radius: 16px; padding: 24px; border: 1px solid #e2e8f0; transition: all 0.3s ease; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
            .premium-stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 20px -5px rgba(0,0,0,0.08); }
            .premium-stat-card .icon-box { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 20px; }
            .premium-stat-card .label { color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: block; }
            .premium-stat-card .value { color: #0f172a; font-size: 28px; font-weight: 800; margin-bottom: 4px; display: block; }
            .premium-stat-card .subtext { font-size: 13px; color: #64748b; font-weight: 500; }
            .accent-blue { border-top: 4px solid #3b82f6; }
            .accent-red { border-top: 4px solid #ef4444; }
            .accent-orange { border-top: 4px solid #f59e0b; }
            .accent-green { border-top: 4px solid #10b981; }
        </style>

        <!-- STAT CARDS -->
        <div class="stats-grid">
            <div class="premium-stat-card accent-blue">
                <div class="icon-box" style="background:#eff6ff; color:#3b82f6;"><i class="fas fa-boxes-stacked"></i></div>
                <span class="label">Total Material</span>
                <span class="value"><?= $total_mat ?></span>
                <span class="subtext">Jenis material</span>
            </div>
            <div class="premium-stat-card <?= $stok_rendah > 0 ? 'accent-red' : 'accent-green' ?>">
                <div class="icon-box" style="background:<?= $stok_rendah > 0 ? '#fef2f2' : '#f0fdf4' ?>; color:<?= $stok_rendah > 0 ? '#ef4444' : '#10b981' ?>;">
                    <i class="fas <?= $stok_rendah > 0 ? 'fa-triangle-exclamation' : 'fa-circle-check' ?>"></i>
                </div>
                <span class="label">Stok Rendah</span>
                <span class="value" style="color:<?= $stok_rendah > 0 ? '#ef4444' : 'inherit' ?>;"><?= $stok_rendah ?></span>
                <span class="subtext">Perlu pemesanan</span>
            </div>
            <div class="premium-stat-card accent-orange">
                <div class="icon-box" style="background:#fffbeb; color:#f59e0b;"><i class="fas fa-clipboard-list"></i></div>
                <span class="label">Permintaan Hari Ini</span>
                <span class="value"><?= $permintaan_hari ?></span>
                <span class="subtext">Permintaan material</span>
            </div>
            <div class="premium-stat-card accent-green">
                <div class="icon-box" style="background:#f0fdf4; color:#10b981;"><i class="fas fa-chart-pie"></i></div>
                <span class="label">Status Stok</span>
                <span class="value"><?= round($avg_stok) ?>%</span>
                <span class="subtext">Rata-rata kecukupan</span>
            </div>
        </div>

        <!-- TABEL MATERIAL -->
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="margin-bottom:0;">Daftar Material &amp; Stok</h3>
                <?php if ($can_edit): ?>
                <div style="display:flex; gap:10px;">
                    <a href="stok_masuk.php" class="btn btn-gold btn-sm">
                        <i class="fas fa-truck-loading"></i> Input Stok Masuk (Supplier)
                    </a>
                </div>
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
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="width: 35%; text-align:left;">Daftar Material &amp; Jumlah</th>
                            <th style="width: 20%; text-align:left;">Keterangan</th>
                            <th style="width: 15%; text-align:left;">No. Invoice</th>
                            <th style="width: 12%; text-align:left;">Tanggal</th>
                            <th style="width: 10%; text-align:left;">Status</th>
                            <th style="width: 10%; text-align:left;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($permintaan && $permintaan->num_rows > 0):
                            while ($pm = $permintaan->fetch_assoc()): ?>
                        <tr>
                            <td style="font-size:14px; line-height:1.5;"><?= $pm['daftar_material'] ?></td>
                            <td>
                                <?php if (strpos($pm['diminta_oleh'], 'Otomatis') === 0): ?>
                                    <span style="display:inline-flex; align-items:center; gap:5px; background:#eff6ff; color:#1d4ed8; padding:3px 8px; border-radius:4px; font-size:11px; font-weight:700;">
                                        <i class="fas fa-robot"></i> Produksi (Otomatis)
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
                                    <span class="badge" style="background:#dbeafe; color:#1e40af; font-size:11px;">✅ Otomatis</span>
                                <?php elseif ($pm['status'] === 'Selesai'): ?>
                                    <span class="badge badge-aman" style="font-size:11px;">Selesai</span>
                                <?php elseif ($pm['status'] === 'Pending'): ?>
                                    <span class="badge badge-pending" style="font-size:11px;">Pending</span>
                                <?php else: ?>
                                    <span class="badge badge-rendah" style="font-size:11px;"><?= $pm['status'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="white-space:nowrap; text-align:left;">
                                <?php if ($pm['pesanan_id']): ?>
                                    <a href="invoice_keluar.php?pesanan_id=<?= $pm['pesanan_id'] ?>" class="btn btn-sm btn-green btn-icon" title="Cetak Bukti Pengeluaran (Gabungan)" target="_blank">
                                        <i class="fas fa-file-invoice"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="cetak.php?id=<?= $pm['single_id'] ?>" class="btn btn-sm btn-outline btn-icon" title="Cetak Bukti" target="_blank">🖨</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile;
                        else: ?>
                        <tr><td colspan="6" style="text-align:center;color:#aaa;padding:20px;">Belum ada permintaan material.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RIWAYAT STOK MASUK (SUPPLIER) -->
        <div class="card" style="border-top: 4px solid var(--gold); margin-top: 30px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="margin:0;"><i class="fas fa-history" style="color:var(--gold);"></i> Riwayat Stok Masuk (Supplier)</h3>
            </div>
            <div class="table-wrap">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="width: 25%; text-align:left;">No. Surat Jalan</th>
                            <th style="width: 25%; text-align:left;">Supplier</th>
                            <th style="width: 20%; text-align:left;">Tanggal Terima</th>
                            <th style="width: 20%; text-align:left;">Penerima</th>
                            <th style="width: 10%; text-align:left;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($riwayat_masuk && $riwayat_masuk->num_rows > 0):
                            while ($rm = $riwayat_masuk->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($rm['no_surat_jalan']) ?></strong></td>
                            <td><?= htmlspecialchars($rm['supplier']) ?></td>
                            <td><?= date('d/m/Y', strtotime($rm['tanggal'])) ?></td>
                            <td><small><?= htmlspecialchars($rm['admin_penerima']) ?></small></td>
                            <td style="text-align:left;">
                                <a href="invoice_masuk.php?id=<?= $rm['id'] ?>" class="btn btn-sm btn-outline btn-icon" title="Cetak Bukti Penerimaan" target="_blank">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile;
                        else: ?>
                        <tr><td colspan="5" style="text-align:center;color:#aaa;padding:20px;">Belum ada riwayat stok masuk.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>
