<?php
session_start();
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../config/config.php';
require_admin();

$admin_active = 'tim';
$topbar_title = 'Data Tim Pendaftar';

// Filter
$q          = trim($_GET['q'] ?? '');
$f_divisi   = (int)($_GET['divisi'] ?? 0);
$f_regional = (int)($_GET['regional'] ?? 0);

$sql = "SELECT t.*, d.singkatan, r.nama_regional, u.email,
               (SELECT COUNT(*) FROM anggota WHERE tim_id=t.id_tim) AS jum_anggota
        FROM tim t
        LEFT JOIN divisi d   ON t.divisi_id=d.id_divisi
        LEFT JOIN regional r ON t.regional_id=r.id_regional
        LEFT JOIN users u    ON t.user_id=u.id
        WHERE 1=1";
$params = [];
if ($q !== '') { $sql .= " AND (t.nama_tim LIKE ? OR t.asal_pt LIKE ? OR t.no_pendaftaran LIKE ?)"; $params[]="%$q%"; $params[]="%$q%"; $params[]="%$q%"; }
if ($f_divisi)   { $sql .= " AND t.divisi_id = ?";   $params[] = $f_divisi; }
if ($f_regional) { $sql .= " AND t.regional_id = ?"; $params[] = $f_regional; }
$sql .= " ORDER BY t.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tims = $stmt->fetchAll();

$divisi   = $pdo->query("SELECT * FROM divisi ORDER BY id_divisi")->fetchAll();
$regional = $pdo->query("SELECT * FROM regional ORDER BY id_regional")->fetchAll();

// Handle delete
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='delete' && csrf_verify()) {
    $pdo->prepare("DELETE FROM tim WHERE id_tim=?")->execute([(int)$_POST['id_tim']]);
    redirect('tim.php?msg=deleted');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><title><?= $topbar_title ?> — SIMDAF KRI 2026</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/icons/css/all.min.css">
</head>
<body>
<div class="dash-wrapper">
  <?php include __DIR__ . '/../templates/sidebar-admin.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../templates/topbar.php'; ?>
    <div class="page-body">

      <?php if (($_GET['msg']??'')==='deleted'): ?>
        <div class="alert alert-success"><i class="fas fa-check"></i> Tim berhasil dihapus.</div>
      <?php endif; ?>

      <div class="card">
        <form method="get" style="display:grid;grid-template-columns:1fr 200px 200px auto;gap:12px;margin-bottom:24px;">
          <input type="text" name="q" placeholder="Cari nama tim, PT, atau no. pendaftaran..." value="<?= e($q) ?>">
          <select name="divisi"><option value="0">Semua Divisi</option>
            <?php foreach ($divisi as $d): ?><option value="<?= $d['id_divisi'] ?>" <?= $f_divisi==$d['id_divisi']?'selected':'' ?>><?= e($d['singkatan']) ?></option><?php endforeach; ?>
          </select>
          <select name="regional"><option value="0">Semua Regional</option>
            <?php foreach ($regional as $r): ?><option value="<?= $r['id_regional'] ?>" <?= $f_regional==$r['id_regional']?'selected':'' ?>><?= e($r['nama_regional']) ?></option><?php endforeach; ?>
          </select>
          <button class="btn btn-primary">Filter</button>
        </form>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
          <span style="color:#4a5568;font-size:13px;">Menampilkan <b style="color:#1A4F8A;"><?= count($tims) ?></b> tim</span>
          <div style="display:flex;gap:8px;">
            <a href="laporan.php?export=excel" class="btn btn-sm btn-outline"><i class="fas fa-chart-bar"></i> Export Excel</a>
            <a href="laporan.php" class="btn btn-sm btn-outline"><i class="fas fa-chart-bar"></i> Laporan Lengkap</a>
          </div>
        </div>

        <table>
          <thead><tr><th>No. Daftar</th><th>Nama Tim</th><th>Asal PT</th><th>Divisi</th><th>Regional</th><th>Anggota</th><th>Status</th><th>Hasil</th><th>Aksi</th></tr></thead>
          <tbody>
            <?php if (!$tims): ?>
              <tr class="table-empty"><td colspan="9">Tidak ada tim yang cocok dengan filter.</td></tr>
            <?php else: foreach ($tims as $t): ?>
              <tr>
                <td><strong><?= e($t['no_pendaftaran']) ?></strong></td>
                <td><?= e($t['nama_tim']) ?><br><small style="color:#718096;"><?= e($t['email']) ?></small></td>
                <td><?= e($t['asal_pt']) ?></td>
                <td><span class="badge bg-primary"><?= e($t['singkatan']) ?></span></td>
                <td><?= e($t['nama_regional']) ?></td>
                <td><?= (int)$t['jum_anggota'] ?> orang</td>
                <td><?= badge_status($t['status']) ?></td>
                <td><?= badge_status($t['hasil_seleksi']) ?></td>
                <td class="col-aksi">
                  <a href="verifikasi.php?id=<?= $t['id_tim'] ?>" class="btn btn-sm btn-outline">Detail</a>
                  <form method="post" style="display:inline;" onsubmit="return confirm('Hapus tim ini? Data anggota dan dokumen juga akan terhapus.');">
                    <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id_tim" value="<?= $t['id_tim'] ?>">
                    <button class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>
</body>
</html>
