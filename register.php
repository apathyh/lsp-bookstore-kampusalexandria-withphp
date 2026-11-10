<?php
require __DIR__ . '/config.php';
$next = $_GET['next'] ?? $_POST['next'] ?? '';
$tujuan = ['checkout' => 'checkout.php', 'pesanan' => 'pesanan_saya.php'][$next] ?? 'index.php';
if (is_admin()) go('admin/index.php');
if (is_user()) go($tujuan);

$f = ['nama' => '', 'username' => '', 'telepon' => '', 'alamat' => ''];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    foreach ($f as $k => $v) $f[$k] = trim($_POST[$k] ?? '');
    $pass = $_POST['password'] ?? '';
    // username harus unik di tabel pelanggan DAN admin
    $cek = $pdo->prepare('SELECT (SELECT COUNT(*) FROM pelanggan WHERE username = ?) + (SELECT COUNT(*) FROM admin WHERE username = ?)');
    $cek->execute([$f['username'], $f['username']]);

    if (!$f['nama'] || !$f['username']) $error = 'Nama dan username wajib diisi.';
    elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $f['username'])) $error = 'Username 3-30 karakter: huruf, angka, atau underscore.';
    elseif (strlen($pass) < 6) $error = 'Password minimal 6 karakter.';
    elseif ($pass !== ($_POST['ulang'] ?? '')) $error = 'Konfirmasi password tidak sama.';
    elseif ($cek->fetchColumn() > 0) $error = 'Username sudah dipakai.';
    else {
        $pdo->prepare('INSERT INTO pelanggan (nama, username, password, telepon, alamat) VALUES (?,?,?,?,?)')
            ->execute([$f['nama'], $f['username'], password_hash($pass, PASSWORD_DEFAULT), $f['telepon'] ?: null, $f['alamat'] ?: null]);
        session_regenerate_id(true);
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['user_nama'] = $f['nama'];
        flash('Akun berhasil dibuat. Selamat datang!');
        go($tujuan);
    }
}

$title = 'Daftar';
include __DIR__ . '/includes/header.php';
?>
<h1>Daftar Akun</h1>
<?php if ($error) echo '<div class="alert alert-danger">' . e($error) . '</div>'; ?>
<form method="post" style="max-width:500px">
  <?= csrf() ?>
  <input type="hidden" name="next" value="<?= e($next) ?>">
  <label>Nama lengkap</label><input name="nama" value="<?= e($f['nama']) ?>" class="form-control mb-2" required>
  <label>Username</label><input name="username" value="<?= e($f['username']) ?>" class="form-control mb-2" required>
  <label>Password (min 6 karakter)</label><input type="password" name="password" class="form-control mb-2" required>
  <label>Ulangi password</label><input type="password" name="ulang" class="form-control mb-2" required>
  <label>No. telepon / WA</label><input name="telepon" value="<?= e($f['telepon']) ?>" class="form-control mb-2">
  <label>Alamat</label><textarea name="alamat" class="form-control mb-2"><?= e($f['alamat']) ?></textarea>
  <button class="btn btn-primary">Daftar</button>
  <a href="login.php">Sudah punya akun?</a>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
