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
        $pct = ($ps['total_terbayar'] / $ps['total_tagihan']) * 100;
        $status = $ps['total_terbayar'] >= $ps['total_tagihan'] ? 'Lunas' 
                : ($ps['total_terbayar'] > 0 ? 'DP ' . round($pct) . '%' : 'Pending');
        $conn->query("UPDATE pesanan SET status='$status' WHERE id=$pesanan_id");
        
        // Log aktivitas
        $p_info = $conn->query("SELECT no_invoice FROM pesanan WHERE id=$pesanan_id")->fetch_assoc();
        $action = "Update pembayaran pesanan #" . $p_info['no_invoice'] . " sebesar " . formatRupiah($jumlah);
        $stmt_log = $conn->prepare("INSERT INTO activity_logs (admin_id, admin_name, action) VALUES (?,?,?)");
        $stmt_log->bind_param("iss", $_SESSION['admin_id'], $_SESSION['admin_name'], $action);
        $stmt_log->execute();
        $stmt_log->close();

        $msg = 'Pembayaran berhasil ditambahkan!';
    }
}

// Ambil SEMUA pesanan (Lunas diletakkan di bawah)
$pesanan = $conn->query("SELECT * FROM pesanan ORDER BY (status = 'Lunas') ASC, tanggal DESC");

