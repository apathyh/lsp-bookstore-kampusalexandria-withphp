<?php
require __DIR__ . '/../config.php';
wajib_admin();
$hitung = fn($sql) => $pdo->query($sql)->fetchColumn();
$tipis = $pdo->query('SELECT id, judul, stok FROM buku WHERE stok <= 3 ORDER BY stok LIMIT 10')->fetchAll();
$title = 'Dashboard';
include __DIR__ . '/../includes/header.php';
?>
<h1>Dashboard</h1>
<ul>
  <li>Total buku: <b><?= $hitung('SELECT COUNT(*) FROM buku') ?></b></li>
  <li>Pesanan baru: <b><?= $hitung("SELECT COUNT(*) FROM pesanan WHERE status = 'baru'") ?></b></li>
  <li>Omzet: <b><?= rupiah($hitung("SELECT COALESCE(SUM(total), 0) FROM pesanan WHERE status IN ('diproses','dikirim','selesai')")) ?></b></li>
</ul>
<h2>Stok menipis (3 atau kurang)</h2>
<?php if (!$tipis) echo '<p>Tidak ada.</p>'; ?>
<table class="table">
  <?php foreach ($tipis as $b): ?>
  <tr><td><?= e($b['judul']) ?></td><td><?= (int)$b['stok'] ?></td><td><a href="buku_form.php?id=<?= $b['id'] ?>">Edit</a></td></tr>
  <?php endforeach; ?>
</table>
<?php include __DIR__ . '/../includes/footer.php'; ?>s