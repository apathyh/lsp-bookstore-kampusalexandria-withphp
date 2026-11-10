<?php
// ganti password (admin dan pelanggan)
require __DIR__ . '/config.php';
if (!is_admin() && !is_user()) go('login.php');
$tabel = is_admin() ? 'admin' : 'pelanggan';
$id = is_admin() ? $_SESSION['admin_id'] : $_SESSION['user_id'];

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $st = $pdo->prepare("SELECT password FROM $tabel WHERE id = ?");
    $st->execute([$id]);
    $baru = $_POST['baru'] ?? '';
    if (!password_verify($_POST['lama'] ?? '', $st->fetchColumn())) $error = 'Password lama salah.';
    elseif (strlen($baru) < 6) $error = 'Password baru minimal 6 karakter.';
    elseif ($baru !== ($_POST['ulang'] ?? '')) $error = 'Konfirmasi password tidak sama.';
    else {
        $pdo->prepare("UPDATE $tabel SET password = ? WHERE id = ?")->execute([password_hash($baru, PASSWORD_DEFAULT), $id]);
        flash('Password berhasil diganti.');
        go('index.php');
    }
}

$title = 'Ganti Password';
include __DIR__ . '/includes/header.php';
?>
<h1>Ganti Password</h1>
<?php if ($error) echo '<div class="alert alert-danger">' . e($error) . '</div>'; ?>
<form method="post" style="max-width:380px">
  <?= csrf() ?>
  <label>Password lama</label><input type="password" name="lama" class="form-control mb-2" required>
  <label>Password baru (min 6 karakter)</label><input type="password" name="baru" class="form-control mb-2" required>
  <label>Ulangi password baru</label><input type="password" name="ulang" class="form-control mb-2" required>
  <button class="btn btn-primary">Simpan</button>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
