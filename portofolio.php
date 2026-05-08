<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portofolio – PT. Prambanan Beton</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<section class="page-hero">
  <div class="container">
    <h1>Jejak <span style="color:var(--gold)">Karya</span> Kami</h1>
    <p>PT. Prambanan Beton telah berkontribusi dalam berbagai proyek infrastruktur, perumahan, dan fasilitas umum di Jawa Timur.</p>
  </div>
</section>

<!-- STATS ROW -->
<div class="stats-strip">
  <div class="stats-grid">
    <?php
    $stats = [
      ['20', '+', 'Proyek Selesai'],
      ['4250', '+', 'm³ Beton Terkirim'],
      ['15', '+', 'Klien Aktif'],
      ['100', '%', 'Tingkat Kepuasan'],
    ];
    foreach ($stats as $s): ?>
    <div class="stat-item">
      <div class="stat-number" style="display:flex; justify-content:center; align-items:baseline;">
        <span class="counter" data-target="<?= $s[0] ?>">0</span><span><?= $s[1] ?></span>
      </div>
      <div class="stat-label"><?= $s[2] ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- PROYEK UNGGULAN -->
<section class="section" style="background:var(--white);">
  <div class="container">
    <div style="display:flex; align-items:flex-end; justify-content:space-between; margin-bottom:3rem; flex-wrap:wrap; gap:1rem;" class="aos">
      <div>
        <div class="section-label">Proyek Unggulan</div>
        <h2 class="section-title">Proyek <span>Terbaru</span></h2>
      </div>
      <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
        <button class="btn" style="background:var(--green-mid);color:var(--white);font-size:0.82rem;padding:0.5rem 1rem;" onclick="filterProyek('semua')">Semua</button>
        <button class="btn" style="background:var(--gray-100);color:var(--gray-600);font-size:0.82rem;padding:0.5rem 1rem;" onclick="filterProyek('gedung')">Gedung</button>
        <button class="btn" style="background:var(--gray-100);color:var(--gray-600);font-size:0.82rem;padding:0.5rem 1rem;" onclick="filterProyek('infrastruktur')">Infrastruktur</button>
        <button class="btn" style="background:var(--gray-100);color:var(--gray-600);font-size:0.82rem;padding:0.5rem 1rem;" onclick="filterProyek('koperasi')">Koperasi</button>
      </div>
    </div>

    <div class="porto-grid" id="proyekGrid">
      <?php
      $projects = [
        [
          'name' => 'Developer Bernady Land',
          'desc' => 'Suplai beton ready mix untuk proyek pengembangan kawasan perumahan Bernady Land Jember.',
          'year' => '2024',
          'volume' => '-',
          'location' => 'Jember',
          'grade' => 'K-225',
          'category' => 'gedung',
          'emoji' => '🏘️',
        ],
        [
          'name' => 'RS Muhammadiyah Jember',
          'desc' => 'Pembangunan Gedung RS Universitas Muhammadiyah Jember dengan spesifikasi beton mutu tinggi.',
          'year' => '2024',
          'volume' => '-',
          'location' => 'Jember',
          'grade' => 'K-300',
          'category' => 'gedung',
          'emoji' => '🏥',
        ],
        [
          'name' => 'Jaringan Irigasi D.I Sembah',
          'desc' => 'Proyek infrastruktur pengairan Jaringan Irigasi D.I Sembah untuk mendukung pertanian daerah.',
          'year' => '2024',
          'volume' => '-',
          'location' => 'Jember',
          'grade' => 'K-225',
          'category' => 'infrastruktur',
          'emoji' => '🌊',
        ],
        [
          'name' => 'Jalan Beton Grobogan',
          'desc' => 'Proyek pengecoran jalan beton di kawasan Grobogan untuk memperlancar akses transportasi.',
          'year' => '2024',
          'volume' => '-',
          'location' => 'Lumajang',
          'grade' => 'K-300',
          'category' => 'infrastruktur',
          'emoji' => '🛣️',
        ],
        [
          'name' => 'Peningkatan Jalan Jatiroto',
          'desc' => 'Peningkatan jalan beton di Jatiroto untuk fasilitas transportasi yang lebih kuat dan awet.',
          'year' => '2024',
          'volume' => '-',
          'location' => 'Lumajang',
          'grade' => 'K-300',
          'category' => 'infrastruktur',
          'emoji' => '🛣️',
        ],
        [
          'name' => 'Gedung Sekolah MAN 2',
          'desc' => 'Proyek pembangunan fasilitas pendidikan Gedung Sekolah MAN 2 Jember.',
          'year' => '2024',
          'volume' => '-',
          'location' => 'Jember',
          'grade' => 'K-250',
          'category' => 'gedung',
          'emoji' => '🏫',
        ],
        [
          'name' => 'Gedung RSUD Haryoto',
          'desc' => 'Pembangunan fasilitas kesehatan gedung RSUD Haryoto.',
          'year' => '2024',
          'volume' => '-',
          'location' => 'Lumajang',
          'grade' => 'K-300',
          'category' => 'gedung',
          'emoji' => '🏥',
        ],
        [
          'name' => 'Gudang Kopa TTN',
          'desc' => 'Proyek pembangunan fasilitas industri Gudang Kopa TTN.',
          'year' => '2024',
          'volume' => '-',
          'location' => 'Jawa Timur',
          'grade' => 'K-300',
          'category' => 'gedung',
          'emoji' => '🏭',
        ],
        [
          'name' => 'Jalan Beton Bondowoso',
          'desc' => 'Pengecoran proyek jalan beton di wilayah Bondowoso.',
          'year' => '2024',
          'volume' => '-',
          'location' => 'Bondowoso',
          'grade' => 'K-250',
          'category' => 'infrastruktur',
          'emoji' => '🛣️',
        ],
        [
          'name' => 'Gedung UIN KHAS Jember',
          'desc' => 'Suplai beton ready mix untuk pembangunan Gedung UIN KHAS Jember.',
          'year' => '2024',
          'volume' => '-',
          'location' => 'Jember',
          'grade' => 'K-300',
          'category' => 'gedung',
          'emoji' => '🏫',
        ],
        [
          'name' => 'Jember Nusantara',
          'desc' => 'Proyek pembangunan Gedung Jember Nusantara (Dekranasda).',
          'year' => '2024',
          'volume' => '-',
          'location' => 'Jember',
          'grade' => 'K-250',
          'category' => 'gedung',
          'emoji' => '🏛️',
        ],
        [
          'name' => 'RS Muhammadiyah Lumajang',
          'desc' => 'Proyek pembangunan Gedung RS Muhammadiyah di Lumajang.',
          'year' => '2024',
          'volume' => '-',
          'location' => 'Lumajang',
          'grade' => 'K-300',
          'category' => 'gedung',
          'emoji' => '🏥',
        ],
      ];
      $images = ['material.jpg', 'pabrik.jpg', 'hero.jpg', 'betoncetak.jpg', 'observasi.jpg', 'hero.jpg'];
      foreach ($projects as $i => $p): 
        $img = $images[$i % count($images)];
      ?>
      <div class="porto-card aos aos-delay-<?= ($i % 3) + 1 ?>" data-category="<?= $p['category'] ?>">
        <div class="porto-img-wrap">
          <img src="assets/images/<?= $img ?>" alt="<?= $p['name'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        <div class="porto-body">
          <div class="porto-title"><?= $p['name'] ?></div>
          <p style="color:var(--gray-600); font-size:0.86rem; line-height:1.6; margin-bottom:1rem;"><?= $p['desc'] ?></p>
          <div class="porto-meta">
            <span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              Tahun: <?= $p['year'] ?>
            </span>
            <span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
              <?= $p['location'] ?>
            </span>
            <span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
              Volume: <?= $p['volume'] ?>
            </span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ULASAN PELANGGAN -->
