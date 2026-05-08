<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar" id="navbar">
  <div class="nav-container">
    <a href="index.php" class="nav-brand">
      <div class="logo-wrapper">
        <img src="assets/images/logo.png" alt="Logo Prambanan Beton" class="logo-img" style="height:100px;width:auto;object-fit:contain;filter:drop-shadow(0 2px 8px rgba(249,168,37,0.3));">
      </div>
    </a>

    <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
      <span></span><span></span><span></span>
    </button>

<ul class="nav-links" id="navLinks">
  <li><a href="index.php" class="nav-link <?= $current=='index.php'?'active':'' ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    Beranda
  </a></li>
  <li><a href="tentang.php" class="nav-link <?= $current=='tentang.php'?'active':'' ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
    Tentang Kami
  </a></li>
  <li><a href="produk.php" class="nav-link <?= $current=='produk.php'?'active':'' ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
    Produk
  </a></li>
  <li><a href="portofolio.php" class="nav-link <?= $current=='portofolio.php'?'active':'' ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
    Portofolio
  </a></li>
  <li><a href="kontak.php" class="nav-link <?= $current=='kontak.php'?'active':'' ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.07 1.18 2 2 0 012 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.09a16 16 0 006 6l.36-.36a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
    Kontak
  </a></li>
</ul>  </div>
</nav>