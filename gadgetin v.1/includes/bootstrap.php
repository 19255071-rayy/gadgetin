<?php
/**
 * bootstrap.php: dipanggil paling awal oleh setiap halaman.
 * Menyiapkan session, konfigurasi, dan fungsi bantu.
 */

// Lokasi folder root situs (satu tingkat di atas folder includes)
define('ROOT_PATH', dirname(__DIR__));

// Mulai session bila belum aktif (dipakai untuk keranjang dan pesanan)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Buat token CSRF sekali per sesi agar form tidak bisa dipalsukan dari situs lain
if (empty($_SESSION['t'])) {
    $_SESSION['t'] = bin2hex(random_bytes(16));
}

// Muat konfigurasi
require_once __DIR__ . '/config.php';

// Muat fungsi bantu
require_once __DIR__ . '/functions.php';
