<?php
// Beranda: katalog HP dengan pencarian, filter merek, dan urutan
$pageTitle = 'Katalog handphone';
require __DIR__ . '/includes/header.php';

// Ambil input dari URL (GET) dan bersihkan spasi
$query = trim($_GET['q'] ?? '');
$brand = $_GET['brand'] ?? '';
$sort  = $_GET['sort'] ?? 'baru';

// Daftar merek untuk tombol filter
$brands = brand_list();
// Saring lalu urutkan HP sesuai input
$phones = sort_phones(filter_phones($query, $brand), $sort);
// Pilihan urutan: kode => label
$sortOptions = ['baru' => 'Terbaru', 'murah' => 'Harga terendah', 'mahal' => 'Harga tertinggi', 'rating' => 'Rating tertinggi'];
?>
<?php // Bagian hero berisi judul dan kotak pencarian ?>
<section class="hero"><div class="wrap">
  <h1>Temukan handphone yang cocok, pilih warnanya, langsung bayar.</h1>
  <form class="search" action="index.php#produk" method="get" role="search">
    <label class="sr" for="q">Cari handphone</label>
    <input id="q" name="q" type="search" placeholder="Cari nama atau merek, mis. Samsung" value="<?= e($query) ?>">
    <button type="submit">Cari</button>
  </form>
</div></section>

<?php // Bagian katalog: filter merek, urutan, dan grid kartu ?>
<section class="wrap" id="produk">
  <form class="filters" method="get" action="index.php#produk">
    <?php // Pertahankan kata kunci dan merek saat urutan diubah ?>
    <?php if ($query !== ''): ?><input type="hidden" name="q" value="<?= e($query) ?>"><?php endif; ?>
    <?php if ($brand !== ''): ?><input type="hidden" name="brand" value="<?= e($brand) ?>"><?php endif; ?>
    <div class="chips">
      <a class="chip <?= $brand === '' ? 'on' : '' ?>" href="index.php#produk">Semua merek</a>
      <?php foreach ($brands as $b): ?>
        <a class="chip <?= $brand === $b ? 'on' : '' ?>" href="index.php?brand=<?= urlencode($b) ?>#produk"><?= e($b) ?></a>
      <?php endforeach; ?>
    </div>
    <label class="sort">Urutkan
      <select name="sort" onchange="this.form.submit()">
        <?php foreach ($sortOptions as $code => $label): ?>
          <option value="<?= $code ?>" <?= $sort === $code ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
      </select>
    </label>
  </form>

  <?php // Tampilkan pesan kosong bila tidak ada hasil ?>
  <?php if (!$phones): ?>
    <p class="empty">Tidak ada HP yang cocok dengan pencarianmu. <a href="index.php#produk">Lihat semua HP</a>.</p>
  <?php else: ?>
  <div class="grid">
    <?php foreach ($phones as $p): ?>
      <?php // Satu kartu HP: foto/ilustrasi warna pertama, nama, titik warna, harga ?>
      <a class="card" href="product.php?id=<?= $p['id'] ?>">
        <div class="art" style="--tint:<?= e($p['colors'][0][1]) ?>"><?= phone_visual($p, 0) ?></div>
        <div class="info">
          <span class="meta"><?= e($p['brand']) ?> &middot; <?= e($p['series']) ?></span>
          <h3><?= e($p['name']) ?></h3>
          <div class="dots" aria-label="<?= count($p['colors']) ?> pilihan warna">
            <?php foreach ($p['colors'] as $color): ?><i style="background:<?= e($color[1]) ?>"></i><?php endforeach; ?>
          </div>
          <div class="row"><strong><?= rupiah($p['price']) ?></strong><span class="rate">&#9733; <?= number_format($p['rating'], 1) ?></span></div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
