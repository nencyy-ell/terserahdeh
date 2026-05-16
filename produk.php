<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Produk Kami – PT. Prambanan Beton</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<section class="page-hero">
  <div class="container">
    <h1>Beton Ready Mix <span style="color:var(--gold)">Berkualitas</span></h1>
    <p>Berbagai varian beton ready mix dengan kekuatan berbeda, siap memenuhi kebutuhan proyek Anda dari skala rumahan hingga infrastruktur besar.</p>
  </div>
</section>

<style>
  .pricing-table-wrapper {
    overflow-x: auto;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    border-radius: var(--radius-lg);
  }
  .pricing-table {
    width: 100%;
    min-width: 600px;
    border-collapse: collapse;
    background: var(--white);
    text-align: left;
  }
  .pricing-table th {
    background: var(--green-dark);
    color: var(--white);
    padding: 1.25rem 1.5rem;
    font-size: 0.95rem;
    white-space: nowrap;
  }
  .pricing-table td {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--gray-100);
    transition: background-color 0.2s ease;
    vertical-align: middle;
  }
  .pricing-table tbody tr:last-child td {
    border-bottom: none;
  }
  .pricing-table tbody tr:hover td {
    background: #f4f8f5;
  }
  .grade-badge {
    display: inline-block;
    padding: 0.35rem 0.85rem;
    background: rgba(27, 94, 32, 0.08);
    color: var(--green-dark);
    border-radius: 50px;
    font-weight: 800;
    font-family: 'Plus Jakarta Sans', sans-serif;
    letter-spacing: 0.02em;
  }
  .price-text {
    font-weight: 800;
    color: var(--green-dark);
    font-size: 1.05rem;
    display: flex;
    align-items: center;
    justify-content: flex-end;
  }
</style>

