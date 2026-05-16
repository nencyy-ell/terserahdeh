<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tentang Kami – PT. Prambanan Beton</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<!-- PAGE HERO -->
<section class="page-hero">
  <div class="container">
    <h1>Profil PT. Prambanan Beton</h1>
    <p>Mengenal lebih dekat perusahaan dan komitmen kami dalam industri konstruksi Indonesia.</p>
  </div>
</section>

<!-- PROFIL -->
<section class="section" style="background:var(--white);">
  <div class="container">
<div class="profil-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:4rem; align-items:center;">      <div class="aos">
        <div style="border-radius:20px; overflow:hidden; height:400px; box-shadow:var(--shadow-lg); position:relative;">
          <video 
            src="assets/video/senja.mp4" 
            style="width:100%; height:100%; object-fit:cover;" 
            controls 
            autoplay 
            muted 
            loop>
          </video>
          <div style="position:absolute; top:1.5rem; left:1.5rem;">
            <div style="background:var(--gold); color:var(--green-dark); padding:0.5rem 1rem; border-radius:8px; font-size:0.78rem; font-weight:700; letter-spacing:0.08em;">▶ VIDEO PERUSAHAAN</div>
          </div>
        </div>
      </div>
  
      <div class="aos aos-delay-2">
        <h2 class="section-title">PT. Prambanan <span>Beton Indonesia</span></h2>
        <p style="color:var(--gray-600); line-height:1.75; margin-bottom:1rem; text-align:justify;">
          PT. Prambanan Beton Indonesia merupakan perusahaan yang bergerak dalam bidang konstruksi dan infrastruktur terutama untuk penyediaan dan pengerjaan pengadaan beton siap pakai atau <em>readymix concrete</em> dan jasa sewa <em>concrete pump</em>.
        </p>
        <p style="color:var(--gray-600); line-height:1.75; margin-bottom:1rem; text-align:justify;">
          Berdiri sejak tahun 2024, perusahaan kami berlokasi di <strong>Jl. Moch. Seruji No. 331 Desa Gambirono, Bangsal, Kabupaten Jember</strong>. Saat ini perusahaan juga sedang mengembangkan usaha produk turunan beton, seperti:
        </p>
        <ul style="color:var(--gray-600); line-height:1.75; margin-bottom:2rem; padding-left:1.5rem;">
          <li>Pagar Precast</li>
          <li>U-Ditch dan Box Culvert</li>
          <li>Tiang Pancang</li>
        </ul>

        <div style="background:var(--gray-100); padding:1.5rem 2rem; border-radius:var(--radius-md); border-left:4px solid var(--green-mid);">
          <h4 style="color:var(--green-dark); font-family:'Playfair Display',serif; margin-bottom:0.75rem; font-size:1.15rem;">Ready Mix Concrete Plant</h4>
          <div style="display:flex; flex-direction:column; gap:0.5rem; color:var(--gray-600); font-size:0.95rem;">
            <div><strong style="color:var(--green-dark);">Kapasitas:</strong> &plusmn; 250 m&sup3;/hari</div>
            <div><strong style="color:var(--green-dark);">Luas Lahan:</strong> &plusmn; 5 Hektar</div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- VISI MISI -->
<section class="section" style="background:var(--gray-100);">
  <div class="container">
    <div style="text-align:center; margin-bottom:3rem;" class="aos">
      <h2 class="section-title">Visi & <span>Misi</span></h2>
    </div>
    <div class="visi-misi-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:2rem;">
      <!-- VISI -->
      <div class="aos" style="background:var(--green-dark); border-radius:var(--radius-lg); padding:2.5rem; color:var(--white);">
        <div style="width:56px; height:56px; background:rgba(249,168,37,0.2); border-radius:14px; display:flex; align-items:center; justify-content:center; margin-bottom:1.5rem;">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#F9A825" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/><line x1="12" y1="2" x2="12" y2="5"/><line x1="12" y1="19" x2="12" y2="22"/><line x1="2" y1="12" x2="5" y2="12"/><line x1="19" y1="12" x2="22" y2="12"/></svg>
        </div>
        <div style="font-size:0.75rem; letter-spacing:0.2em; color:var(--gold); text-transform:uppercase; font-weight:700; margin-bottom:0.75rem;">VISI</div>
        <p style="color:rgba(255,255,255,0.85); line-height:1.75; font-size:1rem;">
          Menjadi perusahaan unggulan yang mendukung pembangunan di Indonesia dalam industri beton dengan mengedepankan <strong style="color:var(--gold);">kualitas produk dan pelayanan yang terbaik</strong>.
        </p>
      </div>
      <!-- MISI -->
      <div class="aos aos-delay-2" style="background:var(--green-dark); border-radius:var(--radius-lg); padding:2.5rem; color:var(--white);">
        <div style="width:56px; height:56px; background:rgba(249,168,37,0.2); border-radius:14px; display:flex; align-items:center; justify-content:center; margin-bottom:1.5rem;">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#F9A825" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div style="font-size:0.75rem; letter-spacing:0.2em; color:var(--gold); text-transform:uppercase; font-weight:700; margin-bottom:0.75rem;">MISI</div>
        <?php
        $missions = [
          'Menjadi partner terpercaya bagi pelanggan dalam industri beton.',
          'Menumbuhkan budaya kerja dan mengembangkan kompetensi SDM secara professional.',
          'Menciptakan lingkungan kerja yang aman berbasis keselamatan dan kesehatan kerja serta lingkungan.',
          'Menjalin kerjasama strategis dengan mitra kerja yang saling menguntungkan.',
        ];
        foreach ($missions as $i => $m): ?>
        <div style="display:flex; gap:14px; align-items:flex-start; margin-bottom:1rem;">
          <div style="width:28px; height:28px; border-radius:50%; background:rgba(249,168,37,0.25); color:var(--gold); display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700; flex-shrink:0; margin-top:1px;"><?= $i+1 ?></div>
          <p style="color:rgba(255,255,255,0.85); font-size:0.9rem; line-height:1.65;"><?= $m ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- SERTIFIKASI & LEGALITAS -->
