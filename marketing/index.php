<?php
require_once '../includes/config.php';
requireLogin();
requireRoleAccess('admin_only');
$currentPage = 'marketing';

$reports = $conn->query("SELECT * FROM marketing_reports ORDER BY tanggal DESC, id DESC LIMIT 20");

$kunjungan_hari = $conn->query("SELECT COUNT(*) as c FROM marketing_reports WHERE tanggal=CURDATE()")->fetch_assoc()['c'] ?? 0;
$marketing_aktif = $conn->query("SELECT COUNT(DISTINCT nama_marketing) as c FROM marketing_reports WHERE tanggal=CURDATE()")->fetch_assoc()['c'] ?? 0;
$lead_baru = $conn->query("SELECT COUNT(*) as c FROM marketing_reports WHERE status_proyek IN ('Follow Up','Nego') AND tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetch_assoc()['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketing - Sistem Internal Prambanan</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/admin.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    <style>
        .clickable-location { cursor: pointer; color: var(--green-mid); text-decoration: underline; font-weight: 500; transition: color 0.2s; }
        .clickable-location:hover { color: var(--gold); }
        /* Modal Styles */
        .modal { 
            display: none; 
            position: fixed; 
            z-index: 9999; 
            top: 0; 
            left: 0; 
            width: 100%;
            height: 100vh;
            background-color: rgba(15, 23, 42, 0.65); 
            backdrop-filter: blur(4px); 
            align-items: center; 
            justify-content: center; 
        }
        @media(max-width: 900px) {
            .modal { left: 0; width: 100vw; }
        }
        .modal-content { background-color: #fff; padding: 0; border-radius: 16px; width: 95%; max-width: 900px; max-height: 95vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); position: relative; display: flex; flex-direction: column; }
        .modal-header { padding: 24px; border-bottom: 1px solid #f1f5f9; flex-shrink: 0; }
        .close-modal { position: absolute; right: 20px; top: 20px; font-size: 28px; font-weight: bold; color: #64748b; cursor: pointer; transition: color 0.2s; z-index: 10; line-height: 1; }
        .close-modal:hover { color: #0f172a; }
        #modalMap { height: 400px; width: 100%; border-bottom: 1px solid #e2e8f0; flex-shrink: 0; }
        .modal-body { padding: 0; flex: 1; }
        .modal-photo-wrap { padding: 32px 24px; text-align: center; background: #f8fafc; }
        .modal-photo-wrap h4 { margin-bottom: 20px; text-align: left; display: flex; align-items: center; gap: 8px; color: #334155; }
        .modal-photo-wrap img { max-width: 100%; border-radius: 12px; box-shadow: var(--shadow-lg); border: 4px solid #fff; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header-row">
            <div class="page-header" style="margin-bottom:0;">
                <h1>Marketing Tracking</h1>
                <p>Monitoring aktivitas tim marketing</p>
            </div>
            <?php if ($_SESSION['admin_role'] === 'marketing'): ?>
                <a href="form.php" class="btn btn-green"><i class="fas fa-plus"></i> Input Laporan Baru</a>
            <?php endif; ?>
        </div>

        <!-- STAT CARDS -->
        <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-top:24px;">
            <div class="stat-card">
                <div class="stat-top"><span class="stat-label">Kunjungan Hari Ini</span><span style="color:var(--green-mid);font-size:20px;">📍</span></div>
                <div class="stat-value"><?= $kunjungan_hari ?></div>
                <div class="stat-sub">Total aktivitas</div>
            </div>
            <div class="stat-card">
                <div class="stat-top"><span class="stat-label">Marketing Aktif</span><span style="color:var(--green-mid);font-size:20px;">⏱</span></div>
                <div class="stat-value" style="color:var(--green-mid);"><?= $marketing_aktif ?></div>
                <div class="stat-sub">Sedang bertugas</div>
            </div>
            <div class="stat-card">
                <div class="stat-top"><span class="stat-label">Lead Baru</span><span style="color:var(--green-mid);font-size:20px;">✅</span></div>
                <div class="stat-value" style="color:var(--gold);"><?= $lead_baru ?></div>
                <div class="stat-sub">Prospek potensial</div>
            </div>
        </div>

        <!-- TABEL LAPORAN -->
        <div class="card">
            <h3>Laporan Marketing</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Marketing</th>
                            <th>Lokasi</th>
                            <th>Aktivitas</th>
                            <th>Waktu</th>
                            <th>Tanggal</th>
                            <th>Hasil</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($reports && $reports->num_rows > 0):
                            while ($r = $reports->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($r['nama_marketing']) ?></strong></td>
                            <td>
                                <div class="clickable-location" onclick="showDetail(<?= $r['lat'] ?>, <?= $r['lng'] ?>, '<?= $r['foto'] ? BASE_URL.'/uploads/'.$r['foto'] : '' ?>', '<?= htmlspecialchars($r['nama_proyek']) ?>')">
                                    <i class="fas fa-map-marker-alt" style="font-size:11px;"></i> <?= htmlspecialchars($r['wilayah'] ?: $r['alamat_proyek']) ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($r['jenis_aktivitas']) ?></td>
                            <td style="font-size:12px;">
                                <i class="fas fa-clock" style="color:#aaa;"></i>
                                <?= $r['jam_mulai'] ? substr($r['jam_mulai'],0,5) : '--' ?> - <?= $r['jam_selesai'] ? substr($r['jam_selesai'],0,5) : '--' ?>
                            </td>
                            <td><?= date('d/m/Y', strtotime($r['tanggal'])) ?></td>
                            <td style="font-size:12px;max-width:180px;"><?= htmlspecialchars($r['hasil_promosi']) ?></td>
                            <td>
                                <?php if ($r['is_verified']): ?>
                                <span class="badge badge-verified">✅ Verified</span>
                                <?php else: ?>
                                <span class="badge badge-pending"><?= htmlspecialchars($r['status_proyek']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="white-space:nowrap;">
                                <a href="cetak.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline btn-icon" title="Cetak Laporan" target="_blank">🖨</a>
                            </td>
                        </tr>
                        <?php endwhile;
                        else: ?>
                        <tr><td colspan="7" style="text-align:center;color:#aaa;padding:32px;">Belum ada laporan marketing.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<!-- Modal Detail Lokasi & Foto -->
<div id="detailModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeModal()">&times;</span>
        
        <div class="modal-header">
            <h3 id="modalTitle" style="margin-bottom: 4px; color: #0f172a;">Detail Lokasi</h3>
            <p id="modalSub" style="color: #64746b; font-size: 14px;">Titik presisi saat laporan dikirim oleh marketing</p>
        </div>
        
        <div id="modalMap"></div>
        
        <div class="modal-body">
            <div class="modal-photo-wrap" id="photoSection" style="display:none;">
                <h4><i class="fas fa-camera" style="color: var(--green-mid);"></i> Foto Kegiatan</h4>
                <img id="modalPhoto" src="" alt="Foto Kegiatan">
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
    let detailMap;
    let detailMarker;

    function initMap() {
        if (!detailMap) {
            detailMap = L.map('modalMap').setView([0, 0], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(detailMap);
            detailMarker = L.marker([0, 0]).addTo(detailMap);
        }
    }

    function showDetail(lat, lng, photoUrl, title) {
        const modal = document.getElementById('detailModal');
        modal.style.display = 'flex';
        
        document.getElementById('modalTitle').innerText = title || 'Detail Lokasi';
        
        setTimeout(() => {
            initMap();
            const pos = [lat, lng];
            detailMap.setView(pos, 17);
            detailMarker.setLatLng(pos);
            detailMap.invalidateSize();
        }, 100);

        const photoImg = document.getElementById('modalPhoto');
        const photoSec = document.getElementById('photoSection');
        if (photoUrl) {
            photoImg.src = photoUrl;
            photoSec.style.display = 'block';
        } else {
            photoSec.style.display = 'none';
        }
    }

    function closeModal() {
        document.getElementById('detailModal').style.display = 'none';
    }

    // Close on click outside
    window.onclick = function(event) {
        const modal = document.getElementById('detailModal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>
</body>
</html>
