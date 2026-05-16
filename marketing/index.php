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
        .clickable-location { cursor: pointer; color: var(--green-mid); font-weight: 600; transition: color 0.2s; display:inline-flex; align-items:center; gap:4px; }
        .clickable-location:hover { color: var(--gold); }

        /* PREMIUM DETAIL MODAL */
        .detail-modal-backdrop {
            display: none;
            position: fixed;
            top: 0; left: var(--sidebar-w); right: 0; bottom: 0;
            background: rgba(5,15,10,0.72);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 900;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .detail-modal-backdrop.open { display: flex; animation: dmFadeIn 0.22s ease-out; }
        @media(max-width:900px) { .detail-modal-backdrop { left:0; } }

        @keyframes dmFadeIn  { from{opacity:0} to{opacity:1} }
        @keyframes dmSlideUp { from{opacity:0;transform:translateY(28px) scale(0.97)} to{opacity:1;transform:translateY(0) scale(1)} }

        .detail-modal {
            background: #fff;
            border-radius: 20px;
            width: 100%; max-width: 740px;
            max-height: 90vh; overflow-y: auto;
            box-shadow: 0 32px 80px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.08);
            animation: dmSlideUp 0.3s cubic-bezier(0.34,1.4,0.64,1) forwards;
            position: relative; display: flex; flex-direction: column;
        }
        .detail-modal::-webkit-scrollbar { width:5px; }
        .detail-modal::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:3px; }

        .dm-header {
            background: linear-gradient(135deg, #0d2b1c 0%, #16452e 55%, #1f6640 100%);
            padding: 28px 28px 22px;
            border-radius: 20px 20px 0 0;
            position: relative; overflow: hidden;
        }
        .dm-header::before {
            content:''; position:absolute; top:-40px; right:-40px;
            width:160px; height:160px; border-radius:50%;
            background: rgba(251,189,35,0.1);
        }
        .dm-header::after {
            content:''; position:absolute; bottom:-50px; left:20px;
            width:120px; height:120px; border-radius:50%;
            background: rgba(255,255,255,0.04);
        }
        .dm-close {
            position:absolute; top:14px; right:14px;
            width:34px; height:34px;
            background:rgba(255,255,255,0.15); border:none; border-radius:50%;
            color:#fff; font-size:16px; cursor:pointer;
            display:flex; align-items:center; justify-content:center;
            transition:background 0.2s; z-index:10;
        }
        .dm-close:hover { background:rgba(255,255,255,0.28); }
        .dm-icon {
            width:46px; height:46px; background:var(--gold);
            border-radius:12px; display:flex; align-items:center;
            justify-content:center; font-size:20px; color:var(--green-dark);
            margin-bottom:12px; box-shadow:0 4px 14px rgba(251,189,35,0.45);
        }
        .dm-title { font-size:20px; font-weight:800; color:#fff; margin:0 0 5px; letter-spacing:-0.3px; }
        .dm-sub { font-size:12px; color:rgba(255,255,255,0.6); margin:0; display:flex; align-items:center; gap:5px; }

        #modalMap { height:300px; width:100%; }

        .dm-map-badge {
            position:absolute; bottom:12px; left:12px;
            background:rgba(13,43,28,0.88);
            color:#fff; font-size:11px; font-weight:600;
            padding:5px 10px; border-radius:20px;
            backdrop-filter:blur(4px);
            display:flex; align-items:center; gap:5px;
            z-index:800; pointer-events:none;
        }
        .dm-map-badge i { color:var(--gold); }

        .dm-photo-section {
            padding: 22px 26px;
            background: #f8fafc;
            border-top: 1px solid #e8edf2;
            border-radius: 0 0 20px 20px;
        }
        .dm-photo-label {
            display:flex; align-items:center; gap:9px;
            font-size:13px; font-weight:700;
            color:var(--green-dark); margin-bottom:14px;
        }
        .dm-photo-label i {
            width:28px; height:28px;
            background:var(--green-light);
            border-radius:6px;
            display:flex; align-items:center; justify-content:center;
            font-size:12px; color:var(--green-mid);
        }
        .dm-photo-grid img {
            width:100%; border-radius:10px;
            border:3px solid #fff;
            box-shadow:0 4px 16px rgba(0,0,0,0.1);
            transition:transform 0.2s, box-shadow 0.2s;
        }
        .dm-photo-grid img:hover { transform:scale(1.015); box-shadow:0 8px 24px rgba(0,0,0,0.16); }
        .dm-no-photo { text-align:center; padding:28px; color:#94a3b8; font-size:13px; }
        .dm-no-photo i { font-size:36px; margin-bottom:10px; opacity:0.35; display:block; }
        /* Premium Stats Style */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 32px; }
        .premium-stat-card { background: #fff; border-radius: 16px; padding: 24px; border: 1px solid #e2e8f0; transition: all 0.3s ease; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .premium-stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 20px -5px rgba(0,0,0,0.08); }
        .premium-stat-card .icon-box { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 20px; }
        .premium-stat-card .label { color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: block; }
        .premium-stat-card .value { color: #0f172a; font-size: 28px; font-weight: 800; margin-bottom: 4px; display: block; }
        .premium-stat-card .subtext { font-size: 13px; color: #64748b; font-weight: 500; }
        .accent-blue { border-top: 4px solid #3b82f6; }
        .accent-green { border-top: 4px solid #10b981; }
        .accent-orange { border-top: 4px solid #f59e0b; }
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
        <div class="stats-grid" style="margin-top:24px;">
            <div class="premium-stat-card accent-blue">
                <div class="icon-box" style="background:#eff6ff; color:#3b82f6;"><i class="fas fa-location-dot"></i></div>
                <span class="label">Kunjungan Hari Ini</span>
                <span class="value"><?= $kunjungan_hari ?></span>
                <span class="subtext">Total aktivitas</span>
            </div>
            <div class="premium-stat-card accent-green">
                <div class="icon-box" style="background:#f0fdf4; color:#10b981;"><i class="fas fa-stopwatch-20"></i></div>
                <span class="label">Marketing Aktif</span>
                <span class="value" style="color:#10b981;"><?= $marketing_aktif ?></span>
                <span class="subtext">Sedang bertugas</span>
            </div>
            <div class="premium-stat-card accent-orange">
                <div class="icon-box" style="background:#fffbeb; color:#f59e0b;"><i class="fas fa-user-plus"></i></div>
                <span class="label">Lead Baru</span>
                <span class="value" style="color:#f59e0b;"><?= $lead_baru ?></span>
                <span class="subtext">Prospek potensial</span>
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

<!-- Modal Detail Lokasi & Foto (Premium) -->
<div id="detailModal" class="detail-modal-backdrop" onclick="if(event.target===this)closeModal()">
    <div class="detail-modal">

        <!-- Header -->
        <div class="dm-header">
            <button class="dm-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
            <div class="dm-icon"><i class="fas fa-map-marker-alt"></i></div>
            <h3 class="dm-title" id="modalTitle">Detail Lokasi</h3>
            <p class="dm-sub"><i class="fas fa-info-circle"></i> <span id="modalSub">Titik presisi saat laporan dikirim oleh marketing</span></p>
        </div>

        <!-- Map -->
        <div style="position:relative;">
            <div id="modalMap"></div>
            <div class="dm-map-badge"><i class="fas fa-satellite-dish"></i> GPS Terverifikasi</div>
        </div>

        <!-- Foto -->
        <div class="dm-photo-section" id="photoSection">
            <div class="dm-photo-label">
                <i class="fas fa-camera"></i>
                Foto Kegiatan
            </div>
            <div class="dm-photo-grid">
                <img id="modalPhoto" src="" alt="Foto Kegiatan" style="display:none;">
            </div>
            <div class="dm-no-photo" id="noPhotoMsg">
                <i class="fas fa-image"></i>
                Tidak ada foto yang dilampirkan
            </div>
        </div>

    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
    let detailMap, detailMarker;

    function initMap() {
        if (!detailMap) {
            detailMap = L.map('modalMap', { zoomControl: true }).setView([0,0], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(detailMap);

            // Custom marker icon (gold pin)
            const goldIcon = L.divIcon({
                className: '',
                html: '<div style="width:36px;height:36px;background:linear-gradient(135deg,#fbbd23,#f5a50b);border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 4px 12px rgba(251,189,35,0.5);border:3px solid #fff;"></div>',
                iconSize: [36,36],
                iconAnchor: [18,36]
            });
            detailMarker = L.marker([0,0], { icon: goldIcon }).addTo(detailMap);
        }
    }

    function showDetail(lat, lng, photoUrl, title) {
        const backdrop = document.getElementById('detailModal');
        backdrop.classList.add('open');
        document.body.style.overflow = 'hidden';

        document.getElementById('modalTitle').innerText = title || 'Detail Lokasi';

        setTimeout(() => {
            initMap();
            const pos = [parseFloat(lat), parseFloat(lng)];
            detailMap.setView(pos, 17);
            detailMarker.setLatLng(pos);
            detailMap.invalidateSize();
        }, 120);

        const photoImg = document.getElementById('modalPhoto');
        const noPhoto  = document.getElementById('noPhotoMsg');
        if (photoUrl) {
            photoImg.src = photoUrl;
            photoImg.style.display = 'block';
            noPhoto.style.display  = 'none';
        } else {
            photoImg.style.display = 'none';
            noPhoto.style.display  = 'block';
        }
    }

    function closeModal() {
        document.getElementById('detailModal').classList.remove('open');
        document.body.style.overflow = '';
    }

    // Escape key
    document.addEventListener('keydown', e => { if(e.key === 'Escape') closeModal(); });
</script>
</body>
</html>
