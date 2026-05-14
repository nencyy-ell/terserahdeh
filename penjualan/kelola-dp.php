<?php
require_once '../includes/config.php';
requireLogin();
$currentPage = 'penjualan';

$msg = '';

// Tambah cicilan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pesanan_id = (int)$_POST['pesanan_id'];
    $jumlah = floatval($_POST['jumlah']);
    $tgl = sanitize($conn, $_POST['tanggal_bayar']);
    $admin_id = $_SESSION['admin_id'];

    if ($jumlah > 0 && $pesanan_id) {
        // Insert pembayaran
        $stmt = $conn->prepare("INSERT INTO pembayaran (pesanan_id, jumlah, tanggal_bayar, created_by) VALUES (?,?,?,?)");
        $stmt->bind_param("idsi", $pesanan_id, $jumlah, $tgl, $admin_id);
        $stmt->execute();

        // Update total_terbayar dan sisa_tagihan pada pesanan
        $conn->query("UPDATE pesanan SET 
            total_terbayar = (SELECT SUM(jumlah) FROM pembayaran WHERE pesanan_id=$pesanan_id),
            sisa_tagihan = total_tagihan - (SELECT SUM(jumlah) FROM pembayaran WHERE pesanan_id=$pesanan_id)
            WHERE id=$pesanan_id");

        // Update status
        $ps = $conn->query("SELECT total_tagihan, total_terbayar FROM pesanan WHERE id=$pesanan_id")->fetch_assoc();
        $status = $ps['total_terbayar'] >= $ps['total_tagihan'] ? 'Lunas' 
                : ($ps['total_terbayar'] > 0 ? 'DP 50%' : 'Pending');
        $conn->query("UPDATE pesanan SET status='$status' WHERE id=$pesanan_id");

        $msg = 'Pembayaran berhasil ditambahkan!';
    }
}

// Ambil pesanan belum lunas
$pesanan = $conn->query("SELECT * FROM pesanan WHERE status != 'Lunas' ORDER BY tanggal DESC");

// Summary
$total_belum_lunas = $conn->query("SELECT COUNT(*) as c FROM pesanan WHERE status != 'Lunas'")->fetch_assoc()['c'] ?? 0;
$total_terbayar    = $conn->query("SELECT SUM(total_terbayar) as t FROM pesanan WHERE status != 'Lunas'")->fetch_assoc()['t'] ?? 0;
$total_sisa        = $conn->query("SELECT SUM(sisa_tagihan) as t FROM pesanan WHERE status != 'Lunas'")->fetch_assoc()['t'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola DP - Sistem Internal Prambanan</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/admin.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="admin-layout">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;" class="no-print">
            <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
        <div class="page-header">
            <h1>Kelola Uang Muka / DP</h1>
            <p>Update pembayaran dan cicilan proyek</p>
        </div>

        <?php if ($msg): ?><div class="alert alert-success">✅ <?= $msg ?></div><?php endif; ?>

        <!-- SUMMARY -->
        <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:24px;">
            <div class="stat-card" style="border:2px solid #3b82f6;">
                <div class="stat-label">Total Proyek Belum Lunas</div>
                <div class="stat-value" style="margin-top:8px;color:#3b82f6;"><?= $total_belum_lunas ?></div>
            </div>
            <div class="stat-card" style="border:2px solid var(--green-mid);">
                <div class="stat-label">Total Terbayar</div>
                <div class="stat-value" style="margin-top:8px;color:var(--green-mid);font-size:20px;"><?= formatRupiah($total_terbayar) ?></div>
            </div>
            <div class="stat-card" style="border:2px solid #ef4444;">
                <div class="stat-label">Total Sisa Tagihan</div>
                <div class="stat-value" style="margin-top:8px;color:#ef4444;font-size:20px;"><?= formatRupiah($total_sisa) ?></div>
            </div>
        </div>

        <!-- TABEL -->
        <div class="card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No. Invoice</th>
                            <th>Pelanggan</th>
                            <th>Proyek</th>
                            <th>Total Tagihan</th>
                            <th>Terbayar</th>
                            <th>Sisa</th>
                            <th>Status</th>
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
                            <td><?= formatRupiah($p['total_tagihan']) ?></td>
                            <td style="color:var(--green-mid);font-weight:600;"><?= formatRupiah($p['total_terbayar']) ?></td>
                            <td style="color:#ef4444;font-weight:600;"><?= formatRupiah($p['sisa_tagihan']) ?></td>
                            <td><span class="badge badge-<?= strtolower(str_replace(' ','',str_replace('DP 50%','dp',$p['status']))) ?>"><?= $p['status'] ?></span></td>
                            <td>
                                <button onclick="openModal(<?= $p['id'] ?>, '<?= addslashes($p['nama_proyek']) ?> - <?= addslashes($p['nama_pelanggan']) ?>', <?= $p['sisa_tagihan'] ?>)"
                                    class="btn btn-sm btn-green">+ Tambah Cicilan</button>
                            </td>
                        </tr>
                        <?php endwhile;
                        else: ?>
                        <tr><td colspan="8" style="text-align:center;color:#aaa;padding:32px;">Semua pesanan sudah lunas. 🎉</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- MODAL TAMBAH PEMBAYARAN -->
<div class="modal-backdrop" id="modalBackdrop" onclick="if(event.target===this)closeModal()">
    <div class="modal">
        <button class="modal-close" onclick="closeModal()">×</button>
        <h3>Tambah Pembayaran</h3>
        <form method="POST">
            <input type="hidden" name="pesanan_id" id="modalPesananId">
            <p style="font-weight:600;font-size:14px;margin-bottom:4px;" id="modalProyek"></p>
            <p style="color:#ef4444;font-size:13px;margin-bottom:16px;" id="modalSisa"></p>

            <div class="form-group">
                <label>Jumlah Pembayaran</label>
                <input type="number" name="jumlah" id="modalJumlah" placeholder="Rp 0" min="1" required>
            </div>
            <div class="form-group">
                <label>Tanggal Pembayaran</label>
                <input type="date" name="tanggal_bayar" value="<?= date('Y-m-d') ?>" required>
            </div>
            <button type="submit" class="btn btn-green" style="width:100%;padding:12px;">Update Saldo</button>
        </form>
    </div>
</div>

<script>
function openModal(id, proyek, sisa) {
    document.getElementById('modalPesananId').value = id;
    document.getElementById('modalProyek').textContent = proyek;
    document.getElementById('modalSisa').textContent = 'Sisa: Rp ' + sisa.toLocaleString('id-ID');
    document.getElementById('modalJumlah').max = sisa;
    document.getElementById('modalBackdrop').classList.add('open');
}
function closeModal() {
    document.getElementById('modalBackdrop').classList.remove('open');
}
</script>
</body>
</html>
