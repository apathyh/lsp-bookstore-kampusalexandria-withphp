<?php
require __DIR__ . '/config.php';
if (!is_user()) go('login.php?next=pesanan');
$st = $pdo->prepare('SELECT * FROM pesanan WHERE pelanggan_id = ? ORDER BY id DESC');
$st->execute([$_SESSION['user_id']]);
$list = $st->fetchAll();
$it = $pdo->prepare('SELECT * FROM pesanan_item WHERE pesanan_id = ?');
$title = 'Pesanan Saya';
include __DIR__ . '/includes/header.php';
?>
<h1>Pesanan Saya</h1>
<?php if (!$list) echo '<p>Belum ada pesanan. <a href="index.php">Belanja dulu</a></p>'; ?>
<?php foreach ($list as $p): $it->execute([$p['id']]); ?>
  <h5><?= e($p['kode']) ?> <span class="badge bg-secondary"><?= e($p['status']) ?></span></h5>
  <p><?= e($p['created_at']) ?> | <?= e(METODE[$p['metode']]) ?>
    <?php if ($p['metode'] === 'transfer' && $p['status'] === 'baru'): ?>
      | <a href="<?= e(wa_link($p)) ?>" target="_blank">Konfirmasi lewat WhatsApp</a>
    <?php endif; ?></p>
  <table class="table table-sm">
    <?php foreach ($it as $i): ?>
      <tr><td><?= e($i['judul']) ?></td><td><?= (int)$i['qty'] ?> x <?= rupiah($i['harga']) ?></td></tr>
    <?php endforeach; ?>
    <tr><th>Total</th><th><?= rupiah($p['total']) ?></th></tr>
  </table>
<?php endforeach; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
