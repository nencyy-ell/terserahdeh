<?php
require_once '../includes/config.php';
requireLogin();
requireRoleAccess('persediaan');
$currentPage = 'persediaan';

$msg = '';
$error = '';

// Hak akses
$can_edit = in_array($_SESSION['admin_role'], ['gudang', 'superadmin']);
if (!$can_edit) {
    redirect('/persediaan/index.php');
}

// Proses Simpan Stok Masuk
if (isset($_POST['simpan_masuk'])) {
    $no_sj    = sanitize($conn, $_POST['no_surat_jalan']);
    $supplier = sanitize($conn, $_POST['supplier']);
    $tanggal  = sanitize($conn, $_POST['tanggal']);
    $admin_id = $_SESSION['admin_id'];

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO stok_masuk (no_surat_jalan, supplier, tanggal, admin_id) VALUES (?,?,?,?)");
        $stmt->bind_param("sssi", $no_sj, $supplier, $tanggal, $admin_id);
        $stmt->execute();
        $masuk_id = $conn->insert_id;

        $items = $_POST['items'];
        $item_stmt = $conn->prepare("INSERT INTO stok_masuk_items (stok_masuk_id, material_id, jumlah, harga_satuan) VALUES (?,?,?,?)");
        $update_stmt = $conn->prepare("UPDATE materials SET stok_tersedia = stok_tersedia + ?, harga_terakhir = ? WHERE id = ?");

        foreach ($items as $item) {
            $mid = (int)$item['material_id'];
            $qty = floatval($item['jumlah']);
            $hrg = floatval($item['harga']);

            if ($mid > 0 && $qty > 0) {
                $item_stmt->bind_param("iidd", $masuk_id, $mid, $qty, $hrg);
                $item_stmt->execute();

                $update_stmt->bind_param("ddi", $qty, $hrg, $mid);
                $update_stmt->execute();
            }
        }

        // Log aktivitas
        $action = "Input stok masuk material: $no_sj dari $supplier";
        $log = $conn->prepare("INSERT INTO activity_logs (admin_id, admin_name, action) VALUES (?,?,?)");
        $log->bind_param("iss", $admin_id, $_SESSION['admin_name'], $action);
        $log->execute();

        $conn->commit();
        redirect("invoice_masuk.php?id=$masuk_id&new=1");
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}

