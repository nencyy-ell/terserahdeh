<?php
// ======================== SECURITY HEADERS ========================
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; frame-src https://maps.google.com https://www.google.com;");

// ======================== SLUG-TO-FILE MAPPING ========================
$slugMap = [
    'beranda'  => 'index',
    'profil'   => 'tentang',
    'layanan'  => 'produk',
    'proyek'   => 'portofolio',
    'hubungi'  => 'kontak',
];

// Direct page names for backward compatibility (old bookmarks still work)
$allowedPages = ['index', 'kontak', 'produk', 'portofolio', 'tentang'];

// ======================== ROUTE RESOLUTION ========================
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');

// Extract the page name from the URI
$page = trim($uri, '/');
$page = str_replace('.php', '', $page); // Remove .php extension if present

// Default to index for empty/root requests
if (empty($page) || $page === 'api') {
    $page = 'index';
}

// Resolve: check slug map first, then fallback to direct names
$actualPage = null;

if (isset($slugMap[$page])) {
    // Matched a clean slug (e.g., /profil -> tentang)
    $actualPage = $slugMap[$page];
} elseif (in_array($page, $allowedPages, true)) {
    // Matched a direct filename for backward compatibility
    $actualPage = $page;
}

if ($actualPage) {
    // Set global variable so navbar can detect the active page
    $GLOBALS['_current_page'] = $actualPage;
    $filePath = __DIR__ . '/../' . $actualPage . '.php';
    if (file_exists($filePath)) {
        require $filePath;
        exit;
    }
}

// Fallback: serve the homepage
$GLOBALS['_current_page'] = 'index';
require __DIR__ . '/../index.php';
