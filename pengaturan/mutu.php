<?php
require_once '../includes/config.php';
requireLogin();
requireRoleAccess('kelola_mutu');
$currentPage = 'kelola_mutu';
$success_msg = $error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'edit_harga') {
        $pid    = (int)($_POST['pid'] ?? 0);
        $harga  = (float)str_replace(['.', ','], ['', '.'], $_POST['harga_per_m3'] ?? 0);
        $nama   = trim($_POST['nama'] ?? '');
        $kekuatan = trim($_POST['kekuatan'] ?? '');
        $stmt = $conn->prepare("UPDATE products SET nama=?, harga_per_m3=?, kekuatan=? WHERE id=?");
        $stmt->bind_param("sdsi", $nama, $harga, $kekuatan, $pid);
        $stmt->execute();
        $success_msg = "Produk <strong>$nama</strong> berhasil diperbarui.";
        $log = "Memperbarui harga produk ID $pid ($nama): Rp " . number_format($harga, 0, ',', '.');
        $sl = $conn->prepare("INSERT INTO activity_logs (admin_id, admin_name, action) VALUES (?,?,?)");
        $sl->bind_param("iss", $_SESSION['admin_id'], $_SESSION['admin_name'], $log);
        $sl->execute();
        $stmt->close();
    }

    if ($action === 'toggle_aktif') {
        $pid = (int)($_POST['pid'] ?? 0);
        $cur = (int)$conn->query("SELECT is_active FROM products WHERE id=$pid")->fetch_assoc()['is_active'];
        $new = $cur ? 0 : 1;
        $conn->query("UPDATE products SET is_active=$new WHERE id=$pid");
        $pname = $conn->query("SELECT kode FROM products WHERE id=$pid")->fetch_assoc()['kode'];
        $success_msg = "Status produk $pname berhasil diubah.";
        $log = ($new ? "Mengaktifkan" : "Menonaktifkan") . " produk: $pname";
        $sl = $conn->prepare("INSERT INTO activity_logs (admin_id, admin_name, action) VALUES (?,?,?)");
        $sl->bind_param("iss", $_SESSION['admin_id'], $_SESSION['admin_name'], $log);
        $sl->execute();
    }

    if ($action === 'tambah_produk') {
        $kode   = trim($_POST['kode'] ?? '');
        $nama   = trim($_POST['nama'] ?? '');
        $harga  = (float)str_replace(['.', ','], ['', '.'], $_POST['harga_per_m3'] ?? 0);
        $kekuatan = trim($_POST['kekuatan'] ?? '');
        if (empty($kode) || empty($nama) || $harga <= 0) {
            $error_msg = "Kode, nama, dan harga wajib diisi.";
        } else {
            $stmt = $conn->prepare("INSERT INTO products (kode, nama, harga_per_m3, kekuatan, is_active) VALUES (?,?,?,?,1)");
            $stmt->bind_param("ssds", $kode, $nama, $harga, $kekuatan);
            if ($stmt->execute()) {
                $success_msg = "Produk <strong>$kode</strong> berhasil ditambahkan.";
                $log = "Menambahkan produk baru: $kode ($nama)";
                $sl = $conn->prepare("INSERT INTO activity_logs (admin_id, admin_name, action) VALUES (?,?,?)");
                $sl->bind_param("iss", $_SESSION['admin_id'], $_SESSION['admin_name'], $log);
                $sl->execute();
            } else {
                $error_msg = "Gagal: kode produk sudah ada.";
            }
            $stmt->close();
        }
    }
}

