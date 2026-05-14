<?php
require_once '../includes/config.php';
requireLogin();
$currentPage = 'penjualan';

// Generate nomor invoice
$last = $conn->query("SELECT no_invoice FROM pesanan ORDER BY id DESC LIMIT 1")->fetch_assoc();
$next_num = 1;
if ($last) {
    preg_match('/INV-\d+-(\d+)/', $last['no_invoice'], $m);
    $next_num = isset($m[1]) ? (int)$m[1] + 1 : 1;
}
$no_invoice = 'INV-' . date('Y') . '-' . str_pad($next_num, 3, '0', STR_PAD_LEFT);

// Ambil produk
$products = $conn->query("SELECT * FROM products WHERE is_active=1 ORDER BY kode");
$prod_data = [];
while($p = $products->fetch_assoc()) {
    $prod_data[] = $p;
}

// Cek stok material rendah
$low_stock = $conn->query("SELECT nama, stok_tersedia, satuan, stok_minimum FROM materials WHERE stok_tersedia <= stok_minimum OR stok_tersedia <= 0");
$low_stock_list = [];
while ($ls = $low_stock->fetch_assoc()) {
    $low_stock_list[] = $ls;
}

// Simpan pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();
    try {
        $no  = sanitize($conn, $_POST['no_invoice']);
        $pel = sanitize($conn, $_POST['nama_pelanggan']);
        $pro = sanitize($conn, $_POST['nama_proyek']);
        $tgl = sanitize($conn, $_POST['tanggal']);
        
        $subtotal = floatval($_POST['subtotal_hidden']);
        $ppn_aktif = isset($_POST['ppn_aktif']) ? 1 : 0;
        $ppn_persen = floatval($_POST['ppn_persen'] ?? 11);
        $ppn_nominal = floatval($_POST['ppn_nominal_hidden'] ?? 0);
        $total = floatval($_POST['total_hidden']);
        $dp = floatval($_POST['uang_muka'] ?? 0);
        $sisa = $total - $dp;
        $status = $dp >= $total ? 'Lunas' : ($dp > 0 ? 'DP 50%' : 'Pending');
        $admin_id = $_SESSION['admin_id'];

        // Calculate summary for main table (compatibility)
        $items = $_POST['items'];
        $total_vol = 0;
        $beton_types = [];
        $first_price = 0;
        
        foreach ($items as $item) {
            $vol = floatval($item['volume']);
            if ($vol > 0) {
                $total_vol += $vol;
                $beton_types[] = sanitize($conn, $item['kode']);
                if ($first_price == 0) $first_price = floatval($item['harga']);
            }
        }
        $types_str = substr(implode(", ", array_unique($beton_types)), 0, 20); // Table has limit of 20 chars

        // 1. Insert Pesanan
        $stmt = $conn->prepare("INSERT INTO pesanan (no_invoice, nama_pelanggan, nama_proyek, tipe_beton, volume, harga_per_m3, subtotal, ppn_aktif, ppn_persen, ppn_nominal, total_tagihan, uang_muka, total_terbayar, sisa_tagihan, status, tanggal, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssdddiddddddssi", $no, $pel, $pro, $types_str, $total_vol, $first_price, $subtotal, $ppn_aktif, $ppn_persen, $ppn_nominal, $total, $dp, $dp, $sisa, $status, $tgl, $admin_id);
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $pesanan_id = $conn->insert_id;

        // 2. Insert Items
        $item_stmt = $conn->prepare("INSERT INTO pesanan_items (pesanan_id, tipe_beton, volume, harga_per_m3, subtotal) VALUES (?,?,?,?,?)");
        
        foreach ($items as $item) {
            $kode = sanitize($conn, $item['kode']);
            $vol  = floatval($item['volume']);
            $hrg  = floatval($item['harga']);
            $sub  = $vol * $hrg;
            if ($vol > 0) {
                $item_stmt->bind_param("isddd", $pesanan_id, $kode, $vol, $hrg, $sub);
                $item_stmt->execute();
            }
        }

        // Log
        $action = "Membuat pesanan baru #$no (Multi-item)";
        $name = $_SESSION['admin_name'];
        $log = $conn->prepare("INSERT INTO activity_logs (admin_id, admin_name, action) VALUES (?,?,?)");
        $log->bind_param("iss", $admin_id, $name, $action);
        $log->execute();

        $conn->commit();
        redirect("/penjualan/invoice.php?id=$pesanan_id&new=1");
    } catch (Exception $e) {
        $conn->rollback();
        $error_msg = "Gagal menyimpan pesanan: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Pesanan Baru - Sistem Internal Prambanan</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/admin.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .item-table th { background: #f1f5f9; color: #475569; font-size: 11px; padding: 10px; }
        .item-table td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
        .item-table input, .item-table select { border: 1px solid #cbd5e1; padding: 8px; border-radius: 4px; font-size: 13px; width: 100%; }
        .btn-add-row { background: #0d2b1c; color: white; border: none; padding: 8px 16px; border-radius: 4px; font-size: 12px; cursor: pointer; margin-top: 10px; }
        .btn-remove { color: #ef4444; cursor: pointer; font-size: 16px; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include '../includes/navbar.php'; ?>

        <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
            <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>

        <div class="page-header">
            <h1>Buat Pesanan Baru</h1>
            <p>Form pemesanan multi-item ala Accurate</p>
        </div>

        <?php if (isset($error_msg)): ?>
        <div class="alert alert-error" style="margin-bottom:24px;">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_msg) ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($low_stock_list)): ?>
        <div class="alert alert-warning" style="margin-bottom:24px; background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; padding:16px; border-radius:8px;">
            <div style="display:flex; gap:12px; align-items:start;">
                <i class="fas fa-exclamation-triangle" style="margin-top:4px; font-size:18px;"></i>
                <div>
                    <strong style="display:block; margin-bottom:4px;">⚠ Peringatan Stok Material Rendah!</strong>
                    <ul style="margin:0; padding-left:20px; font-size:14px;">
                        <?php foreach ($low_stock_list as $ls): ?>
                            <li>
                                <strong><?= htmlspecialchars($ls['nama']) ?></strong>: 
                                <?= number_format($ls['stok_tersedia'], 2) ?> <?= $ls['satuan'] ?> 
                                (Minimum: <?= number_format($ls['stok_minimum'], 2) ?> <?= $ls['satuan'] ?>)
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <form method="POST" id="pesananForm">
            <div style="display:grid; grid-template-columns: 1fr 340px; gap:24px; align-items:start;">
                <!-- KIRI: DATA & ITEMS -->
                <div>
                    <div class="card" style="padding:24px;">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>No. Invoice</label>
                                <input type="text" name="no_invoice" value="<?= $no_invoice ?>" readonly style="background:#f1f5f9;">
                            </div>
                            <div class="form-group">
                                <label>Tanggal</label>
                                <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Pelanggan</label>
                                <input type="text" name="nama_pelanggan" placeholder="Nama Pelanggan" required>
                            </div>
                            <div class="form-group">
                                <label>Proyek</label>
                                <input type="text" name="nama_proyek" placeholder="Nama Proyek" required>
                            </div>
                        </div>

                        <div style="margin-top:30px;">
                            <h3 style="font-size:15px; margin-bottom:12px; color:#1e293b;">Rincian Barang</h3>
                            <div class="table-wrap" style="border:none;">
                                <table class="item-table">
                                    <thead>
                                        <tr>
                                            <th style="width:50px;">No</th>
                                            <th>Tipe Beton</th>
                                            <th style="width:120px;">Kuantitas (m3)</th>
                                            <th style="width:180px;">Harga Satuan</th>
                                            <th style="width:180px;">Total</th>
                                            <th style="width:40px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemRows">
                                        <tr class="row-item">
                                            <td class="row-num">1</td>
                                            <td>
                                                <select name="items[0][kode]" class="select-prod" onchange="updateRow(this)">
                                                    <option value="">-- Pilih --</option>
                                                    <?php foreach($prod_data as $p): ?>
                                                        <option value="<?= $p['kode'] ?>" data-harga="<?= $p['harga_per_m3'] ?>"><?= $p['kode'] ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td><input type="number" name="items[0][volume]" class="val-vol" value="0" min="0" step="0.01" oninput="calculate()"></td>
                                            <td><input type="number" name="items[0][harga]" class="val-harga" value="0" oninput="calculate()"></td>
                                            <td><input type="text" class="val-subtotal" value="Rp 0" readonly style="background:#f8fafc; font-weight:700;"></td>
                                            <td><i class="fas fa-trash btn-remove" onclick="removeRow(this)"></i></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" class="btn-add-row" onclick="addRow()"><i class="fas fa-plus"></i> Tambah Item</button>
                        </div>
                    </div>
                </div>

                <!-- KANAN: RINGKASAN -->
                <div class="card" style="padding:24px; position:sticky; top:100px; background:#fff;">
                    <h3 style="font-size:16px; margin-bottom:20px;">Ringkasan Biaya</h3>
                    
                    <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:14px; color:#64748b;">
                        <span>Subtotal</span>
                        <span id="txtSubtotal" style="font-weight:700; color:#1e293b;">Rp 0</span>
                    </div>

                    <div style="margin-bottom:12px; padding:12px; background:#f8fafc; border-radius:6px;">
                        <label style="display:flex; align-items:center; justify-content:space-between; cursor:pointer;">
                            <span style="font-size:13px; font-weight:600;">Kena Pajak (PPN)</span>
                            <input type="checkbox" name="ppn_aktif" id="ppnToggle" onchange="calculate()">
                        </label>
                        <div id="ppnArea" style="display:none; margin-top:10px;">
                            <div style="display:flex; align-items:center; justify-content:space-between; font-size:12px;">
                                <span>PPN (%)</span>
                                <input type="number" name="ppn_persen" id="ppnPersen" value="11" style="width:60px; padding:4px; border:1px solid #ddd;" oninput="calculate()">
                            </div>
                            <div style="display:flex; justify-content:space-between; margin-top:8px; font-weight:700; color:#ec4899;">
                                <span>Nilai PPN</span>
                                <span id="txtPPN">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex; justify-content:space-between; margin-top:20px; padding-top:15px; border-top:2px solid #f1f5f9; font-size:18px; font-weight:800; color:#1e293b;">
                        <span>Total</span>
                        <span id="txtTotal">Rp 0</span>
                    </div>

                    <div style="margin-top:20px;">
                        <label style="font-size:12px; font-weight:700; color:#64748b;">Uang Muka (DP)</label>
                        <input type="number" name="uang_muka" id="uangMuka" value="0" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:4px; margin-top:6px;" oninput="calculate()">
                    </div>

                    <div style="display:flex; justify-content:space-between; margin-top:15px; font-size:14px; color:#ef4444; font-weight:700;">
                        <span>Sisa Tagihan</span>
                        <span id="txtSisa">Rp 0</span>
                    </div>

                    <input type="hidden" name="subtotal_hidden" id="h_sub">
                    <input type="hidden" name="ppn_nominal_hidden" id="h_ppn">
                    <input type="hidden" name="total_hidden" id="h_total">

                    <button type="submit" class="btn-masuk" style="width:100%; margin-top:30px; padding:15px; font-size:15px;">
                        <i class="fas fa-save"></i> SIMPAN PESANAN
                    </button>
                </div>
            </div>
        </form>
    </main>
</div>

<script>
let rowCount = 1;

function addRow() {
    const table = document.getElementById('itemRows');
    const newRow = document.createElement('tr');
    newRow.className = 'row-item';
    newRow.innerHTML = `
        <td class="row-num">${rowCount + 1}</td>
        <td>
            <select name="items[${rowCount}][kode]" class="select-prod" onchange="updateRow(this)">
                <option value="">-- Pilih --</option>
                <?php foreach($prod_data as $p): ?>
                    <option value="<?= $p['kode'] ?>" data-harga="<?= $p['harga_per_m3'] ?>"><?= $p['kode'] ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td><input type="number" name="items[${rowCount}][volume]" class="val-vol" value="0" min="0" step="0.01" oninput="calculate()"></td>
        <td><input type="number" name="items[${rowCount}][harga]" class="val-harga" value="0" oninput="calculate()"></td>
        <td><input type="text" class="val-subtotal" value="Rp 0" readonly style="background:#f8fafc; font-weight:700;"></td>
        <td><i class="fas fa-trash btn-remove" onclick="removeRow(this)"></i></td>
    `;
    table.appendChild(newRow);
    rowCount++;
    reIndex();
}

function removeRow(btn) {
    if (document.querySelectorAll('.row-item').length > 1) {
        btn.closest('tr').remove();
        reIndex();
        calculate();
    } else {
        alert("Minimal harus ada 1 item.");
    }
}

function reIndex() {
    document.querySelectorAll('.row-item').forEach((row, i) => {
        row.querySelector('.row-num').textContent = i + 1;
    });
}

function updateRow(sel) {
    const row = sel.closest('tr');
    const harga = sel.options[sel.selectedIndex].dataset.harga || 0;
    row.querySelector('.val-harga').value = harga;
    calculate();
}

function formatRp(n) {
    return 'Rp ' + Math.round(n).toLocaleString('id-ID');
}

function calculate() {
    let subtotal = 0;
    document.querySelectorAll('.row-item').forEach(row => {
        const vol = parseFloat(row.querySelector('.val-vol').value) || 0;
        const hrg = parseFloat(row.querySelector('.val-harga').value) || 0;
        const sub = vol * hrg;
        subtotal += sub;
        row.querySelector('.val-subtotal').value = formatRp(sub);
    });

    const ppnOn = document.getElementById('ppnToggle').checked;
    const ppnPct = parseFloat(document.getElementById('ppnPersen').value) || 0;
    const dp = parseFloat(document.getElementById('uangMuka').value) || 0;

    document.getElementById('ppnArea').style.display = ppnOn ? 'block' : 'none';
    
    const ppnVal = ppnOn ? (subtotal * ppnPct / 100) : 0;
    const total = subtotal + ppnVal;
    const sisa = total - dp;

    document.getElementById('txtSubtotal').textContent = formatRp(subtotal);
    document.getElementById('txtPPN').textContent = formatRp(ppnVal);
    document.getElementById('txtTotal').textContent = formatRp(total);
    document.getElementById('txtSisa').textContent = formatRp(Math.max(0, sisa));

    document.getElementById('h_sub').value = subtotal;
    document.getElementById('h_ppn').value = ppnVal;
    document.getElementById('h_total').value = total;
}
</script>
</body>
</html>