<section class="section" style="background:var(--white);">
  <div class="container">
    <div style="text-align:center; margin-bottom:3rem;" class="aos">
      <h2 class="section-title">Legalitas Usaha & <span>Sertifikasi</span></h2>
    </div>
    <div class="cert-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
      <?php
      $certs = [
        ['icon' => '📜', 'name' => 'Akta Pendirian Perusahaan', 'issuer' => 'Notaris Achmad Basith Bravarianto SH., M.Kn Nomor 8', 'year' => '31 Januari 2024', 'pdf' => '#'],
        ['icon' => '⚖️', 'name' => 'SK Kemenkumham', 'issuer' => 'AHU-0010206.AH.01.01.Tahun2024', 'year' => '2024', 'pdf' => '#'],
        ['icon' => '🏢', 'name' => 'Nomor Induk Berusaha (NIB)', 'issuer' => '2602240275053', 'year' => '2024', 'pdf' => '#'],
        ['icon' => '🏅', 'name' => 'ISO 9001', 'issuer' => 'Quality Management System', 'year' => '2024', 'pdf' => 'assets/pdf/sertifikat-iso-9001.pdf'],
        ['icon' => '🌿', 'name' => 'ISO 14001', 'issuer' => 'Environmental Management System', 'year' => '2024', 'pdf' => 'assets/pdf/sertifikat-iso-14001.pdf'],
        ['icon' => '🦺', 'name' => 'ISO 45001', 'issuer' => 'Occupational Health & Safety', 'year' => '2024', 'pdf' => 'assets/pdf/sertifikat-iso-45001.pdf'],
      ];
      foreach ($certs as $i => $c): 
        $hasPdf = $c['pdf'] !== '#';
      ?>
      <a href="<?= $c['pdf'] ?>" <?= $hasPdf ? 'target="_blank"' : 'onclick="event.preventDefault(); alert(\'Dokumen '.$c['name'].' belum tersedia.\')"' ?> class="cert-card aos aos-delay-<?= ($i % 3) + 1 ?>" style="display:flex; flex-direction:column; justify-content:center; height:100%; text-align:center; padding:2rem 1.5rem; text-decoration:none; color:inherit; cursor:<?= $hasPdf ? 'pointer' : 'default' ?>;">
        <div class="cert-icon" style="font-size:2rem; background:none; background:linear-gradient(135deg,var(--gold),var(--gold-dim)); margin:0 auto 1.5rem;">
          <?= $c['icon'] ?>
        </div>
        <div class="cert-name" style="font-size:1.1rem; margin-bottom:0.5rem; color:var(--green-dark);"><?= $c['name'] ?></div>
        <div class="cert-issuer" style="font-size:0.85rem; color:var(--gray-600); margin-bottom:0.5rem;"><?= $c['issuer'] ?></div>
        <?php if($c['year']): ?>
        <div class="cert-year" style="font-size:0.8rem; color:var(--green-mid); font-weight:700;"><?= $c['year'] ?></div>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- MITRA USAHA -->
<section class="section" style="background:var(--gray-100);">
  <div class="container">
    <div style="text-align:center; margin-bottom:3rem;" class="aos">
      <h2 class="section-title">Mitra <span>Usaha</span></h2>
      <p class="section-desc" style="margin:0.75rem auto 0; text-align:center;">Kami telah dipercaya dan bekerja sama dengan berbagai perusahaan terkemuka.</p>
    </div>
    
    <div class="aos" style="display:flex; flex-wrap:wrap; justify-content:center; gap:1rem;">
      <?php
      $mitra = [
        'PT. Tugu Beton Semesta Abadi',
        'PT. Tugu Beton Muda Mandiri',
        'PT. Sumber Urip Sejati',
        'CV Khaya Alam',
        'CV. Putri Melisa Mustika',
        'CV. Robbana Indonesaa',
        'CV Karya Adi Sentosa',
        'PT. Kimia Konstruksi Indonesia',
        'CV. Sembilan Wali',
        'CV. Sumber Rejeki Grup',
        'CV. Innomas Sempurna'
      ];
      foreach ($mitra as $i => $m): ?>
      <div style="background:var(--white); padding:1rem 1.5rem; border-radius:50px; font-weight:600; font-size:0.95rem; color:var(--green-dark); box-shadow:0 4px 15px rgba(0,0,0,0.03); border:1px solid rgba(0,0,0,0.05); display:inline-flex; align-items:center; gap:0.5rem; transition:transform 0.3s ease;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
        <?= $m ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<?php include 'includes/footer.php'; ?>
<script src="assets/js/main.js"></script>
</body>
</html>