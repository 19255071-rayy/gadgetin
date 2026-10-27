<?php
// Keranjang belanja: tampil isi keranjang dan proses tambah/ubah/hapus
require_once __DIR__ . '/includes/bootstrap.php';

// Bila ada kiriman form yang token CSRF-nya valid, proses aksinya lalu arahkan ulang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
    header('Location: ' . cart_apply_action($_POST));
    exit;
}

$pageTitle = 'Keranjang';
require __DIR__ . '/includes/header.php';

// Ambil isi keranjang dan hitung ringkasan biaya
$items    = cart_items();
$subtotal = array_sum(array_column($items, 'sub'));
$shipping = shipping_fee($subtotal);
?>
<div class="wrap page">
  <h1>Keranjang</h1>
  <?php if (!$items): ?>
    <?php // Keranjang kosong ?>
    <p class="empty">Keranjangmu kosong. <a href="index.php">Pilih handphone</a> dulu.</p>
  <?php else: ?>
  <div class="two">
    <div>
      <?php foreach ($items as $item): $p = $item['phone']; ?>
        <?php // Satu baris keranjang: gambar, nama, warna, jumlah, hapus ?>
        <div class="line">
          <div class="thumb" style="--tint:<?= e($item['color'][1]) ?>">
            <?php // Cari indeks warna agar foto yang tampil sesuai warna pilihan ?>
            <?= phone_visual($p, (int) array_search($item['color'], $p['colors'], true)) ?>
          </div>
          <div class="grow">
            <a href="product.php?id=<?= $p['id'] ?>"><b><?= e($p['name']) ?></b></a><br>
            <span class="meta">Warna <?= e($item['color'][0]) ?></span><br>
            <?= rupiah($p['price']) ?>
          </div>
          <form method="post" class="qty">
            <?= csrf_field() ?>
            <input type="hidden" name="key" value="<?= e($item['key']) ?>">
            <input type="hidden" name="action" value="qty">
            <label class="sr" for="q<?= e($item['key']) ?>">Jumlah</label>
            <select id="q<?= e($item['key']) ?>" name="q" onchange="this.form.submit()">
              <?php for ($n = 1; $n <= MAX_QTY; $n++): ?><option <?= $n === $item['qty'] ? 'selected' : '' ?>><?= $n ?></option><?php endfor; ?>
            </select>
          </form>
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="key" value="<?= e($item['key']) ?>">
            <button class="link" name="action" value="remove">Hapus</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>

    <?php // Ringkasan biaya ?>
    <aside class="sum">
      <h2>Ringkasan</h2>
      <p><span>Subtotal</span><b><?= rupiah($subtotal) ?></b></p>
      <p><span>Ongkir</span><b><?= $shipping ? rupiah($shipping) : 'Gratis' ?></b></p>
      <p class="tot"><span>Total</span><b><?= rupiah($subtotal + $shipping) ?></b></p>
      <a class="btn" href="checkout.php">Lanjut ke pembayaran</a>
      <small>Gratis ongkir untuk belanja mulai <?= rupiah(FREE_SHIPPING_MIN) ?>.</small>
    </aside>
  </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
