<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PT. Prambanan Beton – Solusi Beton Berkualitas</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .hero-centered {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      max-width: 820px;
      margin: 0 auto;
      padding: 5rem 2rem 4rem;
    }
    .hero-centered .hero-badge { margin-bottom: 1.5rem; }
    .hero-centered .hero-title {
      font-size: clamp(2.6rem, 6vw, 4.2rem);
      line-height: 1.12;
      margin-bottom: 1.5rem;
    }
    .hero-centered .hero-subtitle {
      max-width: 620px;
      font-size: 1.05rem;
      line-height: 1.75;
      margin-bottom: 2rem;
      color: rgba(255,255,255,0.8);
    }
    .hero-centered .hero-actions {
      display: flex;
      gap: 1rem;
      justify-content: center;
      flex-wrap: wrap;
      margin-bottom: 2.5rem;
    }

    /* Hero background image */
    .hero {
      position: relative;
    }
    .hero-bg-img {
      position: absolute;
      inset: 0;
      background-image: url('assets/images/hero.jpg');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      z-index: 0;
    }
    .hero-bg-img::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(
        to bottom,
        rgba(8, 25, 10, 0.78) 0%,
        rgba(10, 30, 12, 0.70) 60%,
        rgba(8, 25, 10, 0.85) 100%
      );
    }
    .hero-bg-pattern,
    .hero-grid-overlay {
      z-index: 1;
    }
    .hero-content {
      position: relative;
      z-index: 2;
    }

    /* PRELOADER MASKOT */
    #preloader {
      position: fixed;
      inset: 0;
      background: rgba(10, 31, 11, 0.4);
      backdrop-filter: blur(15px);
      -webkit-backdrop-filter: blur(15px);
      z-index: 999999;
      display: flex;
      justify-content: center;
      align-items: center;
      transition: opacity 0.8s ease, visibility 0.8s ease;
    }
    .mascot-loader-container {
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .mascot-img {
      width: 600px;
      max-width: 95vw;
      height: auto;
      filter: drop-shadow(0 20px 30px rgba(0,0,0,0.6));
      animation: mascotBounce 1.5s cubic-bezier(0.28, 0.84, 0.42, 1) infinite;
      transform-origin: bottom center;
    }
    .mascot-shadow {
      width: 320px;
      height: 30px;
      background: rgba(0, 0, 0, 0.4);
      border-radius: 50%;
      margin-top: 20px;
      animation: shadowPulse 1.5s cubic-bezier(0.28, 0.84, 0.42, 1) infinite;
    }
    .mascot-text {
      margin-top: 2rem;
      font-family: 'Playfair Display', serif;
      font-size: clamp(1.8rem, 4vw, 2.8rem);
      font-weight: 800;
      color: var(--white);
      text-align: center;
      text-shadow: 0 4px 10px rgba(0,0,0,0.6);
      animation: textFadeUp 0.8s ease-out 0.2s both;
    }
    .mascot-subtext {
      font-size: clamp(1rem, 2vw, 1.25rem);
      color: var(--gold);
      margin-top: 0.5rem;
      text-align: center;
      text-shadow: 0 2px 8px rgba(0,0,0,0.8);
      animation: textFadeUp 0.8s ease-out 0.5s both;
    }
    @keyframes mascotBounce {
      0%, 100% { transform: translateY(0) scaleY(1); }
      40% { transform: translateY(-40px) scaleY(1.05); }
      50% { transform: translateY(-45px) scaleY(1.05); }
      80% { transform: translateY(0) scaleY(0.95); }
    }
    @keyframes shadowPulse {
      0%, 100% { transform: scale(1); opacity: 0.6; }
      40% { transform: scale(0.5); opacity: 0.2; }
      50% { transform: scale(0.4); opacity: 0.1; }
    }
    @keyframes textFadeUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    body.loaded #preloader {
      opacity: 0;
      visibility: hidden;
      pointer-events: none;
    }
  </style>
</head>
<body>

<!-- ==================== PRELOADER ==================== -->
<div id="preloader">
  <div class="mascot-loader-container">
    <img src="assets/images/maskot.png" alt="Maskot Prama" class="mascot-img">
    <div class="mascot-shadow"></div>
    <div class="mascot-text">Halo, perkenalkan saya Prama!</div>
    <div class="mascot-subtext">Prama siap membantu kelancaran proyek beton Anda.</div>
  </div>
</div>

<?php include 'includes/navbar.php'; ?>

<!-- ==================== HERO ==================== -->
<section class="hero">
  <div class="hero-bg-img"></div>
  <div class="hero-bg-pattern"></div>
  <div class="hero-grid-overlay"></div>
  <div class="hero-content hero-centered">
    <div class="hero-badge">
      <span class="hero-badge-dot"></span>
      Terpercaya Sejak 2022 · Jawa Timur
    </div>
    <h1 class="hero-title">
      Solusi Beton <span>Ready Mix</span><br>
      Terbaik Anda
    </h1>
    <p class="hero-subtitle">
      PT. Prambanan Beton menyediakan beton berkualitas tinggi sesuai SNI untuk berbagai proyek konstruksi — dari gedung bertingkat, jalan raya, hingga infrastruktur publik di seluruh Jawa Timur.
    </p>
    <div class="hero-actions">
      <a href="/hubungi" class="btn btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.64A2 2 0 012 1h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 8.09a16 16 0 006 6l.36-.36a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
        Hubungi Kami
      </a>
      <a href="/proyek" class="btn btn-outline">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
        Lihat Proyek
      </a>
    </div>
  </div>
</section>

