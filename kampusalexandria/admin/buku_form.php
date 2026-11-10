<?php
require __DIR__ . '/../config.php';
wajib_admin();

$id = (int)($_GET['id'] ?? 0);
$b = ['judul' => '', 'penulis' => '', 'penerbit' => '', 'tahun' => '', 'harga' => 0, 'stok' => 0, 'deskripsi' => '', 'kategori_id' => 0, 'thumbnail' => null];
if ($id) {
    $st = $pdo->prepare('SELECT * FROM buku WHERE id = ?');
    $st->execute([$id]);
    $b = $st->fetch();
    if (!$b) { flash('Buku tidak ditemukan.'); go('admin/buku.php'); }
}
$kategori = $pdo->query('SELECT * FROM kategori ORDER BY nama')->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    foreach (['judul', 'penulis', 'penerbit', 'tahun', 'harga', 'stok', 'deskripsi', 'kategori_id'] as $k) $b[$k] = trim($_POST[$k] ?? '');
    if (!$b['judul'] || !$b['penulis']) $error = 'Judul dan penulis wajib diisi.';
    elseif ((int)$b['harga'] < 0 || (int)$b['stok'] < 0) $error = 'Harga dan stok tidak boleh negatif.';
    else try {
        $cover = upload($_FILES['thumbnail'] ?? null);
        $v = [$b['judul'], $b['penulis'], $b['penerbit'] ?: null, $b['tahun'] ?: null, (int)$b['harga'], (int)$b['stok'], $b['deskripsi'], $b['kategori_id'] ?: null, $cover ?? $b['thumbnail']];
        if ($id) {
            $pdo->prepare('UPDATE buku SET judul=?, penulis=?, penerbit=?, tahun=?, harga=?, stok=?, deskripsi=?, kategori_id=?, thumbnail=? WHERE id=?')->execute(array_merge($v, [$id]));
            if ($cover) hapus_cover($b['thumbnail']);
        } else {
            $pdo->prepare('INSERT INTO buku (judul, penulis, penerbit, tahun, harga, stok, deskripsi, kategori_id, thumbnail) VALUES (?,?,?,?,?,?,?,?,?)')->execute($v);
        }
        flash('Buku disimpan.');
        go('admin/buku.php');
    } catch (Exception $ex) {
        $error = $ex->getMessage();
    }
}

$title = $id ? 'Edit Buku' : 'Tambah Buku';
include __DIR__ . '/../includes/header.php';
?>
<h1><?= $title ?></h1>
<?php if ($error) echo '<div class="alert alert-danger">' . e($error) . '</div>'; ?>
<form method="post" enctype="multipart/form-data" style="max-width:600px">
  <?= csrf() ?>
  <label>Judul *</label><input name="judul" value="<?= e($b['judul']) ?>" class="form-control mb-2" required>
  <label>Penulis *</label><input name="penulis" value="<?= e($b['penulis']) ?>" class="form-control mb-2" required>
  <label>Penerbit</label><input name="penerbit" value="<?= e($b['penerbit']) ?>" class="form-control mb-2">
  <label>Tahun terbit</label><input type="number" name="tahun" value="<?= e($b['tahun']) ?>" class="form-control mb-2">
  <label>Kategori</label>
  <select name="kategori_id" class="form-select mb-2">
    <option value="0">- tanpa kategori -</option>
    <?php foreach ($kategori as $k): ?>
      <option value="<?= $k['id'] ?>" <?= $b['kategori_id'] == $k['id'] ? 'selected' : '' ?>><?= e($k['nama']) ?></option>
    <?php endforeach; ?>
  </select>
  <label>Harga (Rp)</label><input type="number" name="harga" value="<?= e($b['harga']) ?>" min="0" class="form-control mb-2" required>
  <label>Stok</label><input type="number" name="stok" value="<?= e($b['stok']) ?>" min="0" class="form-control mb-2" required>
  <label>Deskripsi</label><textarea name="deskripsi" class="form-control mb-2"><?= e($b['deskripsi']) ?></textarea>
  <label>Cover (JPG, PNG, WEBP, maks 2 MB)</label>
  <?php if ($id) echo '<div><img src="' . thumb($b['thumbnail']) . '" width="90"></div>'; ?>
  <input type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp" class="form-control mb-3">
  <button class="btn btn-primary">Simpan</button>
  <a class="btn btn-secondary" href="buku.php">Batal</a>
</form>
<?php include __DIR__ . '/../includes/footer.php'; ?>
