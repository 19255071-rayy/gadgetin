<?php
// Alat bantu: cek foto HP mana yang sudah terbaca dan mana yang belum
$pageTitle = 'Cek foto HP';
require __DIR__ . '/includes/header.php';
?>
<div class="wrap page">
  <h1>Cek foto HP</h1>
  <p class="lead">Letakkan foto di <code><?= e(IMG_DIR) ?>/&lt;folder-hp&gt;/&lt;nama-warna&gt;.webp</code>. Tanda centang berarti foto terbaca.</p>
  <?php foreach (load_phones() as $p): $folder = slug($p['name']); ?>
    <?php // Satu blok per HP: nama folder dan status tiap warna ?>
    <h2><?= e($p['name']) ?> <small class="meta">folder: <?= e($folder) ?>/</small></h2>
    <table class="specs">
      <?php foreach ($p['colors'] as [$name, $hex]): $file = find_image_file($folder, slug($name)); ?>
        <tr>
          <th scope="row"><?= e($name) ?></th>
          <td><?= $file ? '&#10003; ' . e($file) : '&#10007; belum ada, buat file <code>' . e(slug($name)) . '.webp</code>' ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endforeach; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