<!-- PRODUK UTAMA -->
<section class="section" style="background:var(--white);">
  <div class="container">
    <div style="text-align:center; margin-bottom:3rem;" class="aos">
      <h2 class="section-title">Pilihan Grade <span>Beton Kami</span></h2>
      <p class="section-desc" style="margin:0.75rem auto 0; text-align:center;">Semua produk menggunakan material pilihan dan diuji di laboratorium kami sebelum pengiriman.</p>
    </div>

    <?php
    $products_featured = [
      [
        'grade' => 'B0',
        'strength' => 'Non-Struktural',
        'color' => 'var(--gray-600)',
        'uses' => ['Lantai kerja (Lean Concrete)', 'Penimbunan lubang', 'Struktur non-struktural', 'Pengerasan dasar tanah'],
        'specs' => ['Slump' => '10 &plusmn; 2 cm', 'Umur Beton' => '28 hari', 'Harga (FA)' => 'Rp.715.000,-'],
        'featured' => false,
      ],
      [
        'grade' => 'K-125',
        'strength' => '125 kg/cm²',
        'color' => 'var(--green-mid)',
        'uses' => ['Pengecoran non-struktural', 'Pondasi ringan', 'Lantai kerja', 'Jalan setapak'],
        'specs' => ['Slump' => '10 &plusmn; 2 cm', 'Umur Beton' => '28 hari', 'Harga (FA)' => 'Rp.720.000,-'],
        'featured' => false,
      ],
      [
        'grade' => 'K-150',
        'strength' => '150 kg/cm²',
        'color' => 'var(--green-accent)',
        'uses' => ['Jalan lingkungan kecil', 'Pondasi ringan', 'Struktur bangunan sederhana', 'Lantai garasi'],
        'specs' => ['Slump' => '10 &plusmn; 2 cm', 'Umur Beton' => '28 hari', 'Harga (FA)' => 'Rp.735.000,-'],
        'featured' => false,
      ],
      [
        'grade' => 'K-175',
        'strength' => '175 kg/cm²',
        'color' => 'var(--gold-dim)',
        'uses' => ['Jalan lingkungan', 'Pengecoran rumah 1 lantai', 'Slab lantai ringan', 'Area parkir motor'],
        'specs' => ['Slump' => '10 &plusmn; 2 cm', 'Umur Beton' => '28 hari', 'Harga (FA)' => 'Rp.750.000,-'],
        'featured' => false,
      ],
      [
        'grade' => 'K-200',
        'strength' => '200 kg/cm²',
        'color' => 'var(--green-mid)',
        'uses' => ['Rumah tinggal 1 lantai', 'Jalan gang / perumahan', 'Ruko sederhana', 'Slab lantai standar'],
        'specs' => ['Slump' => '10 &plusmn; 2 cm', 'Umur Beton' => '28 hari', 'Harga (FA)' => 'Rp.770.000,-'],
        'featured' => false,
      ],
      [
        'grade' => 'K-225',
        'strength' => '225 kg/cm²',
        'color' => 'var(--green-accent)',
        'uses' => ['Rumah tinggal 2 lantai', 'Jalan raya dan jalan lingkungan', 'Trotoar dan jalan setapak', 'Area parkir kendaraan ringan'],
        'specs' => ['Slump' => '10 &plusmn; 2 cm', 'Umur Beton' => '28 hari', 'Harga (FA)' => 'Rp.780.000,-'],
        'featured' => true,
      ],
      [
        'grade' => 'K-250',
        'strength' => '250 kg/cm²',
        'color' => 'var(--gold-dim)',
        'uses' => ['Ruko bertingkat', 'Konstruksi bangunan standar', 'Jalan perumahan', 'Struktur kolom dan balok'],
        'specs' => ['Slump' => '10 &plusmn; 2 cm', 'Umur Beton' => '28 hari', 'Harga (FA)' => 'Rp.800.000,-'],
        'featured' => false,
      ],
      [
        'grade' => 'K-275',
        'strength' => '275 kg/cm²',
        'color' => 'var(--green-mid)',
        'uses' => ['Konstruksi bangunan bertingkat', 'Jalan raya kapasitas sedang', 'Area industri ringan', 'Retaining wall'],
        'specs' => ['Slump' => '10 &plusmn; 2 cm', 'Umur Beton' => '28 hari', 'Harga (FA)' => 'Rp.810.000,-'],
        'featured' => false,
      ],
      [
        'grade' => 'K-300',
        'strength' => '300 kg/cm²',
        'color' => 'var(--green-accent)',
        'uses' => ['Kolom dan balok bangunan', 'Lantai gedung bertingkat', 'Plat lantai dan tangga', 'Struktur bangunan umum'],
        'specs' => ['Slump' => '10 &plusmn; 2 cm', 'Umur Beton' => '28 hari', 'Harga (FA)' => 'Rp.820.000,-'],
        'featured' => true,
      ],
      [
        'grade' => 'K-350',
        'strength' => '350 kg/cm²',
        'color' => 'var(--gold-dim)',
        'uses' => ['Gedung bertingkat tinggi', 'Lantai pabrik & gudang', 'Jalan tol', 'Kolam renang'],
        'specs' => ['Slump' => '10 &plusmn; 2 cm', 'Umur Beton' => '28 hari', 'Harga (FA)' => 'Rp.850.000,-'],
        'featured' => false,
      ],
      [
        'grade' => 'K-400',
        'strength' => '400 kg/cm²',
        'color' => '#c62828',
        'uses' => ['Struktur jembatan', 'Konstruksi berat', 'Fondasi proyek besar', 'Pier dan pile cap'],
        'specs' => ['Slump' => '10 &plusmn; 2 cm', 'Umur Beton' => '28 hari', 'Harga (FA)' => 'Rp.875.000,-'],
        'featured' => true,
      ],
      [
        'grade' => 'K-450',
        'strength' => '450 kg/cm²',
        'color' => 'var(--green-mid)',
        'uses' => ['Jalan tol heavy duty', 'Landasan pacu (Runway)', 'Struktur prategang', 'Konstruksi underpass'],
        'specs' => ['Slump' => '10 &plusmn; 2 cm', 'Umur Beton' => '28 hari', 'Harga (FA)' => 'Rp.895.000,-'],
        'featured' => false,
      ],
      [
        'grade' => 'K-475',
        'strength' => '475 kg/cm²',
        'color' => 'var(--green-accent)',
        'uses' => ['Infrastruktur skala besar', 'Bantalan rel kereta', 'Konstruksi jembatan bentang panjang', 'Rigid pavement'],
        'specs' => ['Slump' => '10 &plusmn; 2 cm', 'Umur Beton' => '28 hari', 'Harga (FA)' => 'Rp.950.000,-'],
        'featured' => false,
      ],
      [
        'grade' => 'K-500',
        'strength' => '500 kg/cm²',
        'color' => '#c62828',
        'uses' => ['Struktur khusus ekstra kuat', 'Jembatan layang (flyover)', 'Bendungan dan infrastruktur besar', 'Proyek dengan beban sangat tinggi'],
        'specs' => ['Slump' => '10 &plusmn; 2 cm', 'Umur Beton' => '28 hari', 'Harga (FA)' => 'Rp.995.000,-'],
        'featured' => false,
      ],
    ];

    foreach ($products_featured as $i => $p):
    $isEven = $i % 2 === 0;
    ?>
    <div class="aos" style="display:grid; grid-template-columns:<?= $isEven ? '1fr 1.5fr' : '1.5fr 1fr' ?>; gap:2.5rem; align-items:center; margin-bottom:4rem; <?= $i < count($products_featured)-1 ? 'padding-bottom:4rem; border-bottom:2px solid var(--gray-100);' : '' ?>">
      <?php if (!$isEven): ?>
      <!-- SPECS SIDE -->
      <div style="background:var(--green-dark); border-radius:var(--radius-lg); padding:2rem; color:var(--white);">
        <div style="font-size:0.75rem; letter-spacing:0.2em; color:rgba(255,255,255,0.5); text-transform:uppercase; margin-bottom:0.5rem;">Spesifikasi Teknis</div>
        <?php foreach ($p['specs'] as $key => $val): ?>
        <div style="display:flex; justify-content:space-between; align-items:center; padding:0.75rem 0; border-bottom:1px solid rgba(255,255,255,0.08);">
          <span style="color:rgba(255,255,255,0.6); font-size:0.88rem;"><?= $key ?></span>
          <span style="color:var(--gold); font-weight:600; font-size:0.9rem;"><?= $val ?></span>
        </div>
        <?php endforeach; ?>
        <div style="margin-top:1.5rem;">
          <a href="/hubungi" class="btn btn-primary" style="width:100%; justify-content:center;">Minta Penawaran Harga</a>
        </div>
      </div>
      <?php endif; ?>

      <!-- INFO SIDE -->
      <div>
        <?php if ($p['featured']): ?>
        <div style="display:inline-flex; background:var(--gold); color:var(--green-dark); font-size:0.72rem; font-weight:800; padding:0.3rem 0.9rem; border-radius:50px; letter-spacing:0.1em; margin-bottom:0.75rem; text-transform:uppercase;">⭐ Paling Diminati</div>
        <?php endif; ?>
        <div style="font-family:'Playfair Display',serif; font-size:3.5rem; font-weight:800; color:<?= $p['color'] ?>; line-height:1;"><?= $p['grade'] ?></div>
        <div style="color:var(--gray-600); font-size:0.95rem; font-weight:500; margin:0.3rem 0 1.5rem;">Kekuatan: <?= $p['strength'] ?></div>
        <div style="font-weight:700; font-size:0.82rem; color:var(--green-dark); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:0.75rem;">Cocok Untuk:</div>
        <ul class="product-uses" style="margin-bottom:2rem;">
          <?php foreach ($p['uses'] as $use): ?>
          <li>
            <div class="check-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
            <?= $use ?>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <?php if ($isEven): ?>
      <!-- SPECS SIDE -->
      <div style="background:var(--green-dark); border-radius:var(--radius-lg); padding:2rem; color:var(--white);">
        <div style="font-size:0.75rem; letter-spacing:0.2em; color:rgba(255,255,255,0.5); text-transform:uppercase; margin-bottom:0.5rem;">Spesifikasi Teknis</div>
        <?php foreach ($p['specs'] as $key => $val): ?>
        <div style="display:flex; justify-content:space-between; align-items:center; padding:0.75rem 0; border-bottom:1px solid rgba(255,255,255,0.08);">
          <span style="color:rgba(255,255,255,0.6); font-size:0.88rem;"><?= $key ?></span>
          <span style="color:var(--gold); font-weight:600; font-size:0.9rem;"><?= $val ?></span>
        </div>
        <?php endforeach; ?>
        <div style="margin-top:1.5rem;">
          <a href="/hubungi" class="btn btn-primary" style="width:100%; justify-content:center;">Minta Penawaran Harga</a>
        </div>
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</section>


