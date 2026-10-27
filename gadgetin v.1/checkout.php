<?php
// Checkout: data penerima dan pilihan metode pembayaran
require_once __DIR__ . '/includes/bootstrap.php';

// Keranjang kosong tidak boleh checkout, kembalikan ke keranjang
$items = cart_items();
if (!$items) {
    header('Location: cart.php');
    exit;
}

// Hitung biaya
$subtotal = array_sum(array_column($items, 'sub'));
$shipping = shipping_fee($subtotal);
$total    = $subtotal + $shipping;

// Nilai awal form dan daftar error
$form   = ['name' => '', 'phone' => '', 'address' => '', 'pay' => ''];
$errors = [];

// Saat form dikirim: ambil input, validasi, lalu buat pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil dan rapikan setiap field
    foreach ($form as $field => $_) {
        $form[$field] = trim($_POST[$field] ?? '');
    }
    // Validasi token CSRF dan isi form
    $errors = validate_checkout($form);
    if (!csrf_ok()) {
        $errors[] = 'Sesi kedaluwarsa, muat ulang halaman.';
    }
    // Jika semua valid, simpan pesanan dan pindah ke halaman pembayaran
    if (!$errors) {
        header('Location: order.php?ref=' . create_order($form, $items));
        exit;
    }
}

$pageTitle = 'Checkout';
require __DIR__ . '/includes/header.php';
?>
<div class="wrap page">
  <h1>Checkout</h1>
  <?php // Kotak pesan error ?>
  <?php if ($errors): ?>
    <div class="alert" role="alert"><?php foreach ($errors as $msg) { echo '<div>' . e($msg) . '</div>'; } ?></div>
  <?php endif; ?>

  <form method="post" class="two" novalidate>
    <?= csrf_field() ?>
    <?php // Kolom kiri: data penerima dan metode bayar ?>
    <div class="form">
      <h2>Data penerima</h2>
      <label>Nama lengkap<input name="name" value="<?= e($form['name']) ?>" autocomplete="name" required></label>
      <label>Nomor HP<input name="phone" value="<?= e($form['phone']) ?>" inputmode="tel" autocomplete="tel" required></label>
      <label>Alamat pengiriman<textarea name="address" rows="3" autocomplete="street-address" required><?= e($form['address']) ?></textarea></label>
      <h2>Metode pembayaran</h2>
      <?php foreach (PAYMENT_METHODS as $code => $label): ?>
        <label class="opt"><input type="radio" name="pay" value="<?= $code ?>" <?= $form['pay'] === $code ? 'checked' : '' ?>> <?= e($label) ?></label>
      <?php endforeach; ?>
    </div>

    <?php // Kolom kanan: ringkasan pesanan ?>
    <aside class="sum">
      <h2>Pesananmu</h2>
      <?php foreach ($items as $item): ?>
        <p><span><?= e($item['phone']['name']) ?> (<?= e($item['color'][0]) ?>) &times;<?= $item['qty'] ?></span><b><?= rupiah($item['sub']) ?></b></p>
      <?php endforeach; ?>
      <p><span>Ongkir</span><b><?= $shipping ? rupiah($shipping) : 'Gratis' ?></b></p>
      <p class="tot"><span>Total bayar</span><b><?= rupiah($total) ?></b></p>
      <button class="btn">Buat pesanan</button>
    </aside>
  </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
