<?php
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    if (isset($_POST['qty'])) {                       // tombol Update (qty 0 = hapus)
        foreach ($_POST['qty'] as $id => $qty) {
            if ((int)$qty > 0) $_SESSION['cart'][(int)$id] = (int)$qty; else unset($_SESSION['cart'][(int)$id]);
        }
        flash('Keranjang diperbarui.');
        go('keranjang.php');
    }
    $id = (int)($_POST['id'] ?? 0);                   // tombol + Keranjang
    $st = $pdo->prepare('SELECT stok FROM buku WHERE id = ?');
    $st->execute([$id]);
    $now = $_SESSION['cart'][$id] ?? 0;
    if ($st->fetchColumn() > $now) { $_SESSION['cart'][$id] = $now + 1; flash('Ditambahkan ke keranjang.'); }
    else flash('Stok tidak cukup.');
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/keranjang.php'));
    exit;
}

[$items, $total] = keranjang($pdo);
$title = 'Keranjang';
include __DIR__ . '/includes/header.php';
?>
<h1>Keranjang</h1>
<?php if (!$items): ?>
  <p>Keranjang masih kosong. <a href="index.php">Cari buku dulu</a></p>
<?php else: ?>
<form method="post">
  <?= csrf() ?>
  <table class="table">
    <tr><th>Buku</th><th>Harga</th><th>Qty (0 = hapus)</th><th>Subtotal</th></tr>
    <?php foreach ($items as $b): ?>
    <tr>
      <td><?= e($b['judul']) ?></td>
      <td><?= rupiah($b['harga']) ?></td>
      <td><input type="number" name="qty[<?= $b['id'] ?>]" value="<?= $b['qty'] ?>" min="0" max="<?= $b['stok'] ?>" class="form-control" style="width:90px"></td>
      <td><?= rupiah($b['sub']) ?></td>
    </tr>
    <?php endforeach; ?>
    <tr><th colspan="3">Total</th><th><?= rupiah($total) ?></th></tr>
  </table>
  <button class="btn btn-secondary">Update</button>
  <a class="btn btn-primary" href="checkout.php">Checkout</a>
</form>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