// Summary
$total_belum_lunas = $conn->query("SELECT COUNT(*) as c FROM pesanan WHERE status != 'Lunas'")->fetch_assoc()['c'] ?? 0;
$total_terbayar    = $conn->query("SELECT SUM(total_terbayar) as t FROM pesanan")->fetch_assoc()['t'] ?? 0;
$total_sisa        = $conn->query("SELECT SUM(sisa_tagihan) as t FROM pesanan WHERE status != 'Lunas'")->fetch_assoc()['t'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola DP & Histori Pembayaran - Sistem Prambanan</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/admin.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .history-row { background: #f8fafc; display: none; }
        .history-row.open { display: table-row; }
        .history-container { padding: 15px 25px; }
        .history-table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .history-table th { background: #f1f5f9; color: #475569; font-size: 11px; text-transform: uppercase; padding: 10px; text-align: left; }
        .history-table td { padding: 10px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #1e293b; }
        .btn-history { background: #e2e8f0; color: #475569; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.2s; }
        .btn-history:hover { background: #cbd5e1; color: #0f172a; }
        .btn-history i { transition: transform 0.2s; }
        .btn-history.active i { transform: rotate(180deg); }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;" class="no-print">
            <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
        <div class="page-header">
            <h1>Kelola DP & Histori Pembayaran</h1>
            <p>Monitor cicilan dan riwayat pembayaran lengkap per invoice</p>
        </div>

        <?php if ($msg): ?><div class="alert alert-success">✅ <?= $msg ?></div><?php endif; ?>

        <!-- SUMMARY -->
        <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:24px;">
            <div class="stat-card" style="border:2px solid #3b82f6;">
                <div class="stat-label">Proyek Belum Lunas</div>
                <div class="stat-value" style="margin-top:8px;color:#3b82f6;"><?= $total_belum_lunas ?></div>
            </div>
            <div class="stat-card" style="border:2px solid var(--green-mid);">
                <div class="stat-label">Total Uang Masuk</div>
                <div class="stat-value" style="margin-top:8px;color:var(--green-mid);font-size:20px;"><?= formatRupiah($total_terbayar) ?></div>
            </div>
            <div class="stat-card" style="border:2px solid #ef4444;">
                <div class="stat-label">Total Piutang (Sisa)</div>
                <div class="stat-value" style="margin-top:8px;color:#ef4444;font-size:20px;"><?= formatRupiah($total_sisa) ?></div>
            </div>
        </div>

        <!-- TABEL -->
        <div class="card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Pelanggan</th>
                            <th>Total Tagihan</th>
                            <th>Terbayar</th>
                            <th>Sisa</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($pesanan && $pesanan->num_rows > 0):
                            while ($p = $pesanan->fetch_assoc()): 
                                $pid = $p['id'];
                                $history = $conn->query("SELECT p.*, a.name as admin_name FROM pembayaran p LEFT JOIN admins a ON p.created_by = a.id WHERE p.pesanan_id = $pid ORDER BY p.tanggal_bayar DESC, p.id DESC");
                            ?>
                        <tr id="row-<?= $pid ?>" class="<?= $p['status'] === 'Lunas' ? 'row-lunas' : '' ?>" style="<?= $p['status'] === 'Lunas' ? 'opacity:0.8;' : '' ?>">
                            <td><strong><?= htmlspecialchars($p['no_invoice']) ?></strong></td>
                            <td><?= htmlspecialchars($p['nama_pelanggan']) ?></td>
                            <td><?= formatRupiah($p['total_tagihan']) ?></td>
                            <td style="color:var(--green-mid);font-weight:600;"><?= formatRupiah($p['total_terbayar']) ?></td>
                            <td style="color:#ef4444;font-weight:600;"><?= formatRupiah($p['sisa_tagihan']) ?></td>
                            <td>
                                <?php
                                $status_val = $p['status'];
                                $cls = 'badge-pending';
                                if ($status_val === 'Lunas') $cls = 'badge-lunas';
                                elseif (strpos($status_val, 'DP') !== false) $cls = 'badge-dp';
                                ?>
                                <span class="badge <?= $cls ?>"><?= $status_val ?></span>
                            </td>
                            <td style="white-space:nowrap;">
                                <a href="invoice.php?id=<?= $pid ?>" class="btn btn-sm btn-outline btn-icon" title="Cetak Invoice" target="_blank">🖨</a>
                                <button class="btn-history" onclick="toggleHistory(<?= $pid ?>)" title="Lihat Histori" style="margin-left:5px;">
                                    <i class="fas fa-chevron-down"></i> Histori
                                </button>
                                <?php if ($p['status'] !== 'Lunas'): ?>
                                    <button onclick="openModal(<?= $pid ?>, '<?= addslashes($p['nama_proyek']) ?>', <?= $p['sisa_tagihan'] ?>)"
                                        class="btn btn-sm btn-green" style="margin-left:5px;">+ Bayar</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <!-- ROW HISTORI (HIDDEN BY DEFAULT) -->
                        <tr class="history-row" id="history-<?= $pid ?>">
                            <td colspan="7">
                                <div class="history-container">
                                    <h4 style="font-size:13px; margin-bottom:10px; color:#475569;"><i class="fas fa-clock-rotate-left"></i> Riwayat Pembayaran Proyek: <?= htmlspecialchars($p['nama_proyek']) ?></h4>
                                    <table class="history-table">
                                        <thead>
                                            <tr>
                                                <th>Tanggal Bayar</th>
                                                <th>Nominal</th>
                                                <th>Penerima</th>
                                                <th>Waktu Input</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($history && $history->num_rows > 0): 
                                                while ($h = $history->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= date('d/m/Y', strtotime($h['tanggal_bayar'])) ?></td>
                                                <td style="font-weight:600; color:var(--green-mid);"><?= formatRupiah($h['jumlah']) ?></td>
                                                <td><?= htmlspecialchars($h['admin_name'] ?? 'System') ?></td>
                                                <td style="color:#94a3b8; font-size:12px;"><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?></td>
                                            </tr>
                                            <?php endwhile; else: ?>
                                            <tr><td colspan="4" style="text-align:center; color:#94a3b8;">Belum ada riwayat pembayaran.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile;
                        else: ?>
                        <tr><td colspan="7" style="text-align:center;color:#aaa;padding:32px;">Belum ada data pesanan.</td></tr>
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
            <button type="submit" class="btn btn-green" style="width:100%;padding:12px;">Simpan Pembayaran</button>
        </form>
    </div>
</div>

<script>
function toggleHistory(id) {
    const row = document.getElementById('history-' + id);
    const btn = document.querySelector('#row-' + id + ' .btn-history');
    row.classList.toggle('open');
    btn.classList.toggle('active');
}

function openModal(id, proyek, sisa) {
    document.getElementById('modalPesananId').value = id;
    document.getElementById('modalProyek').textContent = proyek;
    document.getElementById('modalSisa').textContent = 'Sisa Piutang: Rp ' + sisa.toLocaleString('id-ID');
    document.getElementById('modalJumlah').max = sisa;
    document.getElementById('modalBackdrop').classList.add('open');
}
function closeModal() {
    document.getElementById('modalBackdrop').classList.remove('open');
}
</script>
</body>
</html>