<style>
  .reviews-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; align-items: stretch; }
  .review-card { 
    display: flex; flex-direction: column; 
    border: 1px solid var(--gray-200);
    padding: 1.5rem;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  }
  .review-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
  .review-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; }
  .review-author { display: flex; align-items: center; gap: 0.75rem; }
  .review-author-img { width: 40px; height: 40px; border-radius: 50%; background: var(--green-mid); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem; }
  .review-meta { display: flex; flex-direction: column; }
  .review-name { font-weight: 600; color: var(--gray-900); font-size: 1rem; margin-bottom: 0.1rem; }
  .review-role { font-size: 0.8rem; color: var(--gray-500); }
  .review-source { display: flex; align-items: center; gap: 0.25rem; font-size: 0.75rem; color: var(--gray-500); }
  .review-source svg { width: 14px; height: 14px; }
  .review-stars-date { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem; }
  .review-stars { color: var(--gold); font-size: 1.1rem; letter-spacing: 1px; }
  .review-date { font-size: 0.8rem; color: var(--gray-500); }
  .review-text { font-size: 0.95rem; color: var(--gray-700); line-height: 1.6; }
</style>
<section class="section" style="background:var(--gray-50);">
  <div class="container">
    <div style="text-align:center; margin-bottom:3rem;" class="aos">
      <h2 class="section-title">Ulasan <span>Pelanggan</span> Kami</h2>
      <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; gap:4px; margin-top:0.75rem;">
        <div style="display:flex; align-items:center; justify-content:center; gap:8px;">
          <span style="font-weight:700; font-size:2rem; color:var(--gray-900);">4.8</span>
          <div style="display:flex; flex-direction:column; align-items:flex-start;">
            <span style="color:var(--gold); font-size:1.2rem; line-height:1;">★★★★★</span>
            <span style="color:var(--gray-500); font-size:0.85rem; margin-top:2px;">Berdasarkan ulasan di Google Maps</span>
          </div>
        </div>
      </div>
    </div>
    <div class="reviews-grid">
      <?php
      $reviews = [
        ['Budi Santoso', 'Local Guide · 15 ulasan', 5, 'Kualitas beton sangat baik, konsisten setiap pengiriman. Tim driver dan operator ramah dan tepat waktu. Sangat puas dengan layanan Prambanan Beton di Jember!', '1 bulan lalu'],
        ['Siti Aminah', '3 ulasan', 5, 'Sudah 3 proyek bekerja sama dan tidak pernah kecewa. Pengiriman selalu tepat jadwal, kualitas beton sesuai spek yang diminta. Lokasi plant di Gambirono juga strategis.', '2 bulan lalu'],
        ['Ahmad Fadli', 'Local Guide · 42 ulasan', 5, 'Harga kompetitif dengan kualitas yang terjamin. Administrasi dan dokumentasi juga rapi. Akan kami jadikan vendor tetap untuk proyek-proyek selanjutnya di wilayah Tapal Kuda.', '3 minggu lalu'],
        ['Rahmat Hidayat', '12 ulasan', 5, 'Kualitas beton siap pakai (ready mix) dan jasanya memuaskan. Permukaan rata, cepat kering. Customer service responsif. Rekomended untuk konstruksi besar.', '4 bulan lalu'],
      ];
      foreach ($reviews as $i => $r): ?>
      <div class="review-card aos aos-delay-<?= $i + 1 ?>">
        <div class="review-header">
          <div class="review-author">
            <div class="review-author-img"><?= mb_substr($r[0], 0, 1) ?></div>
            <div class="review-meta">
              <span class="review-name"><?= $r[0] ?></span>
              <span class="review-role"><?= $r[1] ?></span>
            </div>
          </div>
          <div class="review-source" title="Ulasan dari Google">
            <!-- Google G logo SVG -->
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
              <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.16v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
              <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.16C1.43 8.55 1 10.22 1 12s.43 3.45 1.16 4.93l3.68-2.84z" fill="#FBBC05"/>
              <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.16 7.07l3.68 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
          </div>
        </div>
        <div class="review-stars-date">
          <div class="review-stars"><?= str_repeat('★', $r[2]) ?></div>
          <div class="review-date"><?= $r[4] ?></div>
        </div>
        <p class="review-text">"<?= $r[3] ?>"</p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section-sm" style="background:var(--gray-100);">
  <div class="container">
    <div class="cta-banner aos">
      <h2>Jadilah Bagian dari Portofolio Kami</h2>
      <p>Hubungi kami untuk mendiskusikan kebutuhan beton proyek Anda berikutnya.</p>
      <a href="kontak.php" class="btn btn-primary">Hubungi Kami Sekarang →</a>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