$materials_res = $conn->query("SELECT id, nama, satuan FROM materials ORDER BY nama");
$materials = [];
while($row = $materials_res->fetch_assoc()) $materials[] = $row;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Stok Masuk - Sistem Prambanan</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/admin.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .form-container { max-width: 900px; margin: 0 auto; }
        .item-row { background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 10px; display: grid; grid-template-columns: 2fr 1fr 1fr 40px; gap: 15px; align-items: end; }
        .btn-remove { color: #ef4444; cursor: pointer; padding-bottom: 12px; font-size: 18px; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include '../includes/sidebar.php'; ?>
    <main class="main-content">
        <?php include '../includes/navbar.php'; ?>
        
        <div class="page-header">
            <a href="index.php" class="btn-back" style="margin-bottom:20px;"><i class="fas fa-arrow-left"></i> Kembali ke Stok</a>
            <h1>Input Penerimaan Material</h1>
            <p>Gunakan form ini saat material baru datang dari supplier.</p>
        </div>

        <?php if ($error): ?><div class="alert alert-error">❌ <?= $error ?></div><?php endif; ?>

        <form method="POST" class="form-container">
            <div class="card">
                <h3><i class="fas fa-truck"></i> Informasi Pengiriman</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>No. Surat Jalan / Invoice Supplier *</label>
                        <input type="text" name="no_surat_jalan" placeholder="Contoh: SJ-2024-001" required>
                    </div>
                    <div class="form-group">
                        <label>Supplier / Vendor *</label>
                        <input type="text" name="supplier" placeholder="Nama PT atau Toko Supplier" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Penerimaan *</label>
                        <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
            </div>

            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <h3 style="margin:0;"><i class="fas fa-cubes"></i> Daftar Material yang Datang</h3>
                    <button type="button" onclick="openNewMaterialModal()" class="btn btn-sm btn-outline" style="font-size:11px;">
                        <i class="fas fa-plus"></i> Jenis Material Belum Ada?
                    </button>
                </div>
                
                <div id="items-container">
                    <div class="item-row">
                        <div class="form-group" style="margin:0;">
                            <label>Pilih Material</label>
                            <select name="items[0][material_id]" class="mat-select" required>
                                <option value="">-- Pilih --</option>
                                <?php foreach($materials as $m): ?>
                                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nama']) ?> (<?= $m['satuan'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label>Jumlah Datang</label>
                            <input type="number" name="items[0][jumlah]" step="0.01" placeholder="0.00" required>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label>Harga per Unit (Rp)</label>
                            <input type="number" name="items[0][harga]" placeholder="0" required>
                        </div>
                        <div class="btn-remove" style="visibility:hidden;"><i class="fas fa-times-circle"></i></div>
                    </div>
                </div>

                <button type="button" onclick="addItem()" class="btn btn-outline" style="margin-top:10px; width:100%;">
                    <i class="fas fa-plus"></i> Tambah Baris Material
                </button>
            </div>

            <div style="text-align:right; margin-bottom:50px;">
                <button type="submit" name="simpan_masuk" class="btn btn-green btn-large" style="width:100%;">
                    <i class="fas fa-check-circle"></i> Simpan & Cetak Bukti Penerimaan
                </button>
            </div>
        </form>
    </main>
</div>

<script>
let rowCount = 1;
function addItem() {
    const container = document.getElementById('items-container');
    const div = document.createElement('div');
    div.className = 'item-row';
    div.innerHTML = `
        <div class="form-group" style="margin:0;">
            <select name="items[${rowCount}][material_id]" class="mat-select" required>
                <option value="">-- Pilih --</option>
                <?php foreach($materials as $m): ?>
                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nama']) ?> (<?= $m['satuan'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin:0;">
            <input type="number" name="items[${rowCount}][jumlah]" step="0.01" placeholder="0.00" required>
        </div>
        <div class="form-group" style="margin:0;">
            <input type="number" name="items[${rowCount}][harga]" placeholder="0" required>
        </div>
        <div class="btn-remove" onclick="this.parentElement.remove()"><i class="fas fa-times-circle"></i></div>
    `;
    container.appendChild(div);
    rowCount++;
}

// Logic untuk Material Baru secara Dinamis
function openNewMaterialModal() {
    document.getElementById('modalMaterialBaru').classList.add('open');
}

function saveNewMaterial() {
    const nama = document.getElementById('new_mat_nama').value;
    const sat  = document.getElementById('new_mat_satuan').value;
    
    if(!nama || !sat) return alert('Nama dan Satuan wajib diisi!');
    
    const btn = document.getElementById('btnSaveMat');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

    const formData = new URLSearchParams();
    formData.append('nama', nama);
    formData.append('satuan', sat);

    fetch('ajax_tambah_material.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if(res.success) {
            const selects = document.querySelectorAll('.mat-select');
            selects.forEach(sel => {
                const opt = document.createElement('option');
                opt.value = res.id;
                opt.text = `${nama} (${sat})`;
                sel.add(opt);
            });
            alert('Material baru berhasil didaftarkan!');
            document.getElementById('modalMaterialBaru').classList.remove('open');
            document.getElementById('new_mat_nama').value = '';
        } else {
            alert('Gagal: ' + res.message);
        }
    })
    .catch(err => alert('Error: ' + err))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = 'Daftarkan Material';
    });
}
</script>

<div class="modal-backdrop" id="modalMaterialBaru" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal" style="width:400px;">
        <button class="modal-close" onclick="document.getElementById('modalMaterialBaru').classList.remove('open')">×</button>
        <h3 style="font-size:18px;"><i class="fas fa-plus-circle"></i> Daftarkan Jenis Material Baru</h3>
        <p style="font-size:12px; color:#666; margin-bottom:20px;">Gunakan ini jika barang yang datang belum ada di daftar pilihan.</p>
        
        <div class="form-group">
            <label>Nama Material *</label>
            <input type="text" id="new_mat_nama" placeholder="Contoh: Semen Gresik 50kg">
        </div>
        <div class="form-group">
            <label>Satuan *</label>
            <select id="new_mat_satuan">
                <option value="T">Ton (T)</option>
                <option value="m³">m³ (Kubik)</option>
                <option value="L">Liter (L)</option>
                <option value="kg">kg</option>
                <option value="Zak">Zak</option>
            </select>
        </div>
        <button type="button" id="btnSaveMat" onclick="saveNewMaterial()" class="btn btn-green" style="width:100%;">Daftarkan Material</button>
    </div>
</div>
</body>
</html>
