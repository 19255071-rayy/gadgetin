<?php
// Header bersama: dipanggil di awal setiap halaman
require_once __DIR__ . '/bootstrap.php';
// Judul halaman default bila halaman tidak menentukan sendiri
$pageTitle = $pageTitle ?? SITE_NAME;
// Jumlah item keranjang untuk lencana di menu
$cartCount = cart_count();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> | <?= e(SITE_NAME) ?></title>
<?php // Font Google: judul Bricolage Grotesque, isi Public Sans ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;800&family=Public+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php // Bilah navigasi atas ?>
<header class="site-header">
  <div class="wrap bar">
    <a class="logo" href="index.php">gadget<span>.in</span></a>
    <nav class="nav">
      <a href="index.php">Semua HP</a>
      <a href="cart.php">Keranjang<?= $cartCount ? ' <b class="badge">' . $cartCount . '</b>' : '' ?></a>
    </nav>
  </div>
</header>
<main>