<!-- PROSES PEMESANAN -->
<section class="section" style="background:var(--white);">
  <div class="container">
    <div style="text-align:center; margin-bottom:3rem;" class="aos">
      <h2 class="section-title">Proses <span>Pemesanan</span> Mudah</h2>
    </div>
    <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:1.5rem; position:relative;">
      <!-- Line connector -->
      <div style="position:absolute; top:36px; left:calc(12.5% + 20px); right:calc(12.5% + 20px); height:2px; background:linear-gradient(90deg,var(--green-mid),var(--gold)); border-radius:2px; display:none;" class="desktop-line"></div>
      <?php
      $steps = [
        ['1', '📞', 'Hubungi Kami', 'Hubungi via WhatsApp, telepon, atau form kontak untuk konsultasi kebutuhan beton Anda.'],
        ['2', '📐', 'Konsultasi & Estimasi', 'Tim kami membantu menentukan grade beton yang tepat dan menghitung volume kebutuhan.'],
        ['3', '📄', 'Penawaran & Invoice', 'Kami mengirimkan penawaran harga dan invoice resmi. Pembayaran DP untuk konfirmasi.'],
        ['4', '🚛', 'Pengiriman Beton', 'Beton ready mix dikirim ke lokasi proyek sesuai jadwal yang telah disepakati.'],
      ];
      foreach ($steps as $i => $step): ?>
      <div class="aos aos-delay-<?= $i + 1 ?>" style="text-align:center;">
        <div style="width:72px; height:72px; border-radius:50%; background:linear-gradient(135deg,var(--green-mid),var(--green-accent)); color:var(--white); display:flex; align-items:center; justify-content:center; margin:0 auto 1.25rem; font-size:1.5rem; box-shadow:0 8px 24px rgba(27,94,32,0.3); position:relative;">
          <?= $step[1] ?>
          <div style="position:absolute; top:-6px; right:-6px; width:22px; height:22px; background:var(--gold); border-radius:50%; color:var(--green-dark); font-size:0.7rem; font-weight:800; display:flex; align-items:center; justify-content:center;"><?= $step[0] ?></div>
        </div>
        <h4 style="font-family:'Plus Jakarta Sans',sans-serif; font-weight:700; color:var(--green-dark); margin-bottom:0.5rem; font-size:0.95rem;"><?= $step[2] ?></h4>
        <p style="color:var(--gray-600); font-size:0.83rem; line-height:1.6;"><?= $step[3] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="section-sm" style="background:var(--gray-100);">
  <div class="container">
    <div class="cta-banner aos">
      <h2>Butuh Konsultasi Terkait Beton?</h2>
      <p>Tim ahli kami siap membantu Anda memilih grade beton yang tepat untuk proyek Anda.</p>
      <a href="/hubungi" class="btn btn-primary">Konsultasi Sekarang →</a>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
<script src="assets/js/main.js"></script>
</body>
</html>