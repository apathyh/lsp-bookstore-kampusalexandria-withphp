<?php
require __DIR__ . '/../config.php';
wajib_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {          // hapus buku
    cek_csrf();
    $id = (int)$_POST['id'];
    $st = $pdo->prepare('SELECT thumbnail FROM buku WHERE id = ?');
    $st->execute([$id]);
    if ($b = $st->fetch()) {
        $pdo->prepare('DELETE FROM buku WHERE id = ?')->execute([$id]);
        hapus_cover($b['thumbnail']);
        flash('Buku dihapus.');
    }
    go('admin/buku.php');
}

$q = trim($_GET['q'] ?? '');
$st = $pdo->prepare('SELECT b.*, k.nama AS kategori FROM buku b LEFT JOIN kategori k ON k.id = b.kategori_id WHERE b.judul LIKE ? OR b.penulis LIKE ? ORDER BY b.id DESC');
$st->execute(["%$q%", "%$q%"]);
$title = 'Data Buku';
include __DIR__ . '/../includes/header.php';
?>
<h1>Data Buku</h1>
<form method="get" class="d-flex gap-2 mb-3">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Cari judul / penulis" class="form-control">
  <button class="btn btn-secondary">Cari</button>
  <a class="btn btn-primary text-nowrap" href="buku_form.php">+ Tambah</a>
</form>
<table class="table align-middle">
  <tr><th>Cover</th><th>Judul</th><th>Kategori</th><th>Harga</th><th>Stok</th><th></th></tr>
  <?php foreach ($st as $b): ?>
  <tr>
    <td><img src="<?= thumb($b['thumbnail']) ?>" width="45"></td>
    <td><?= e($b['judul']) ?><br><small><?= e($b['penulis']) ?></small></td>
    <td><?= e($b['kategori'] ?? '-') ?></td>
    <td><?= rupiah($b['harga']) ?></td>
    <td><?= (int)$b['stok'] ?></td>
    <td class="d-flex gap-1">
      <a class="btn btn-sm btn-primary" href="buku_form.php?id=<?= $b['id'] ?>">Edit</a>
      <form method="post" onsubmit="return confirm('Hapus buku ini?')"><?= csrf() ?><input type="hidden" name="id" value="<?= $b['id'] ?>"><button class="btn btn-sm btn-danger">Hapus</button></form>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php include __DIR__ . '/../includes/footer.php'; ?>