<script src="assets/js/main.js"></script>
<script>
function filterProyek(category) {
  const cards = document.querySelectorAll('.porto-card');
  const btns = document.querySelectorAll('[onclick^="filterProyek"]');
  btns.forEach(b => { b.style.background = 'var(--gray-100)'; b.style.color = 'var(--gray-600)'; });
  event.target.style.background = 'var(--green-mid)';
  event.target.style.color = 'var(--white)';
  cards.forEach(card => {
    if (category === 'semua' || card.dataset.category === category) {
      card.style.display = '';
      card.style.animation = 'fadeUp 0.4s ease both';
    } else {
      card.style.display = 'none';
    }
  });
}

// ANIMASI COUNTER STATISTIK
document.addEventListener('DOMContentLoaded', () => {
  const counters = document.querySelectorAll('.counter');
  
  const observer = new IntersectionObserver((entries, obs) => {
    entries.forEach(entry => {
      if(entry.isIntersecting) {
        const counter = entry.target;
        const target = +counter.getAttribute('data-target');
        // Kecepatan animasi, sesuaikan agar feel-nya pas
        const duration = 2000; // 2 detik
        const frameRate = 1000 / 60; // 60fps
        const totalFrames = Math.round(duration / frameRate);
        let frame = 0;
        
        const counterInterval = setInterval(() => {
          frame++;
          const progress = frame / totalFrames;
          // Easing easeOutQubic
          const easeProgress = 1 - Math.pow(1 - progress, 3);
          const currentCount = Math.round(target * easeProgress);
          
          counter.innerText = currentCount >= 1000 ? currentCount.toLocaleString('id-ID') : currentCount;
          
          if(frame === totalFrames) {
            clearInterval(counterInterval);
            counter.innerText = target >= 1000 ? target.toLocaleString('id-ID') : target;
          }
        }, frameRate);
        
        obs.unobserve(counter);
      }
    });
  }, { threshold: 0.5 });
  
  counters.forEach(counter => observer.observe(counter));
});
</script>
</body>
</html>