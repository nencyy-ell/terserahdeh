<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Clean up URI (e.g., /kontak instead of /kontak.php)
$uri = rtrim($uri, '/');
if (empty($uri)) {
    $uri = '/index.php';
}

$file = realpath(__DIR__ . '/../' . ltrim($uri, '/'));

// Check if requested route matches a PHP file in the root
if ($file && file_exists($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
    require $file;
} 
// Support for clean URLs (e.g., /kontak -> kontak.php)
elseif (file_exists(__DIR__ . '/../' . ltrim($uri, '/') . '.php')) {
    require __DIR__ . '/../' . ltrim($uri, '/') . '.php';
}
else {
    // If not found, fallback to root index.php
    require __DIR__ . '/../index.php';
}
