<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'kampusalexandria');
define('DB_USER', 'root');
define('DB_PASS', '');

define('BASE_URL', '/kampusalexandria');   // nama folder di htdocs, awali dengan /
define('NAMA_TOKO', 'Kampus Alexandria');
define('WA_ADMIN', '6281234567890');       // nomor WhatsApp admin, awali 62 (tanpa + dan tanpa 0)
define('REKENING', 'BCA 1234567890 a.n. Kampus Alexandria');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
const METODE = ['cod' => 'COD (bayar di tempat saat barang tiba)', 'transfer' => 'Transfer (konfirmasi ke admin lewat WhatsApp)'];

session_start();
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
} catch (PDOException $e) {
    die('Koneksi database gagal. Cek config.php, nyalakan MySQL di XAMPP, lalu import database.sql.');
}

function e($s)         { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rupiah($n)    { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
function go($path)     { header('Location: ' . BASE_URL . '/' . $path); exit; }
function is_admin()    { return !empty($_SESSION['admin_id']); }
function is_user()     { return !empty($_SESSION['user_id']); }
function wajib_admin() { if (!is_admin()) go('login.php'); }

function flash($m = null) {
    if ($m !== null) { $_SESSION['flash'] = $m; return; }
    $m = $_SESSION['flash'] ?? '';
    unset($_SESSION['flash']);
    return $m ? '<div class="alert alert-info">' . e($m) . '</div>' : '';
}

// token rahasia supaya form tidak bisa dikirim dari website lain
function csrf() {
    $_SESSION['csrf'] = $_SESSION['csrf'] ?? bin2hex(random_bytes(16));
    return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
}
function cek_csrf() {
    if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? '')))
        die('Request tidak valid, kembali dan coba lagi.');
}

function thumb($f) { return BASE_URL . '/uploads/' . ($f && is_file(UPLOAD_DIR . $f) ? e($f) : 'default.svg'); }

function upload($f) {
    if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($f['error'] !== UPLOAD_ERR_OK) throw new Exception('Upload gagal.');
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name'])] ?? null;
    if (!$ext || $f['size'] > 2 * 1024 * 1024) throw new Exception('Cover harus JPG/PNG/WEBP, maksimal 2 MB.');
    $nama = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . $nama)) throw new Exception('Gagal menyimpan gambar. Cek folder uploads.');
    return $nama;
}

function hapus_cover($f) { if ($f && is_file(UPLOAD_DIR . $f)) unlink(UPLOAD_DIR . $f); }

// isi keranjang: [daftar buku + qty + subtotal, total]
function keranjang($pdo) {
    $cart = $_SESSION['cart'] ?? [];
    $items = []; $total = 0;
    if ($cart) {
        $st = $pdo->prepare('SELECT * FROM buku WHERE id IN (' . implode(',', array_fill(0, count($cart), '?')) . ')');
        $st->execute(array_keys($cart));
        foreach ($st as $b) {
            $b['qty'] = min($cart[$b['id']], max(1, $b['stok']));
            $b['sub'] = $b['qty'] * $b['harga'];
            $total += $b['sub'];
            $items[] = $b;
        }
    }
    return [$items, $total];
}

function wa_link($p) {
    $teks = 'Halo admin, saya mau bayar pesanan ' . $p['kode'] . ' total ' . rupiah($p['total']);
    return 'https://wa.me/' . WA_ADMIN . '?text=' . urlencode($teks);
}
