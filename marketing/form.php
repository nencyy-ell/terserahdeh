<?php
require_once '../includes/config.php';
requireLogin();
if ($_SESSION['admin_role'] !== 'marketing') {
    header("Location: index.php");
    exit;
}
$currentPage = 'marketing';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // SIMPAN FOTO: Hasil dari kamera yang sudah dikompres & watermark
    $nama_file_foto = '';
    if (!empty($_POST['foto_watermark'])) {
        $foto_data = $_POST['foto_watermark'];
        $foto_data = str_replace('data:image/jpeg;base64,', '', $foto_data);
        $foto_data = str_replace(' ', '+', $foto_data);
        $img_decoded = base64_decode($foto_data);
        
        $nama_file_foto = 'MKT_' . time() . '.jpg';
        // Pastikan folder uploads sudah ada
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

    $sql = "INSERT INTO marketing_reports (nama_marketing,nama_kontraktor,nama_proyek,alamat_proyek,jenis_proyek,status_proyek,estimasi_volume,contact_person,wilayah,jenis_aktivitas,jam_mulai,jam_selesai,lat,lng,hasil_promosi,tanggal, foto) 
            VALUES ('{$data['nama_marketing']}','{$data['nama_kontraktor']}','{$data['nama_proyek']}','{$data['alamat_proyek']}','{$data['jenis_proyek']}','{$data['status_proyek']}',{$data['estimasi_volume']},'{$data['contact_person']}','{$data['wilayah']}','{$data['jenis_aktivitas']}','{$data['jam_mulai']}','{$data['jam_selesai']}',{$data['lat']},{$data['lng']},'{$data['hasil_promosi']}','{$data['tanggal']}', '$nama_file_foto')";

    if ($conn->query($sql)) {
        // Log aktivitas
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
        .main-content { width: 100%; padding: 15px; box-sizing: border-box; }
        .full-card { width: 100%; background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 20px; box-sizing: border-box; }
        .section-title { border-left: 5px solid #f39c12; padding-left: 15px; margin-bottom: 20px; color: #333; font-weight: bold; }
        hr { border: 0; border-top: 1px solid #eee; margin: 30px 0; }
        #map { height: 350px; width: 100%; border-radius: 8px; border: 1px solid #ddd; }
        .btn-large { padding: 15px; font-size: 16px; font-weight: bold; flex: 1; border-radius: 8px; cursor: pointer; }
        
        /* Style Review Foto */
        #photo-review-wrap { 
            display: none; 
            margin-top: 10px; 
            padding: 15px; 
            background: #f1f8e9; 
            border: 1px solid #2e7d32; 
            border-radius: 8px; 
            text-align: center;
        }
        #img-preview { 
            max-width: 100%; 
            max-height: 200px; 
            border-radius: 6px; 
            border: 2px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            cursor: pointer;
        }
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
        <div class="page-header">
            <h1>Form Track Marketing</h1>
        </div>

        <?php if (isset($_GET['saved'])): ?>
            <div class="alert alert-success" style="margin-bottom: 20px; background: #dcfce7; color: #166534; padding: 16px; border-radius: 8px; display: flex; align-items: center; gap: 12px; font-weight: 500; border: 1px solid #bbf7d0;">
                <i class="fas fa-check-circle" style="font-size: 20px;"></i>
                Laporan marketing berhasil dikirim!
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="full-card">
                
                <div class="form-section">
                    <h3 class="section-title">Data Proyek</h3>
                    <div class="form-group">
                        <label>Nama Kontraktor *</label>
                        <input type="text" name="nama_kontraktor" placeholder="Masukkan nama kontraktor" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Proyek *</label>
                        <input type="text" name="nama_proyek" placeholder="Masukkan nama proyek" required>
                    </div>
                    <div class="form-group">
                        <label>Alamat Proyek *</label>
                        <input type="text" name="alamat_proyek" placeholder="Masukkan alamat proyek" required>
                    </div>
                    <div class="form-group">
                        <label>Jenis Proyek *</label>
                        <div class="radio-group">
                            <?php foreach(['Pemerintah','BUMN','Swasta','Retail','Lainnya'] as $j): ?>
                            <label class="radio-item">
                                <input type="radio" name="jenis_proyek" value="<?= $j ?>" required> <?= $j ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Status *</label>
                        <div class="radio-group">
                            <?php foreach(['Follow Up','Nego','Deal','Loss'] as $s): ?>
                            <label class="radio-item">
                                <input type="radio" name="status_proyek" value="<?= $s ?>" required> <?= $s ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Estimasi Volume (m&sup3;) *</label>
                        <input type="number" name="estimasi_volume" placeholder="Masukkan estimasi volume" min="0">
                    </div>
                    <div class="form-group">
                        <label>Contact Person *</label>
                        <input type="text" name="contact_person" placeholder="Nama orang yang dapat dihubungi" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal *</label>
                        <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <hr>

                <div class="form-section">
                    <h3 class="section-title">Aktivitas & Lokasi</h3>
                    <div class="form-group">
                        <label>Nama Marketing *</label>
                        <input type="text" name="nama_marketing" value="<?= htmlspecialchars($_SESSION['admin_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Wilayah / Lokasi Promosi *</label>
                        <input type="text" id="wilayah_input" name="wilayah" placeholder="Mendeteksi lokasi otomatis..." readonly required>
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
                        <div class="form-group">
                            <label>Jam Mulai</label>
                            <input type="time" name="jam_mulai" max="23:59">
                        </div>
                        <div class="form-group">
                            <label>Jam Selesai</label>
                            <input type="time" name="jam_selesai" max="23:59">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Lokasi (Maps)</label>
                        <div id="map"></div>
                        <input type="hidden" name="lat" id="lat">
                        <input type="hidden" name="lng" id="lng">
                    </div>

                    <div class="form-group">
                        <label>Foto Kegiatan *</label>
                        
                        <div id="photo-review-wrap">
                            <div style="color: #2e7d32; font-size: 14px; margin-bottom: 8px; font-weight: bold;">
                                <i class="fas fa-check-circle"></i> Foto Siap Dikirim
                            </div>
                            <img id="img-preview" src="" title="Klik untuk ganti foto">
                            <div style="font-size: 11px; color: #666; margin-top: 5px;">Klik gambar jika ingin memotret ulang</div>
                        </div>

                        <div id="btn-camera" style="border:2px dashed #f39c12; border-radius:8px; padding:35px; text-align:center; cursor:pointer; color:#f39c12; background: #fffcf5;" onclick="document.getElementById('fotoInput').click()">
                            <i class="fas fa-camera fa-2x"></i><br><strong>Buka Kamera</strong>
                        </div>
                        
                        <input type="file" id="fotoInput" accept="image/*" capture="camera" style="display:none;">
                        <input type="hidden" name="foto_watermark" id="foto_watermark">
                    </div>

                    <div class="form-group">
                        <label>Hasil Promosi / Keterangan</label>
                        <textarea name="hasil_promosi" placeholder="Tulis hasil kunjungan..." style="height: 120px;"></textarea>
                    </div>
                </div>

                <div style="display:flex; gap:15px; margin-top: 25px;">
                    <button type="submit" class="btn btn-green btn-large">Kirim Laporan</button>
                    <a href="index.php" class="btn btn-outline btn-large" style="text-align: center; line-height: 20px;">Batal</a>
                </div>

            </div>
        </form>
    </main>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
// --- MAPS ---
const map = L.map('map', { dragging: false, tap: false, touchZoom: false, scrollWheelZoom: false }).setView([0,0], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
let marker = L.marker([0,0]).addTo(map);

if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(p) {
        const lat = p.coords.latitude; const lng = p.coords.longitude;
        map.setView([lat, lng], 17);
        marker.setLatLng([lat, lng]);
        document.getElementById('lat').value = lat;
        document.getElementById('lng').value = lng;
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(r => r.json()).then(d => { document.getElementById('wilayah_input').value = d.display_name; });
    }, null, { enableHighAccuracy: true });
}

// --- WATERMARK & REVIEW KECIL ---
document.getElementById('fotoInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(event) {
        const img = new Image();
        img.onload = function() {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            // 1. AUTO KOMPRES (MAX 1024PX)
            const maxW = 1024;
            const scale = maxW / img.width;
            canvas.width = (img.width > maxW) ? maxW : img.width;
            canvas.height = (img.width > maxW) ? img.height * scale : img.height;
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

            // 2. WATERMARK KOTAK ABU (POJOK KANAN BAWAH)
            const skrg = new Date();
            const tgl = skrg.getDate().toString().padStart(2, '0') + '/' + (skrg.getMonth() + 1).toString().padStart(2, '0') + '/' + skrg.getFullYear();
            const jam = skrg.getHours().toString().padStart(2, '0') + ':' + skrg.getMinutes().toString().padStart(2, '0');
            const teks1 = "diambil pada:";
            const teks2 = tgl + " " + jam;

            const fSize = Math.round(canvas.width / 50);
            const fSizeSmall = Math.round(fSize * 0.6);
            ctx.font = fSize + "px Arial";
            const wTeks = ctx.measureText(teks2).width;
            const p = Math.round(canvas.width / 40);
            const kW = wTeks + (p * 2);
            const kH = (fSize + fSizeSmall) + (p * 2);

            ctx.fillStyle = "rgba(50, 50, 50, 0.6)";
            ctx.fillRect(canvas.width - kW - p, canvas.height - kH - p, kW, kH);
            ctx.fillStyle = "white";
            ctx.textAlign = "left";
            ctx.font = fSizeSmall + "px Arial";
            ctx.fillText(teks1, canvas.width - kW, canvas.height - kH + fSizeSmall);
            ctx.font = fSize + "px Arial";
            ctx.fillText(teks2, canvas.width - kW, canvas.height - p - 5);

            // 3. SIMPAN DATA
            const finalData = canvas.toDataURL('image/jpeg', 0.6);
            document.getElementById('foto_watermark').value = finalData;

            // 4. REVIEW TAMPILAN
            document.getElementById('photo-review-wrap').style.display = 'block';
            document.getElementById('img-preview').src = finalData;
            document.getElementById('btn-camera').style.display = 'none';
        };
        img.src = event.target.result;
    };
    reader.readAsDataURL(file);
});

// Klik gambar review untuk foto ulang
document.getElementById('img-preview').onclick = function() {
    document.getElementById('fotoInput').click();
};
</script>
</body>
</html>