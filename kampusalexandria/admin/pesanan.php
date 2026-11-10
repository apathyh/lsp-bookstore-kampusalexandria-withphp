<?php
require __DIR__ . '/../config.php';
wajib_admin();
const STATUS = ['baru', 'diproses', 'dikirim', 'selesai', 'batal'];
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id) {                                            // ---------- detail satu pesanan ----------
    $st = $pdo->prepare('SELECT * FROM pesanan WHERE id = ?');
    $st->execute([$id]);
    $p = $st->fetch();
    if (!$p) { flash('Pesanan tidak ditemukan.'); go('admin/pesanan.php'); }
    $st = $pdo->prepare('SELECT * FROM pesanan_item WHERE pesanan_id = ?');
    $st->execute([$id]);
    $items = $st->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        cek_csrf();
        $baru = $_POST['status'] ?? '';
        if (in_array($baru, STATUS, true) && $baru !== $p['status']) {
            $ke_batal = $baru === 'batal';
            try {
                $pdo->beginTransaction();
                if ($ke_batal || $p['status'] === 'batal') {   // batal: stok kembali. Aktif lagi: stok dipotong lagi
                    $up = $pdo->prepare($ke_batal ? 'UPDATE buku SET stok = stok + ? WHERE id = ?' : 'UPDATE buku SET stok = stok - ? WHERE id = ? AND stok >= ?');
                    foreach ($items as $i) {
                        if (!$i['buku_id']) continue;
                        $up->execute($ke_batal ? [$i['qty'], $i['buku_id']] : [$i['qty'], $i['buku_id'], $i['qty']]);
                        if (!$ke_batal && !$up->rowCount()) throw new Exception('Stok "' . $i['judul'] . '" tidak cukup untuk mengaktifkan lagi.');
                    }
                }
                $pdo->prepare('UPDATE pesanan SET status = ? WHERE id = ?')->execute([$baru, $id]);
                $pdo->commit();
                flash('Status diubah ke ' . $baru . '.');
            } catch (Exception $ex) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                flash($ex->getMessage());
            }
        }
        go('admin/pesanan.php?id=' . $id);
    }

    $title = 'Detail Pesanan';
    include __DIR__ . '/../includes/header.php';
?>
<p><a href="pesanan.php">&larr; Kembali</a></p>
<h1>Pesanan <?= e($p['kode']) ?></h1>
<p>Nama: <?= e($p['nama']) ?><br>
   Telepon: <?= e($p['telepon']) ?><br>
   Alamat: <?= nl2br(e($p['alamat'])) ?><br>
   Metode bayar: <b><?= e(METODE[$p['metode']]) ?></b><br>
   Tanggal: <?= e($p['created_at']) ?></p>
<table class="table">
  <tr><th>Buku</th><th>Harga</th><th>Qty</th><th>Subtotal</th></tr>
  <?php foreach ($items as $i): ?>
  <tr><td><?= e($i['judul']) ?></td><td><?= rupiah($i['harga']) ?></td><td><?= (int)$i['qty'] ?></td><td><?= rupiah($i['harga'] * $i['qty']) ?></td></tr>
  <?php endforeach; ?>
  <tr><th colspan="3">Total</th><th><?= rupiah($p['total']) ?></th></tr>
</table>
<form method="post" class="d-flex gap-2" style="max-width:350px">
  <?= csrf() ?><input type="hidden" name="id" value="<?= $id ?>">
  <select name="status" class="form-select">
    <?php foreach (STATUS as $s): ?><option value="<?= $s ?>" <?= $p['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
  </select>
  <button class="btn btn-primary text-nowrap">Ubah status</button>
</form>
<?php
} else {                                              // ---------- daftar pesanan ----------
    $status = in_array($_GET['status'] ?? '', STATUS, true) ? $_GET['status'] : '';
    $st = $pdo->prepare("SELECT * FROM pesanan WHERE (? = '' OR status = ?) ORDER BY id DESC");
    $st->execute([$status, $status]);
    $title = 'Pesanan';
    include __DIR__ . '/../includes/header.php';
?>
<h1>Pesanan</h1>
<p><a href="pesanan.php">Semua</a><?php foreach (STATUS as $s) echo ' | <a href="?status=' . $s . '">' . ucfirst($s) . '</a>'; ?></p>
<table class="table">
  <tr><th>Kode</th><th>Pemesan</th><th>Metode</th><th>Total</th><th>Status</th><th>Tanggal</th><th></th></tr>
  <?php foreach ($st as $p): ?>
  <tr>
    <td><?= e($p['kode']) ?></td><td><?= e($p['nama']) ?></td><td><?= e(strtoupper($p['metode'])) ?></td>
    <td><?= rupiah($p['total']) ?></td><td><?= e($p['status']) ?></td><td><?= e($p['created_at']) ?></td>
    <td><a href="?id=<?= $p['id'] ?>">Detail</a></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php } include __DIR__ . '/../includes/footer.php'; ?>
