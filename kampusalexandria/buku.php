<?php
require __DIR__ . '/config.php';
$st = $pdo->prepare('SELECT b.*, k.nama AS kategori FROM buku b LEFT JOIN kategori k ON k.id = b.kategori_id WHERE b.id = ?');
$st->execute([(int)($_GET['id'] ?? 0)]);
$b = $st->fetch();
if (!$b) go('index.php');
$title = $b['judul'];
include __DIR__ . '/includes/header.php';
?>
<p><a href="index.php">&larr; Kembali ke katalog</a></p>
<div class="row">
  <div class="col-md-3"><img src="<?= thumb($b['thumbnail']) ?>" class="img-fluid"></div>
  <div class="col-md-9">
    <h1><?= e($b['judul']) ?></h1>
    <p>oleh <?= e($b['penulis']) ?></p>
    <h3><?= rupiah($b['harga']) ?></h3>
    <p>Kategori: <?= e($b['kategori'] ?? '-') ?><br>
       Penerbit: <?= e($b['penerbit'] ?? '-') ?><br>
       Tahun: <?= e($b['tahun'] ?? '-') ?><br>
       Stok: <?= (int)$b['stok'] ?></p>
    <p><?= nl2br(e($b['deskripsi'])) ?></p>
    <?php if ($b['stok'] > 0): ?>
      <form method="post" action="keranjang.php"><?= csrf() ?><input type="hidden" name="id" value="<?= $b['id'] ?>"><button class="btn btn-success">+ Tambah ke keranjang</button></form>
    <?php else: ?><p><b>Stok habis</b></p><?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
