<?php require_once __DIR__ . '/../config.php'; $n = array_sum($_SESSION['cart'] ?? []); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(($title ?? '') . ' - ' . NAMA_TOKO) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container py-3">
<nav class="d-flex flex-wrap gap-3 align-items-center border-bottom pb-2 mb-3">
  <b class="me-auto"><?= e(NAMA_TOKO) ?></b>
  <a href="<?= BASE_URL ?>/index.php">Katalog</a>
  <a href="<?= BASE_URL ?>/keranjang.php">Keranjang (<?= $n ?>)</a>
  <?php if (is_admin()): ?>
    <a href="<?= BASE_URL ?>/admin/index.php">Dashboard</a>
    <a href="<?= BASE_URL ?>/admin/buku.php">Buku</a>
    <a href="<?= BASE_URL ?>/admin/kategori.php">Kategori</a>
    <a href="<?= BASE_URL ?>/admin/pesanan.php">Pesanan</a>
    <a href="<?= BASE_URL ?>/profil.php">Ganti Password</a>
    <a href="<?= BASE_URL ?>/logout.php">Logout</a>
  <?php elseif (is_user()): ?>
    <a href="<?= BASE_URL ?>/pesanan_saya.php">Pesanan Saya</a>
    <a href="<?= BASE_URL ?>/profil.php">Ganti Password</a>
    <a href="<?= BASE_URL ?>/logout.php">Logout (<?= e($_SESSION['user_nama']) ?>)</a>
  <?php else: ?>
    <a href="<?= BASE_URL ?>/login.php">Login</a>
    <a href="<?= BASE_URL ?>/register.php">Daftar</a>
  <?php endif; ?>
</nav>
<?= flash() ?>