$products = $conn->query("SELECT * FROM products ORDER BY is_active DESC, kode ASC");
$total_p  = $conn->query("SELECT COUNT(*) c FROM products")->fetch_assoc()['c'];
$aktif_p  = $conn->query("SELECT COUNT(*) c FROM products WHERE is_active=1")->fetch_assoc()['c'];
$min_harga = $conn->query("SELECT MIN(harga_per_m3) v FROM products WHERE is_active=1")->fetch_assoc()['v'];
$max_harga = $conn->query("SELECT MAX(harga_per_m3) v FROM products WHERE is_active=1")->fetch_assoc()['v'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Harga & Mutu - Sistem Prambanan</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/admin.css?v=<?= time() ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.mutu-kode{font-size:18px;font-weight:900;color:var(--green-dark);font-family:monospace}
.harga-cell{font-weight:800;color:var(--text);font-size:15px}
.harga-cell small{display:block;font-size:11px;color:var(--text-muted);font-weight:500;}
.toggle-btn{border:none;cursor:pointer;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700;transition:all .2s}
.toggle-on{background:#d1fae5;color:#065f46}
.toggle-on:hover{background:#a7f3d0}
.toggle-off{background:#fee2e2;color:#991b1b}
.toggle-off:hover{background:#fca5a5}
/* Premium Stats Style */
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 32px; }
.premium-stat-card { background: #fff; border-radius: 16px; padding: 20px; border: 1px solid #e2e8f0; transition: all 0.3s ease; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
.premium-stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 20px -5px rgba(0,0,0,0.08); }
.premium-stat-card .icon-box { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; margin-bottom: 16px; }
.premium-stat-card .label { color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block; }
.premium-stat-card .value { color: #0f172a; font-size: 22px; font-weight: 800; display: block; }
.accent-orange { border-top: 4px solid #f59e0b; }
.accent-green { border-top: 4px solid #10b981; }
.accent-red { border-top: 4px solid #ef4444; }
.accent-blue { border-top: 4px solid #3b82f6; }
</style>
</head>
<body>
<div class="admin-layout">
<?php include '../includes/sidebar.php'; ?>
<main class="main-content">
<?php include '../includes/navbar.php'; ?>

<div class="page-header-row">
    <div class="page-header" style="margin-bottom:0;">
        <h1><i class="fas fa-cubes" style="color:var(--gold);font-size:26px;margin-right:10px;"></i>Kelola Harga & Mutu</h1>
        <p>Konfigurasi produk beton dan harga per m³</p>
    </div>
    <button class="btn btn-green" onclick="openModal('mTambah')"><i class="fas fa-plus"></i> Tambah Produk</button>
</div>

<?php if($success_msg): ?><div class="alert alert-success" style="margin-top:20px;"><i class="fas fa-check-circle"></i> <?= $success_msg ?></div><?php endif; ?>
<?php if($error_msg): ?><div class="alert alert-error" style="margin-top:20px;"><i class="fas fa-exclamation-circle"></i> <?= $error_msg ?></div><?php endif; ?>

<div class="stats-grid" style="margin-top:24px;">
    <div class="premium-stat-card accent-orange">
        <div class="icon-box" style="background:#fffbeb; color:#f59e0b;"><i class="fas fa-layer-group"></i></div>
        <span class="label">Total Produk</span>
        <span class="value"><?= $total_p ?></span>
    </div>
    <div class="premium-stat-card accent-green">
        <div class="icon-box" style="background:#f0fdf4; color:#10b981;"><i class="fas fa-check-double"></i></div>
        <span class="label">Produk Aktif</span>
        <span class="value"><?= $aktif_p ?></span>
    </div>
    <div class="premium-stat-card accent-red">
        <div class="icon-box" style="background:#fef2f2; color:#ef4444;"><i class="fas fa-arrow-trend-down"></i></div>
        <span class="label">Harga Terendah</span>
        <span class="value" style="font-size:18px;"><?= formatRupiah($min_harga ?? 0) ?></span>
    </div>
    <div class="premium-stat-card accent-blue">
        <div class="icon-box" style="background:#eff6ff; color:#3b82f6;"><i class="fas fa-arrow-trend-up"></i></div>
        <span class="label">Harga Tertinggi</span>
        <span class="value" style="font-size:18px;"><?= formatRupiah($max_harga ?? 0) ?></span>
    </div>
</div>

<div class="card" style="margin-top:0;">
    <h3><i class="fas fa-list"></i> Daftar Produk Beton</h3>
    <div class="table-wrap">
    <table>
        <thead><tr><th>Kode Mutu</th><th>Nama Produk</th><th>Kekuatan</th><th>Harga / m³</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php while($p = $products->fetch_assoc()): ?>
        <tr style="<?= !$p['is_active'] ? 'opacity:.55;' : '' ?>">
            <td><span class="mutu-kode"><?= htmlspecialchars($p['kode']) ?></span></td>
            <td style="font-weight:600;"><?= htmlspecialchars($p['nama']) ?></td>
            <td style="color:var(--text-muted);font-size:13px;"><?= htmlspecialchars($p['kekuatan'] ?? '-') ?></td>
            <td>
                <div class="harga-cell">
                    <?= formatRupiah($p['harga_per_m3']) ?>
                    <small>per m³</small>
                </div>
            </td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="toggle_aktif">
                    <input type="hidden" name="pid" value="<?= $p['id'] ?>">
                    <button type="submit" class="toggle-btn <?= $p['is_active'] ? 'toggle-on' : 'toggle-off' ?>"
                        onclick="return confirm('Ubah status produk ini?')">
                        <?= $p['is_active'] ? '✅ Aktif' : '🔴 Nonaktif' ?>
                    </button>
                </form>
            </td>
            <td>
                <button class="btn btn-sm btn-outline" onclick='openEditModal(<?= json_encode($p) ?>)' title="Edit Harga">
                    <i class="fas fa-edit"></i> Edit
                </button>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>
</main>
</div>

<!-- MODAL TAMBAH PRODUK -->
<div class="modal-backdrop" id="mTambah">
<div class="modal" style="max-width:500px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
        <h3 style="margin-bottom:0;"><i class="fas fa-plus-circle" style="color:var(--gold);"></i> Tambah Produk Baru</h3>
        <button class="modal-close" onclick="closeModal('mTambah')">×</button>
    </div>
    <form method="POST">
    <input type="hidden" name="action" value="tambah_produk">
    <div class="form-group"><label>Kode Mutu *</label><input type="text" name="kode" required placeholder="Contoh: K-350" style="font-weight:700;font-family:monospace;font-size:16px;"></div>
    <div class="form-group"><label>Nama Produk *</label><input type="text" name="nama" required placeholder="Contoh: Beton K-350"></div>
    <div class="form-group"><label>Kekuatan / Spesifikasi</label><input type="text" name="kekuatan" placeholder="Contoh: 29.05 MPa"></div>
    <div class="form-group"><label>Harga per m³ (Rp) *</label><input type="number" name="harga_per_m3" required placeholder="850000" min="0" step="1000"></div>
    <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:8px;">
        <button type="button" class="btn btn-outline" onclick="closeModal('mTambah')">Batal</button>
        <button type="submit" class="btn btn-green"><i class="fas fa-save"></i> Simpan</button>
    </div>
    </form>
</div>
</div>

<!-- MODAL EDIT PRODUK -->
<div class="modal-backdrop" id="mEdit">
<div class="modal" style="max-width:500px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
        <h3 style="margin-bottom:0;"><i class="fas fa-edit" style="color:var(--gold);"></i> Edit Produk</h3>
        <button class="modal-close" onclick="closeModal('mEdit')">×</button>
    </div>
    <form method="POST">
    <input type="hidden" name="action" value="edit_harga">
    <input type="hidden" name="pid" id="ep_id">
    <div class="form-group">
        <label>Kode Mutu</label>
        <input type="text" id="ep_kode" disabled style="opacity:.6;font-weight:700;font-family:monospace;font-size:16px;">
        <p class="form-note">Kode mutu tidak dapat diubah.</p>
    </div>
    <div class="form-group"><label>Nama Produk *</label><input type="text" name="nama" id="ep_nama" required></div>
    <div class="form-group"><label>Kekuatan / Spesifikasi</label><input type="text" name="kekuatan" id="ep_kekuatan" placeholder="Contoh: 29.05 MPa"></div>
    <div class="form-group">
        <label>Harga per m³ (Rp) *</label>
        <input type="number" name="harga_per_m3" id="ep_harga" required min="0" step="1000">
        <p class="form-note">Perubahan harga akan berlaku pada pesanan baru.</p>
    </div>
    <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:8px;">
        <button type="button" class="btn btn-outline" onclick="closeModal('mEdit')">Batal</button>
        <button type="submit" class="btn btn-green"><i class="fas fa-save"></i> Simpan Perubahan</button>
    </div>
    </form>
</div>
</div>

<script>
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
function openEditModal(p){
    document.getElementById('ep_id').value=p.id;
    document.getElementById('ep_kode').value=p.kode;
    document.getElementById('ep_nama').value=p.nama;
    document.getElementById('ep_kekuatan').value=p.kekuatan||'';
    document.getElementById('ep_harga').value=parseFloat(p.harga_per_m3);
    openModal('mEdit');
}
document.querySelectorAll('.modal-backdrop').forEach(b=>{
    b.addEventListener('click',e=>{if(e.target===b)b.classList.remove('open')});
});
</script>
</body>
</html>
