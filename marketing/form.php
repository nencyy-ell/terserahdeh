<?php
require_once '../includes/config.php';
requireLogin();
if ($_SESSION['admin_role'] !== 'marketing') {
    header("Location: index.php");
    exit;
}
$currentPage = 'marketing';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_file_foto = '';
    if (!empty($_POST['foto_watermark'])) {
        $foto_data = $_POST['foto_watermark'];
        $foto_data = str_replace('data:image/jpeg;base64,', '', $foto_data);
        $foto_data = str_replace(' ', '+', $foto_data);
        $img_decoded = base64_decode($foto_data);
        
        $nama_file_foto = 'MKT_' . time() . '.jpg';
        file_put_contents('../uploads/' . $nama_file_foto, $img_decoded);
    }

    $data = [
        'nama_marketing'  => sanitize($conn, $_POST['nama_marketing']),
        'nama_kontraktor' => sanitize($conn, $_POST['nama_kontraktor']),
        'nama_proyek'     => sanitize($conn, $_POST['nama_proyek']),
        'alamat_proyek'   => sanitize($conn, $_POST['alamat_proyek']),
        'jenis_proyek'    => sanitize($conn, $_POST['jenis_proyek']),
        'status_proyek'   => sanitize($conn, $_POST['status_proyek']),
        'estimasi_volume' => floatval($_POST['estimasi_volume'] ?? 0),
        'mutu_beton'      => sanitize($conn, $_POST['mutu_beton'] ?? ''),
        'volume_pasti'    => floatval($_POST['volume_pasti'] ?? 0),
        'contact_person'  => sanitize($conn, $_POST['contact_person']),
        'wilayah'         => sanitize($conn, $_POST['wilayah']),
        'jenis_aktivitas' => sanitize($conn, $_POST['jenis_aktivitas']),
        'jam_mulai'       => sanitize($conn, $_POST['jam_mulai']),
        'jam_selesai'     => sanitize($conn, $_POST['jam_selesai']),
        'lat'             => floatval($_POST['lat'] ?? SITE_MAPS_LAT),
        'lng'             => floatval($_POST['lng'] ?? SITE_MAPS_LNG),
        'hasil_promosi'   => sanitize($conn, $_POST['hasil_promosi']),
        'tanggal'         => sanitize($conn, $_POST['tanggal']),
    ];

    // Mutu & volume hanya wajib jika status Deal
    $mutu_sql  = $data['mutu_beton']   ? "'{$data['mutu_beton']}'"  : 'NULL';
    $volp_sql  = $data['volume_pasti'] > 0 ? $data['volume_pasti'] : 'NULL';

    $sql = "INSERT INTO marketing_reports (nama_marketing,nama_kontraktor,nama_proyek,alamat_proyek,jenis_proyek,status_proyek,estimasi_volume,mutu_beton,volume_pasti,contact_person,wilayah,jenis_aktivitas,jam_mulai,jam_selesai,lat,lng,hasil_promosi,tanggal,foto) 
            VALUES ('{$data['nama_marketing']}','{$data['nama_kontraktor']}','{$data['nama_proyek']}','{$data['alamat_proyek']}','{$data['jenis_proyek']}','{$data['status_proyek']}',{$data['estimasi_volume']},$mutu_sql,$volp_sql,'{$data['contact_person']}','{$data['wilayah']}','{$data['jenis_aktivitas']}','{$data['jam_mulai']}','{$data['jam_selesai']}',{$data['lat']},{$data['lng']},'{$data['hasil_promosi']}','{$data['tanggal']}','$nama_file_foto')";

    if ($conn->query($sql)) {
        $action = "Mengirim laporan marketing proyek: " . $data['nama_proyek'];
        $stmt_log = $conn->prepare("INSERT INTO activity_logs (admin_id, admin_name, action) VALUES (?,?,?)");
        $stmt_log->bind_param("iss", $_SESSION['admin_id'], $_SESSION['admin_name'], $action);
        $stmt_log->execute();
        $stmt_log->close();

        if ($_SESSION['admin_role'] === 'marketing') {
            redirect('/marketing/form.php?saved=1');
        } else {
            redirect('/marketing/index.php?saved=1');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Track Marketing - Sistem Prambanan</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/admin.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    <style>
        .admin-layout { width: 100%; max-width: 100%; }
        .main-content { width: 100%; padding: 15px 20px; box-sizing: border-box; }
        .form-center-wrap { max-width: 1250px; margin: 0 auto; }
        .full-card { width: 100%; background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 24px; box-sizing: border-box; }
        .section-title { border-left: 5px solid #f39c12; padding-left: 15px; margin-bottom: 20px; color: #333; font-weight: bold; }
        hr { border: 0; border-top: 1px solid #eee; margin: 30px 0; }
        #map { height: 350px; width: 100%; border-radius: 8px; border: 1px solid #ddd; }
        .btn-large { padding: 15px; font-size: 16px; font-weight: bold; flex: 1; border-radius: 8px; cursor: pointer; }
        #photo-review-wrap { display: none; margin-top: 10px; padding: 15px; background: #f1f8e9; border: 1px solid #2e7d32; border-radius: 8px; text-align: center; }
        #img-preview { max-width: 100%; max-height: 200px; border-radius: 6px; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); cursor: pointer; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include '../includes/sidebar.php'; ?>
    <main class="main-content">
        <div style="margin-bottom:16px;">
            <?php if ($_SESSION['admin_role'] !== 'marketing'): ?>
                <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
            <?php endif; ?>
        </div>
        <div class="form-center-wrap">
        <div class="page-header"><h1>Form Track Marketing</h1></div>

        <?php if (isset($_GET['saved'])): ?>
            <div class="alert alert-success" style="margin-bottom: 20px; background: #dcfce7; color: #166534; padding: 16px; border-radius: 8px; display: flex; align-items: center; gap: 12px; font-weight: 500; border: 1px solid #bbf7d0;">
                <i class="fas fa-check-circle" style="font-size: 20px;"></i> Laporan marketing berhasil dikirim!
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="full-card">
                <div class="form-section">
                    <h3 class="section-title">Data Proyek</h3>
                    <div class="form-group"><label>Nama Kontraktor *</label><input type="text" name="nama_kontraktor" required></div>
                    <div class="form-group"><label>Nama Proyek *</label><input type="text" name="nama_proyek" required></div>
                    <div class="form-group"><label>Alamat Proyek *</label><input type="text" name="alamat_proyek" required></div>
                    <div class="form-group">
                        <label>Jenis Proyek *</label>
                        <div class="radio-group">
                            <?php foreach(['Pemerintah','BUMN','Swasta','Retail','Lainnya'] as $j): ?>
                            <label class="radio-item"><input type="radio" name="jenis_proyek" value="<?= $j ?>" required> <?= $j ?></label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form-group">
                       <div class="form-group"><label>Status *</label>
                        <div class="radio-group">
                            <?php foreach(['Follow Up','Nego','Deal','Loss'] as $s): ?>
                            <label class="radio-item">
                                <input type="radio" name="status_proyek" value="<?= $s ?>" required onchange="toggleDealFields(this.value)">
                                <?= $s ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form-group"><label>Estimasi Volume (m&sup3;)</label><input type="number" name="estimasi_volume" min="0" step="0.01"></div>

                    <!-- FIELD DEAL: hanya muncul saat status = Deal -->
                    <div id="dealFields" style="display:none; background:#f0fdf4; border:2px solid #bbf7d0; border-radius:10px; padding:16px; margin-top:8px;">
                        <div style="font-size:13px; font-weight:800; color:#065f46; margin-bottom:14px; display:flex; align-items:center; gap:8px;">
                            <i class="fas fa-handshake" style="color:#16a34a;"></i>
                            Detail Deal — Wajib diisi untuk notifikasi pesanan
                        </div>

                        <?php
                        $prod_mkt = $conn->query("SELECT kode, nama FROM products WHERE is_active=1 ORDER BY kode");
                        ?>
                        <div class="form-group" style="margin-bottom:14px;">
                            <label style="color:#065f46; font-weight:700;">Mutu / Tipe Beton yang Disepakati *</label>
                            <select name="mutu_beton" id="mutu_beton_sel">
                                <option value="">-- Pilih Mutu Beton --</option>
                                <?php while($pm = $prod_mkt->fetch_assoc()): ?>
                                <option value="<?= $pm['kode'] ?>"><?= $pm['kode'] ?> — <?= htmlspecialchars($pm['nama']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label style="color:#065f46; font-weight:700;">Volume Pasti (m³) *</label>
                            <input type="number" name="volume_pasti" id="volume_pasti_inp" min="0" step="0.01" placeholder="Contoh: 120.5">
                            <p class="form-note" style="color:#16a34a;">Volume yang sudah disepakati dengan kontraktor. Akan otomatis mengisi form pesanan.</p>
                        </div>
                    </div>
                    <div class="form-group"><label>Contact Person *</label><input type="text" name="contact_person" required></div>
                    <div class="form-group"><label>Tanggal *</label><input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required></div>
                </div>
                <hr>
                <div class="form-section">
                    <h3 class="section-title">Aktivitas & Lokasi</h3>
                    <div class="form-group"><label>Nama Marketing *</label><input type="text" name="nama_marketing" value="<?= htmlspecialchars($_SESSION['admin_name']) ?>" required></div>
                    <div class="form-group">
                        <label>Wilayah / Lokasi Promosi *</label>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="wilayah_input" name="wilayah" readonly required style="flex: 1;">
                            <button type="button" onclick="requestLocation()" style="padding: 10px 15px; background: #e0f2fe; color: #0284c7; border: 1px solid #7dd3fc; border-radius: 6px; cursor: pointer;">
                                <i class="fas fa-location-crosshairs"></i> Refresh GPS
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Jenis Aktivitas *</label>
                        <select name="jenis_aktivitas" required>
                            <option value="">-- Pilih aktivitas --</option>
                            <option>Promosi ke Developer</option>
                            <option>Kunjungan Proyek</option>
                            <option>Survey Lokasi</option>
                            <option>Presentasi Produk</option>
                            <option>Follow Up Pelanggan</option>
                            <option>Lainnya</option>
                        </select>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="form-group"><label>Jam Mulai</label><input type="time" name="jam_mulai"></div>
                        <div class="form-group"><label>Jam Selesai</label><input type="time" name="jam_selesai"></div>
                    </div>
                    <div class="form-group">
                        <label>Lokasi (Maps)</label>
                        <div id="map"></div>
                        <input type="hidden" name="lat" id="lat"><input type="hidden" name="lng" id="lng">
                    </div>
                    <div class="form-group">
                        <label>Foto Kegiatan *</label>
                        <div id="photo-review-wrap">
                            <div style="color: #2e7d32; font-size: 14px; margin-bottom: 8px; font-weight: bold;"><i class="fas fa-check-circle"></i> Foto Siap</div>
                            <img id="img-preview" src="">
                        </div>
                        <div id="btn-camera" style="border:2px dashed #f39c12; border-radius:8px; padding:35px; text-align:center; cursor:pointer; color:#f39c12; background: #fffcf5;" onclick="document.getElementById('fotoInput').click()">
                            <i class="fas fa-camera fa-2x"></i><br><strong>Buka Kamera</strong>
                        </div>
                        <input type="file" id="fotoInput" accept="image/*" capture="camera" style="display:none;">
                        <input type="hidden" name="foto_watermark" id="foto_watermark">
                    </div>
                    <div class="form-group"><label>Hasil Promosi / Keterangan</label><textarea name="hasil_promosi" style="height: 120px;"></textarea></div>
                </div>
                <div style="display:flex; gap:15px; margin-top: 25px;">
                    <button type="submit" class="btn btn-green btn-large">Kirim Laporan</button>
                    <a href="index.php" class="btn btn-outline btn-large" style="text-align: center; line-height: 20px;">Batal</a>
                </div>
            </div>
        </form>
        </div><!-- /form-center-wrap -->
    </main>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
// Toggle field Deal
function toggleDealFields(status) {
    const box   = document.getElementById('dealFields');
    const mutu  = document.getElementById('mutu_beton_sel');
    const volp  = document.getElementById('volume_pasti_inp');
    if (status === 'Deal') {
        box.style.display  = 'block';
        mutu.required      = true;
        volp.required      = true;
    } else {
        box.style.display  = 'none';
        mutu.required      = false;
        volp.required      = false;
    }
}

const map = L.map('map').setView([-6.200000, 106.816666], 13); 
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
let marker = L.marker([-6.200000, 106.816666], { draggable: true }).addTo(map);
let watchId, fallbackTimeout;
let bestLat = 0, bestLng = 0, bestAkurasi = 999999;

function getAddress(lat, lng) {
    document.getElementById('wilayah_input').value = "Menerjemahkan alamat detail...";
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
        .then(r => r.json())
        .then(d => { 
            if (d && d.address) {
                const a = d.address;
                
                // Menangkap berbagai kemungkinan kunci dari API Nominatim
                const jalan = a.road || a.pedestrian || a.path || a.suburb || "";
                const nomor = a.house_number ? " No. " + a.house_number : "";
                const desa = a.village || a.suburb || a.hamlet || a.township || "";
                const kecamatan = a.city_district || a.district || a.municipality || "";
                const kotaKab = a.city || a.regency || a.county || "";
                const provinsi = a.state || "";
                const kodepos = a.postcode || "";

                let komponen = [
                    jalan + nomor,
                    desa,
                    kecamatan,
                    kotaKab,
                    provinsi,
                    kodepos
                ].filter(x => x && x.trim() !== "");

                let hasil = komponen.join(", ");
                document.getElementById('wilayah_input').value = hasil || d.display_name;
            }
        }).catch(() => { document.getElementById('wilayah_input').value = "Gagal mengambil alamat otomatis"; });
}

function updatePosition(lat, lng) {
    map.setView([lat, lng], 17);
    marker.setLatLng([lat, lng]);
    document.getElementById('lat').value = lat;
    document.getElementById('lng').value = lng;
}

marker.on('dragend', function() {
    const p = marker.getLatLng();
    updatePosition(p.lat, p.lng);
    if (watchId) navigator.geolocation.clearWatch(watchId);
    if (fallbackTimeout) clearTimeout(fallbackTimeout);
    getAddress(p.lat, p.lng);
});

function requestLocation() {
    document.getElementById('wilayah_input').value = "Mencari sinyal GPS terbaik...";
    bestAkurasi = 999999;
    if (navigator.geolocation) {
        if (watchId) navigator.geolocation.clearWatch(watchId);
        if (fallbackTimeout) clearTimeout(fallbackTimeout);

        fallbackTimeout = setTimeout(() => {
            if (watchId) navigator.geolocation.clearWatch(watchId);
            if (bestLat !== 0) { updatePosition(bestLat, bestLng); getAddress(bestLat, bestLng); }
        }, 15000);

        watchId = navigator.geolocation.watchPosition((p) => {
            const lat = p.coords.latitude, lng = p.coords.longitude, accu = Math.round(p.coords.accuracy);
            if (accu < bestAkurasi) { bestAkurasi = accu; bestLat = lat; bestLng = lng; updatePosition(lat, lng); }
            if (accu <= 20) { 
                navigator.geolocation.clearWatch(watchId); clearTimeout(fallbackTimeout); getAddress(lat, lng); 
            } else { 
                document.getElementById('wilayah_input').value = `Menajamkan lokasi... (${accu}m)`; 
            }
        }, () => { document.getElementById('wilayah_input').value = "GPS Gagal. Geser pin manual."; }, { enableHighAccuracy: true, maximumAge: 0 });
    }
}

requestLocation();

document.getElementById('fotoInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(event) {
        const img = new Image();
        img.onload = function() {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const maxW = 1024;
            const scale = maxW / img.width;
            canvas.width = (img.width > maxW) ? maxW : img.width;
            canvas.height = (img.width > maxW) ? img.height * scale : img.height;
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

            const skrg = new Date();
            const teks = skrg.toLocaleDateString('id-ID') + " " + skrg.toLocaleTimeString('id-ID');
            const fSize = Math.round(canvas.width / 40);
            ctx.font = fSize + "px Arial";
            ctx.fillStyle = "rgba(50, 50, 50, 0.6)";
            ctx.fillRect(canvas.width - ctx.measureText(teks).width - 20, canvas.height - fSize - 20, ctx.measureText(teks).width + 10, fSize + 10);
            ctx.fillStyle = "white";
            ctx.fillText(teks, canvas.width - ctx.measureText(teks).width - 15, canvas.height - 15);

            const finalData = canvas.toDataURL('image/jpeg', 0.7);
            document.getElementById('foto_watermark').value = finalData;
            document.getElementById('photo-review-wrap').style.display = 'block';
            document.getElementById('img-preview').src = finalData;
            document.getElementById('btn-camera').style.display = 'none';
        };
        img.src = event.target.result;
    };
    reader.readAsDataURL(file);
});
</script>
</body>
</html>