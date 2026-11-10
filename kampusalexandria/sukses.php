<?php
require __DIR__ . '/config.php';
$st = $pdo->prepare('SELECT * FROM pesanan WHERE kode = ? AND pelanggan_id = ?');
$st->execute([$_GET['kode'] ?? '', $_SESSION['user_id'] ?? 0]);
$p = $st->fetch();
if (!$p) go('index.php');
$title = 'Pesanan berhasil';
include __DIR__ . '/includes/header.php';
?>
<h1>Pesanan berhasil dibuat</h1>
<p>Kode pesanan: <b><?= e($p['kode']) ?></b> (simpan ya)<br>
   Total: <b><?= rupiah($p['total']) ?></b><br>
   Metode: <?= e(METODE[$p['metode']]) ?></p>
<?php if ($p['metode'] === 'cod'): ?>
  <p>Siapkan uang pas dan bayar ke kurir saat barang tiba.</p>
<?php else: ?>
  <p>Transfer ke <b><?= e(REKENING) ?></b>, lalu konfirmasi ke admin:</p>
  <a class="btn btn-success" href="<?= e(wa_link($p)) ?>" target="_blank">Konfirmasi lewat WhatsApp</a>
<?php endif; ?>
<p class="mt-3"><a href="pesanan_saya.php">Lihat pesanan saya</a> | <a href="index.php">Belanja lagi</a></p>
<?php include __DIR__ . '/includes/footer.php'; ?>
