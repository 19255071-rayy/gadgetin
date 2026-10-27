<?php
// Halaman pesanan: instruksi bayar, lalu halaman sukses beranimasi
require_once __DIR__ . '/includes/bootstrap.php';

// Ambil pesanan berdasarkan nomor di URL (?ref=...)
$ref   = $_GET['ref'] ?? '';
$order = $_SESSION['orders'][$ref] ?? null;

// Pesanan tidak ada: tampilkan 404
if ($order === null) {
    http_response_code(404);
    $pageTitle = 'Pesanan tidak ditemukan';
    require __DIR__ . '/includes/header.php';
    echo '<div class="wrap page"><p class="empty">Pesanan tidak ditemukan. <a href="index.php">Ke katalog</a>.</p></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

// SIMULASI pembayaran. Untuk produksi, ganti dengan Midtrans Snap / Xendit:
// buat transaksi dari server, arahkan ke halaman bayar gateway, lalu ubah status lewat webhook.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok() && $order['status'] === 'pending') {
    // Tandai lunas dan catat waktunya
    $_SESSION['orders'][$ref]['status']  = 'paid';
    $_SESSION['orders'][$ref]['paid_at'] = time();
    // Muat ulang halaman agar menampilkan layar sukses
    header('Location: order.php?ref=' . urlencode($ref));
    exit;
}

$pageTitle = 'Pesanan ' . $ref;
require __DIR__ . '/includes/header.php';

// Pesanan selesai bila sudah dibayar atau memakai COD
$done     = in_array($order['status'], ['paid', 'cod'], true);
// Batas waktu bayar: 24 jam sejak pesanan dibuat
$deadline = date('j M Y, H:i', $order['time'] + 86400);
// Warna percikan animasi
$sparks   = ['#2F5BFF', '#12B886', '#F59E0B', '#E8365D'];
?>
<div class="wrap page narrow">
  <?php if ($done): ?>
    <?php // Layar sukses: centang beranimasi, percikan, dan ringkasan ?>
    <section class="success" aria-live="polite">
      <div class="burst" aria-hidden="true">
        <?php for ($i = 0; $i < 12; $i++): ?><span style="--a:<?= $i * 30 ?>deg;--c:<?= $sparks[$i % 4] ?>"></span><?php endfor; ?>
      </div>
      <svg class="check" viewBox="0 0 52 52" aria-hidden="true"><circle class="ring" cx="26" cy="26" r="24"/><path class="tick" d="M14 27l8 8 16-17"/></svg>
      <h1><?= $order['status'] === 'paid' ? 'Pembayaran berhasil' : 'Pesanan berhasil dibuat' ?></h1>
      <p class="lead">
        <?php if ($order['status'] === 'paid'): ?>
          Terima kasih, <?= e($order['cust']['name']) ?>. Kami sudah menerima <b><?= rupiah($order['total']) ?></b> dan pesananmu segera dikemas.
        <?php else: ?>
          Siapkan <b><?= rupiah($order['total']) ?></b> saat kurir tiba, <?= e($order['cust']['name']) ?>.
        <?php endif; ?>
      </p>
      <dl class="meta-list">
        <div><dt>Nomor pesanan</dt><dd><?= e($ref) ?></dd></div>
        <div><dt>Metode</dt><dd><?= e(PAYMENT_METHODS[$order['pay']]) ?></dd></div>
        <div><dt>Waktu</dt><dd><?= date('j M Y, H:i', $order['paid_at'] ?? $order['time']) ?></dd></div>
      </dl>
      <div class="btns c"><a class="btn" href="index.php">Belanja lagi</a><button class="btn ghost" type="button" onclick="window.print()">Cetak bukti</button></div>
    </section>
  <?php else: ?>
    <?php // Instruksi pembayaran untuk pesanan yang belum dibayar ?>
    <h1>Selesaikan pembayaran</h1>
    <p class="lead">Bayar sebelum <?= e($deadline) ?>. Nomor pesanan: <b><?= e($ref) ?></b></p>
    <div class="paybox">
      <p class="meta"><?= e(PAYMENT_METHODS[$order['pay']]) ?></p>
      <p class="price"><?= rupiah($order['total']) ?></p>
      <?php if (str_starts_with($order['pay'], 'va_')): ?>
        <?php // Virtual Account: tampilkan nomor per 4 digit ?>
        <p>Nomor Virtual Account</p>
        <p class="va"><?= e(chunk_split($order['va'], 4, ' ')) ?></p>
        <p class="meta">Buka m-banking, pilih Transfer, Virtual Account, lalu masukkan nomor di atas.</p>
      <?php else: ?>
        <?php // QRIS: kotak QR contoh ?>
        <div class="qr" role="img" aria-label="Contoh kode QRIS"></div>
        <p class="meta">Buka aplikasi e-wallet, pindai kode QRIS ini, lalu konfirmasi pembayaran.</p>
      <?php endif; ?>
      <form method="post" id="payform"><?= csrf_field() ?><button class="btn">Simulasikan: saya sudah bayar</button></form>
      <small>Tombol ini hanya untuk uji coba. Pada versi produksi, status berubah otomatis dari notifikasi payment gateway.</small>
    </div>
  <?php endif; ?>

  <?php // Rincian pesanan ditampilkan di semua status ?>
  <h2>Rincian pesanan</h2>
  <div class="sum flat">
    <?php foreach ($order['items'] as $item): ?>
      <p><span><?= e($item['name']) ?> (<?= e($item['color']) ?>) &times;<?= $item['q'] ?></span><b><?= rupiah($item['sub']) ?></b></p>
    <?php endforeach; ?>
    <p><span>Ongkir</span><b><?= $order['ship'] ? rupiah($order['ship']) : 'Gratis' ?></b></p>
    <p class="tot"><span>Total</span><b><?= rupiah($order['total']) ?></b></p>
    <p class="meta">Dikirim ke: <?= e($order['cust']['address']) ?> &middot; <?= e($order['cust']['phone']) ?></p>
  </div>
</div>
<?php // Layar loading yang dimunculkan JavaScript saat "membayar" ?>
<div id="proc" class="proc" hidden role="status"><div class="spin"></div><p>Memproses pembayaran...</p></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
