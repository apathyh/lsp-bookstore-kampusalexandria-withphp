<?php
require __DIR__ . '/../config.php';
wajib_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $aksi = $_POST['aksi'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    try {
        if ($aksi === 'hapus') {
            $pdo->prepare('DELETE FROM kategori WHERE id = ?')->execute([$id]);
            flash('Kategori dihapus (buku di dalamnya jadi tanpa kategori).');
        } elseif ($nama === '') {
            flash('Nama kategori wajib diisi.');
        } elseif ($aksi === 'tambah') {
            $pdo->prepare('INSERT INTO kategori (nama) VALUES (?)')->execute([$nama]);
            flash('Kategori ditambahkan.');
        } elseif ($aksi === 'ubah') {
            $pdo->prepare('UPDATE kategori SET nama = ? WHERE id = ?')->execute([$nama, $id]);
            flash('Kategori diubah.');
        }
    } catch (PDOException $ex) {
        flash('Nama kategori sudah ada.');
    }
    go('admin/kategori.php');
}

$list = $pdo->query('SELECT k.*, (SELECT COUNT(*) FROM buku b WHERE b.kategori_id = k.id) AS jml FROM kategori k ORDER BY k.nama')->fetchAll();
$title = 'Kategori';
include __DIR__ . '/../includes/header.php';
?>
<h1>Kategori</h1>
<form method="post" class="d-flex gap-2 mb-3" style="max-width:500px">
  <?= csrf() ?><input type="hidden" name="aksi" value="tambah">
  <input name="nama" placeholder="Nama kategori baru" class="form-control" required>
  <button class="btn btn-primary text-nowrap">+ Tambah</button>
</form>
<table class="table align-middle">
  <tr><th>Nama</th><th>Jumlah buku</th><th></th></tr>
  <?php foreach ($list as $k): ?>
  <tr>
    <td>
      <form method="post" class="d-flex gap-2">
        <?= csrf() ?><input type="hidden" name="aksi" value="ubah"><input type="hidden" name="id" value="<?= $k['id'] ?>">
        <input name="nama" value="<?= e($k['nama']) ?>" class="form-control" required>
        <button class="btn btn-sm btn-secondary">Simpan</button>
      </form>
    </td>
    <td><?= (int)$k['jml'] ?></td>
    <td>
      <form method="post" onsubmit="return confirm('Hapus kategori ini?')">
        <?= csrf() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= $k['id'] ?>">
        <button class="btn btn-sm btn-danger">Hapus</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php include __DIR__ . '/../includes/footer.php'; ?>
