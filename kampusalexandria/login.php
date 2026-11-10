<?php
// satu halaman login untuk admin dan pelanggan
require __DIR__ . '/config.php';
$next = $_GET['next'] ?? $_POST['next'] ?? '';
$tujuan = ['checkout' => 'checkout.php', 'pesanan' => 'pesanan_saya.php'][$next] ?? 'index.php';
if (is_admin()) go('admin/index.php');
if (is_user()) go($tujuan);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    foreach (['admin', 'pelanggan'] as $tabel) {
        $st = $pdo->prepare("SELECT * FROM $tabel WHERE username = ?");
        $st->execute([trim($_POST['username'] ?? '')]);
        $r = $st->fetch();
        if ($r && password_verify($_POST['password'] ?? '', $r['password'])) {
            session_regenerate_id(true);
            if ($tabel === 'admin') { $_SESSION['admin_id'] = $r['id']; go('admin/index.php'); }
            $_SESSION['user_id'] = $r['id'];
            $_SESSION['user_nama'] = $r['nama'];
            go($tujuan);
        }
    }
    $error = 'Username atau password salah.';
}

$title = 'Login';
include __DIR__ . '/includes/header.php';
?>
<h1>Login</h1>
<?php if ($error) echo '<div class="alert alert-danger">' . e($error) . '</div>'; ?>
<form method="post" style="max-width:380px">
  <?= csrf() ?>
  <input type="hidden" name="next" value="<?= e($next) ?>">
  <label>Username</label><input name="username" class="form-control mb-2" required autofocus>
  <label>Password</label><input type="password" name="password" class="form-control mb-2" required>
  <button class="btn btn-primary">Masuk</button>
  <a href="register.php?next=<?= e($next) ?>">Belum punya akun?</a>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
