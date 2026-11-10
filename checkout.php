<?php
require __DIR__ . '/config.php';
if (is_admin()) { flash('Akun admin tidak bisa belanja.'); go('keranjang.php'); }
if (!is_user()) { flash('Login dulu untuk checkout.'); go('login.php?next=checkout'); }
[$items, $total] = keranjang($pdo);
if (!$items) { flash('Keranjang kosong.'); go('keranjang.php'); }

$st = $pdo->prepare('SELECT nama, telepon, alamat FROM pelanggan WHERE id = ?');
$st->execute([$_SESSION['user_id']]);
$f = $st->fetch() + ['metode' => 'cod'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    foreach ($f as $k => $v) $f[$k] = trim($_POST[$k] ?? '');
    if (!$f['nama'] || !$f['telepon'] || !$f['alamat'] || !isset(METODE[$f['metode']])) {
        $error = 'Semua kolom wajib diisi dan metode pembayaran harus dipilih.';
    } else try {
        $pdo->beginTransaction();
        $cek = $pdo->prepare('SELECT stok FROM buku WHERE id = ? FOR UPDATE');   // kunci baris supaya stok tidak rebutan
        foreach ($items as $b) {
            $cek->execute([$b['id']]);
            if ($cek->fetchColumn() < $b['qty']) throw new Exception('Stok "' . $b['judul'] . '" tidak cukup.');
        }
        $kode = 'KA' . date('ymdHis') . random_int(10, 99);
        $pdo->prepare('INSERT INTO pesanan (pelanggan_id, kode, nama, telepon, alamat, metode, total) VALUES (?,?,?,?,?,?,?)')
            ->execute([$_SESSION['user_id'], $kode, $f['nama'], $f['telepon'], $f['alamat'], $f['metode'], $total]);
        $pid = $pdo->lastInsertId();
        $ins = $pdo->prepare('INSERT INTO pesanan_item (pesanan_id, buku_id, judul, harga, qty) VALUES (?,?,?,?,?)');
        $upd = $pdo->prepare('UPDATE buku SET stok = stok - ? WHERE id = ?');
        foreach ($items as $b) {
            $ins->execute([$pid, $b['id'], $b['judul'], $b['harga'], $b['qty']]);
            $upd->execute([$b['qty'], $b['id']]);
        }
        $pdo->commit();
        unset($_SESSION['cart']);
        go('sukses.php?kode=' . $kode);
    } catch (Exception $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = $ex->getMessage();
    }
}

$title = 'Checkout';
include __DIR__ . '/includes/header.php';
?>
<h1>Checkout</h1>
<?php if ($error) echo '<div class="alert alert-danger">' . e($error) . '</div>'; ?>
<p>Total belanja: <b><?= rupiah($total) ?></b> (<?= count($items) ?> judul)</p>
<form method="post" style="max-width:500px">
  <?= csrf() ?>
  <label>Nama penerima</label><input name="nama" value="<?= e($f['nama']) ?>" class="form-control mb-2" required>
  <label>No. telepon / WA</label><input name="telepon" value="<?= e($f['telepon']) ?>" class="form-control mb-2" required>
  <label>Alamat lengkap</label><textarea name="alamat" class="form-control mb-2" required><?= e($f['alamat']) ?></textarea>
  <p class="mb-1"><b>Metode pembayaran</b></p>
  <?php foreach (METODE as $k => $v): ?>
    <label class="d-block"><input type="radio" name="metode" value="<?= $k ?>" <?= $f['metode'] === $k ? 'checked' : '' ?>> <?= e($v) ?></label>
  <?php endforeach; ?>
  <button class="btn btn-primary mt-3">Buat pesanan</button>
  <a class="btn btn-secondary mt-3" href="keranjang.php">Kembali</a>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
