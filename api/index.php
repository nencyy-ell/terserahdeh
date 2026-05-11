<?php
/**
 * Vercel Serverless PHP Router
 * 
 * Security: Uses a strict whitelist approach instead of dynamic file resolution
 * to prevent path traversal and arbitrary file inclusion attacks.
 */

// ======================== SECURITY HEADERS ========================
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; frame-src https://maps.google.com https://www.google.com;");

// ======================== WHITELIST OF ALLOWED PAGES ========================
// Only these pages can be accessed. Any other request falls back to index.
$allowedPages = [
    'index',
    'kontak',
    'produk',
    'portofolio',
    'tentang',
];

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

// Only allow whitelisted pages — prevents path traversal attacks
if (in_array($page, $allowedPages, true)) {
    $filePath = __DIR__ . '/../' . $page . '.php';
    if (file_exists($filePath)) {
        require $filePath;
        exit;
    }
}

// Fallback: serve the homepage
require __DIR__ . '/../index.php';
