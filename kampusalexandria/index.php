<?php
require __DIR__ . '/config.php';
$q = trim($_GET['q'] ?? '');
$kat = (int)($_GET['kategori'] ?? 0);
$st = $pdo->prepare("SELECT * FROM buku WHERE (judul LIKE ? OR penulis LIKE ?) AND (? = '0' OR kategori_id = ?) ORDER BY id DESC");
$st->execute(["%$q%", "%$q%", $kat, $kat]);
$list = $st->fetchAll();
$kategori = $pdo->query('SELECT * FROM kategori ORDER BY nama')->fetchAll();
$title = 'Katalog';
include __DIR__ . '/includes/header.php';
?>
<h1>Katalog Buku</h1>
<form method="get" class="d-flex gap-2 mb-3">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Cari judul / penulis" class="form-control">
  <select name="kategori" class="form-select">
    <option value="0">Semua kategori</option>
    <?php foreach ($kategori as $k): ?>
      <option value="<?= $k['id'] ?>" <?= $kat == $k['id'] ? 'selected' : '' ?>><?= e($k['nama']) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-primary">Cari</button>
</form>
<?php if (!$list) echo '<p>Buku tidak ditemukan.</p>'; ?>
<table class="table align-middle">
  <?php foreach ($list as $b): ?>
  <tr>
    <td width="80"><img src="<?= thumb($b['thumbnail']) ?>" width="70"></td>
    <td><a href="buku.php?id=<?= $b['id'] ?>"><?= e($b['judul']) ?></a><br><small><?= e($b['penulis']) ?></small></td>
    <td><?= rupiah($b['harga']) ?></td>
    <td>
      <?php if ($b['stok'] > 0): ?>
        <form method="post" action="keranjang.php"><?= csrf() ?><input type="hidden" name="id" value="<?= $b['id'] ?>"><button class="btn btn-sm btn-success">+ Keranjang</button></form>
      <?php else: ?>Stok habis<?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php include __DIR__ . '/includes/footer.php'; ?>
