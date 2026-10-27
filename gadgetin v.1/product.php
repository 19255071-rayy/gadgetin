<?php
// Detail HP: foto, pilihan warna, spesifikasi, tombol beli
require_once __DIR__ . '/includes/bootstrap.php';

// Cari HP dari parameter ?id=
$phone = find_phone((int) ($_GET['id'] ?? 0));

// Bila tidak ditemukan, tampilkan halaman 404
if ($phone === null) {
    http_response_code(404);
    $pageTitle = 'HP tidak ditemukan';
    require __DIR__ . '/includes/header.php';
    echo '<div class="wrap"><p class="empty">HP tidak ditemukan. <a href="index.php">Kembali ke katalog</a>.</p></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

// Judul tab sesuai nama HP
$pageTitle = $phone['name'];
require __DIR__ . '/includes/header.php';

// Warna pertama jadi pilihan awal
[$firstName, $firstHex] = $phone['colors'][0];
// Foto warna pertama (null bila belum ada)
$firstPhoto = phone_photo($phone, $firstName);
?>
<div class="wrap detail">
  <a class="back" href="index.php#produk">&larr; Kembali ke katalog</a>
  <div class="d-grid">
    <?php // Area gambar: foto asli bila ada, ilustrasi SVG sebagai cadangan; JavaScript menukarnya saat warna dipilih ?>
    <div class="d-art" id="art" style="--tint:<?= e($firstHex) ?>">
      <img id="photo" <?= $firstPhoto ? 'src="' . e($firstPhoto) . '"' : 'hidden' ?> alt="<?= e($phone['name']) ?>">
      <div id="drawing" <?= $firstPhoto ? 'hidden' : '' ?>><?= phone_svg($firstHex) ?></div>
    </div>

    <?php // Form beli: dikirim ke cart.php ?>
    <form method="post" action="cart.php">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= $phone['id'] ?>">
      <span class="meta"><?= e($phone['brand']) ?> &middot; <?= e($phone['series']) ?> &middot; &#9733; <?= number_format($phone['rating'], 1) ?></span>
      <h1><?= e($phone['name']) ?></h1>
      <p class="lead"><?= e($phone['summary']) ?></p>
      <p class="price"><?= rupiah($phone['price']) ?></p>

      <?php // Pilihan warna: tiap radio menyimpan nama, hex, dan alamat foto untuk JavaScript ?>
      <fieldset class="colors">
        <legend>Warna: <b id="cname"><?= e($firstName) ?></b></legend>
        <?php foreach ($phone['colors'] as $i => [$name, $hex]): ?>
          <label class="sw" title="<?= e($name) ?>">
            <input type="radio" name="c" value="<?= $i ?>"
                   data-hex="<?= e($hex) ?>" data-name="<?= e($name) ?>"
                   data-img="<?= e(phone_photo($phone, $name) ?? '') ?>" <?= $i === 0 ? 'checked' : '' ?>>
            <span style="background:<?= e($hex) ?>"></span><em class="sr"><?= e($name) ?></em>
          </label>
        <?php endforeach; ?>
      </fieldset>

      <?php // Tombol aksi ?>
      <div class="btns">
        <button class="btn" name="action" value="buy">Beli sekarang</button>
        <button class="btn ghost" name="action" value="add">Tambah ke keranjang</button>
      </div>

      <?php // Tabel spesifikasi ?>
      <h2>Spesifikasi</h2>
      <table class="specs">
        <?php foreach (phone_specs($phone) as $label => $value): ?>
          <tr><th scope="row"><?= e($label) ?></th><td><?= e($value) ?></td></tr>
        <?php endforeach; ?>
      </table>
    </form>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