<!-- ==================== STATS ==================== -->
<div class="stats-strip">
  <div class="stats-grid">
    <?php
    $stats = [
      ['icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 17V7m6 10V11"/></svg>', 'num' => 20, 'label' => 'Proyek Selesai'],
      ['icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 3h15v13H1zM16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>', 'num' => 10, 'label' => 'Armada Truk'],
      ['icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg>', 'num' => 5, 'label' => 'Tahun Pengalaman'],
      ['icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>', 'num' => 35, 'label' => 'Tim Profesional'],
    ];
    foreach ($stats as $s): ?>
    <div class="stat-item aos">
      <div class="stat-icon"><?= $s['icon'] ?></div>
      <div class="stat-number" data-target="<?= $s['num'] ?>">0+</div>
      <div class="stat-label"><?= $s['label'] ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ==================== ABOUT SNIPPET ==================== -->
<section class="section" style="background: var(--white);">
  <div class="container">
    <div class="profil-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:4rem; align-items:center;">
      <div class="aos">
        <div class="section-label">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>
          Tentang Kami
        </div>
        <h2 class="section-title">Pengalaman & Kualitas yang <span>Dapat Dipercaya</span></h2>
        <p class="section-desc" style="margin-bottom:1.5rem;">Kami adalah produsen beton ready mix terpercaya dengan pengalaman lebih dari 5 tahun dalam industri konstruksi. Berlokasi strategis di Jember, Jawa Timur, untuk melayani berbagai wilayah dengan cepat dan efisien.</p>
        <p class="section-desc" style="margin-bottom:2rem;">Didukung tim profesional, peralatan modern, dan kontrol kualitas ketat, kami berkomitmen memberikan produk terbaik untuk kesuksesan proyek konstruksi Anda.</p>
        <a href="/profil" class="btn btn-green">
          Selengkapnya
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
      </div>
      <div class="aos aos-delay-2">
        <!-- Foto pabrik / operasional -->
        <div style="border-radius:20px; overflow:hidden; background:linear-gradient(135deg,#e8f5e9,#f9fbe7); height:360px; display:flex; align-items:center; justify-content:center; position:relative; box-shadow: var(--shadow-lg);">
          <img src="assets/images/pabrik.jpg" alt="Pabrik PT Prambanan Beton" style="width:100%;height:100%;object-fit:cover;">
          <div style="position:absolute; bottom:1.5rem; left:1.5rem; background:var(--green-mid); color:var(--white); padding:0.75rem 1.25rem; border-radius:10px; font-size:0.82rem; font-weight:600; box-shadow:0 6px 18px rgba(27,94,32,0.3);">
            ✅ Bersertifikat SNI & ISO 9001:2015
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ==================== WHY US ==================== -->
<section class="section" style="background:var(--gray-100);">
  <div class="container">
    <div style="text-align:center; margin-bottom:3rem;" class="aos">
      <div class="section-label" style="margin: 0 auto 1rem;">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        Keunggulan Kami
      </div>
      <h2 class="section-title">Mengapa Memilih <span>Prambanan Beton?</span></h2>
    </div>
    <div class="why-grid">
      <?php
      $whys = [
        ['🎯', 'Kualitas Terjamin SNI', 'Setiap batch beton diuji secara ketat di laboratorium kami untuk memenuhi standar nasional Indonesia.'],
        ['⚡', 'Pengiriman Tepat Waktu', 'Armada truk mixer modern kami siap mengantarkan beton ke lokasi proyek sesuai jadwal yang disepakati.'],
        ['💰', 'Harga Kompetitif', 'Kami menawarkan harga terbaik tanpa mengorbankan kualitas, dengan opsi penawaran khusus untuk proyek besar.'],
        ['🔬', 'Teknologi Modern', 'Batching plant otomatis dengan kontrol komposisi presisi memastikan konsistensi kualitas di setiap pengiriman.'],
        ['👥', 'Tim Berpengalaman', 'Lebih dari 35 profesional berpengalaman siap mendampingi proyek Anda dari konsultasi hingga penyelesaian.'],
        ['🌿', 'Ramah Lingkungan', 'Proses produksi kami mengutamakan efisiensi energi dan pengelolaan limbah yang bertanggung jawab.'],
      ];
      foreach ($whys as $i => $w): ?>
      <div class="why-card aos aos-delay-<?= ($i % 3) + 1 ?>">
        <div class="why-icon">
          <svg viewBox="0 0 24 24" fill="currentColor"><text y="20" font-size="18"><?= $w[0] ?></text></svg>
        </div>
        <div class="why-title"><?= $w[1] ?></div>
        <div class="why-desc"><?= $w[2] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ==================== CTA ==================== -->
<section class="section-sm" style="background:var(--gray-100);">
  <div class="container">
    <div class="cta-banner aos">
      <h2>Siap Memulai Proyek Anda?</h2>
      <p>Konsultasikan kebutuhan beton Anda bersama tim ahli kami. Kami siap memberikan penawaran terbaik.</p>
      <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
        <a href="/hubungi" class="btn btn-primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
          WhatsApp Sekarang
        </a>
        <a href="/proyek" class="btn btn-outline">Lihat Portofolio</a>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

<script src="assets/js/main.js"></script>
<script>
  // Script untuk menghilangkan preloader animasi maskot setelah halaman dimuat
  window.addEventListener('load', () => {
    // Delay ditambahkan agar animasi terlihat lebih lama (3 detik)
    setTimeout(() => {
      document.body.classList.add('loaded');
    }, 3000); 
  });
</script>
</body>
</html>