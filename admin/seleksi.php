<?php
session_start();
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../config/config.php';
require_admin();

$admin_active = 'seleksi';
$topbar_title = 'Seleksi Tim';

$tahap = $_GET['tahap'] ?? 'tahap1';

// Bulk update
if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    $aksi = $_POST['aksi'] ?? '';
    $id   = (int)$_POST['id_tim'];

    if ($tahap === 'tahap1') {
        if (in_array($aksi, ['lolos','cadangan','tidak_lolos','pending'])) {
            $pdo->prepare("UPDATE tim SET hasil_seleksi=? WHERE id_tim=?")->execute([$aksi,$id]);
        }
    } else {
        $catatan = trim($_POST['catatan'] ?? '');
        if (in_array($aksi, ['lolos','cadangan','tidak_lolos','pending'])) {
            $pdo->prepare("UPDATE laporan_teknis SET hasil_tahap2=?, catatan_juri=? WHERE tim_id=?")
                ->execute([$aksi,$catatan,$id]);
        }
    }
    redirect('seleksi.php?tahap=' . $tahap . '&msg=ok');
}

if ($tahap === 'tahap1') {
    $list = $pdo->query("SELECT t.*, d.singkatan, r.nama_regional
                         FROM tim t
                         LEFT JOIN divisi d ON t.divisi_id=d.id_divisi
                         LEFT JOIN regional r ON t.regional_id=r.id_regional
                         WHERE t.status='valid'
                         ORDER BY t.created_at DESC")->fetchAll();
} else {
    $list = $pdo->query("SELECT t.*, d.singkatan, r.nama_regional, l.id_laporan, l.file_laporan, l.link_video, l.hasil_tahap2, l.catatan_juri
                         FROM tim t
                         INNER JOIN laporan_teknis l ON l.tim_id=t.id_tim
                         LEFT JOIN divisi d ON t.divisi_id=d.id_divisi
                         LEFT JOIN regional r ON t.regional_id=r.id_regional
                         WHERE t.hasil_seleksi='lolos'
                         ORDER BY l.submitted_at DESC")->fetchAll();
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

      <?php if (($_GET['msg']??'')==='ok'): ?><div class="alert alert-success"><i class="fas fa-check"></i> Hasil seleksi diperbarui.</div><?php endif; ?>

      <div class="card" style="padding:8px;margin-bottom:20px;">
        <div style="display:flex;gap:6px;">
          <a href="?tahap=tahap1" class="btn <?= $tahap==='tahap1'?'btn-primary':'btn-outline' ?>" style="flex:1;justify-content:center;">Tahap I — Proposal Teknis</a>
          <a href="?tahap=tahap2" class="btn <?= $tahap==='tahap2'?'btn-primary':'btn-outline' ?>" style="flex:1;justify-content:center;">Tahap II — Laporan Teknis</a>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="card-title">
            <?= $tahap==='tahap1' ? 'Seleksi Tahap I — Berdasarkan Proposal Teknis' : 'Seleksi Tahap II — Berdasarkan Laporan & Demo Robot' ?>
          </div>
          <span style="color:#718096;font-size:13px;">Total: <b style="color:#1A4F8A;"><?= count($list) ?></b> tim</span>
        </div>

        <?php if ($tahap === 'tahap1'): ?>
          <table>
            <thead><tr><th>No.</th><th>Tim</th><th>Divisi</th><th>Regional</th><th>Hasil Saat Ini</th><th>Aksi Seleksi</th></tr></thead>
            <tbody>
              <?php if (!$list): ?>
                <tr class="table-empty"><td colspan="6">Belum ada tim yang berkasnya valid untuk seleksi.</td></tr>
              <?php else: foreach ($list as $t): ?>
                <tr>
                  <td><strong><?= e($t['no_pendaftaran']) ?></strong></td>
                  <td><?= e($t['nama_tim']) ?><br><small style="color:#718096;"><?= e($t['asal_pt']) ?></small></td>
                  <td><span class="badge bg-primary"><?= e($t['singkatan']) ?></span></td>
                  <td><?= e($t['nama_regional']) ?></td>
                  <td><?= badge_status($t['hasil_seleksi']) ?></td>
                  <td>
                    <form method="post" style="display:flex;gap:4px;">
                      <?= csrf_field() ?>
                      <input type="hidden" name="id_tim" value="<?= $t['id_tim'] ?>">
                      <button name="aksi" value="lolos"       class="btn btn-sm btn-success" title="Lolos"><i class="fas fa-check"></i></button>
                      <button name="aksi" value="cadangan"    class="btn btn-sm btn-warning" title="Cadangan"><i class="fas fa-bookmark"></i></button>
                      <button name="aksi" value="tidak_lolos" class="btn btn-sm btn-danger"  title="Tidak Lolos"><i class="fas fa-times"></i></button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        <?php else: ?>
          <?php if (!$list): ?>
            <p style="text-align:center;color:#718096;padding:30px;font-style:italic;">Belum ada laporan teknis yang masuk.</p>
          <?php else: foreach ($list as $t): ?>
            <div style="padding:20px;background:#F0F4F8;border-radius:10px;margin-bottom:14px;">
              <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;">
                <div>
                  <div style="font-weight:700;color:#1a1e2e;font-size:16px;"><?= e($t['nama_tim']) ?> · <span class="badge bg-primary"><?= e($t['singkatan']) ?></span></div>
                  <div style="color:#718096;font-size:13px;margin-top:4px;"><?= e($t['no_pendaftaran']) ?> · <?= e($t['asal_pt']) ?> · <?= e($t['nama_regional']) ?></div>
                </div>
                <?= badge_status($t['hasil_tahap2'] ?? 'pending') ?>
              </div>
              <div style="display:flex;gap:14px;margin-bottom:14px;flex-wrap:wrap;">
                <?php if ($t['file_laporan']): ?>
                  <a href="<?= UPLOAD_URL . e($t['file_laporan']) ?>" target="_blank" class="btn btn-sm btn-outline"><i class="fas fa-file-pdf"></i> Laporan PDF</a>
                <?php endif; ?>
                <?php if ($t['link_video']): ?>
                  <a href="<?= e($t['link_video']) ?>" target="_blank" class="btn btn-sm btn-outline"><i class="fas fa-film"></i> Video Demo</a>
                <?php endif; ?>
              </div>
              <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id_tim" value="<?= $t['id_tim'] ?>">
                <input type="text" name="catatan" placeholder="Catatan juri (opsional)" value="<?= e($t['catatan_juri'] ?? '') ?>" style="margin-bottom:10px;">
                <div style="display:flex;gap:6px;">
                  <button name="aksi" value="lolos"       class="btn btn-sm btn-success"><i class="fas fa-check"></i> Lolos</button>
                  <button name="aksi" value="cadangan"    class="btn btn-sm btn-warning"><i class="fas fa-bookmark"></i> Cadangan</button>
                  <button name="aksi" value="tidak_lolos" class="btn btn-sm btn-danger"><i class="fas fa-times"></i> Tidak Lolos</button>
                  <button name="aksi" value="pending"     class="btn btn-sm btn-outline"><i class="fas fa-hourglass-half"></i> Reset</button>
                </div>
              </form>
            </div>
          <?php endforeach; endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
</body>
</html>
