<?php
session_start();
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../config/config.php';
require_admin();

$admin_active = 'verifikasi';
$topbar_title = 'Verifikasi Berkas';

$detail_id = (int)($_GET['id'] ?? 0);

// proses verifikasi berkas
if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    $id = (int)$_POST['id_tim'];
    $aksi = $_POST['aksi'] ?? '';
    $catatan = trim($_POST['catatan'] ?? '');

    if ($aksi==='valid')        $pdo->prepare("UPDATE tim SET status='valid', catatan_verifikasi=? WHERE id_tim=?")->execute([$catatan,$id]);
    elseif ($aksi==='tidak_valid') $pdo->prepare("UPDATE tim SET status='tidak_valid', catatan_verifikasi=? WHERE id_tim=?")->execute([$catatan,$id]);
    elseif ($aksi==='pending')  $pdo->prepare("UPDATE tim SET status='pending', catatan_verifikasi=? WHERE id_tim=?")->execute([$catatan,$id]);

    redirect('verifikasi.php' . ($detail_id?'?id='.$detail_id:'') . '&msg=ok');
}

if ($detail_id) {
    $stmt = $pdo->prepare("SELECT t.*, d.nama_divisi, d.singkatan, r.nama_regional, u.email
                           FROM tim t LEFT JOIN divisi d ON t.divisi_id=d.id_divisi
                           LEFT JOIN regional r ON t.regional_id=r.id_regional
                           LEFT JOIN users u ON t.user_id=u.id
                           WHERE t.id_tim=?");
    $stmt->execute([$detail_id]);
    $tim = $stmt->fetch();
    if (!$tim) redirect('verifikasi.php');

    $stmt = $pdo->prepare("SELECT * FROM anggota WHERE tim_id=? ORDER BY id_anggota");
    $stmt->execute([$detail_id]); $anggota = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT * FROM dokumen WHERE tim_id=?");
    $stmt->execute([$detail_id]); $dok = $stmt->fetch() ?: [];
} else {
    $list = $pdo->query("SELECT t.*, d.singkatan,
                                (SELECT COUNT(*) FROM dokumen WHERE tim_id=t.id_tim) AS has_doc
                         FROM tim t LEFT JOIN divisi d ON t.divisi_id=d.id_divisi
                         ORDER BY t.created_at DESC")->fetchAll();
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

      <?php if (($_GET['msg']??'')==='ok'): ?><div class="alert alert-success"><i class="fas fa-check"></i> Status verifikasi diperbarui.</div><?php endif; ?>

      <?php if ($detail_id && $tim): ?>
        <!-- DETAIL -->
        <a href="verifikasi.php" class="btn btn-outline btn-sm mb-3" style="margin-bottom:16px;">← Kembali ke Daftar</a>

        <div class="card">
          <div class="card-header">
            <div>
              <div class="card-title"><?= e($tim['nama_tim']) ?></div>
              <div style="color:#718096;font-size:13px;margin-top:4px;"><?= e($tim['no_pendaftaran']) ?> · <?= e($tim['asal_pt']) ?></div>
            </div>
            <div><?= badge_status($tim['status']) ?></div>
          </div>
          <table style="width:100%;">
            <tr><td style="padding:6px 0;color:#4a5568;width:30%;">Divisi</td><td><b><?= e($tim['nama_divisi']) ?></b></td></tr>
            <tr><td style="padding:6px 0;color:#4a5568;">Regional</td><td><?= e($tim['nama_regional']) ?></td></tr>
            <tr><td style="padding:6px 0;color:#4a5568;">Email Ketua</td><td><?= e($tim['email']) ?></td></tr>
            <tr><td style="padding:6px 0;color:#4a5568;">Dosen Pembimbing</td><td><?= e($tim['nama_dosen']) ?> (<?= e($tim['nidn_dosen']) ?>)</td></tr>
          </table>
        </div>

        <div class="card">
          <div class="card-header"><div class="card-title">Anggota Tim (<?= count($anggota) ?>)</div></div>
          <table>
            <thead><tr><th>#</th><th>Nama</th><th>NIM</th><th>Prodi</th><th>HP</th></tr></thead>
            <tbody><?php foreach ($anggota as $i=>$a): ?>
              <tr><td><?= $i+1 ?></td><td><strong><?= e($a['nama_lengkap']) ?></strong></td><td><?= e($a['nim']) ?></td><td><?= e($a['prodi']) ?></td><td><?= e($a['no_hp']) ?></td></tr>
            <?php endforeach; ?></tbody>
          </table>
        </div>

        <div class="card">
          <div class="card-header"><div class="card-title">Dokumen Diunggah</div></div>
          <div class="d-grid-2">
            <?php
            $dox = [
                'proposal_teknis' =>'Proposal Teknis Robot',
                'foto_robot' =>'Foto Desain Robot',
                'surat_pengantar' =>'Surat Pengantar PT',
                'ktm_anggota' =>'KTM Anggota',
                'surat_pernyataan' =>'Surat Pernyataan Keaslian'];
            foreach ($dox as $k=>$l):
                $f = $dok[$k] ?? '';
            ?>
              <div style="padding:14px;background:#F0F4F8;border-radius:10px;">
                <div style="font-size:13px;color:#4a5568;margin-bottom:4px;"><?= $l ?></div>
                <?php if ($f): ?>
                  <a href="<?= UPLOAD_URL . e($f) ?>" target="_blank" style="font-weight:600;color:#1A4F8A;font-size:14px;text-decoration:none;"><i class="fas fa-paperclip"></i> <?= e($f) ?></a>
                <?php else: ?>
                  <span style="color:#EF4444;font-size:13px;font-weight:600;"><i class="fas fa-times"></i> Belum diunggah</span>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><div class="card-title">Aksi Verifikasi</div></div>
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="id_tim" value="<?= $detail_id ?>">
            <div class="form-group">
              <label>Catatan untuk Tim (opsional, akan dilihat oleh peserta)</label>
              <textarea name="catatan" rows="3" placeholder="Tulis catatan jika ada..."><?= e($tim['catatan_verifikasi']) ?></textarea>
            </div>
            <div style="display:flex;gap:10px;">
              <button name="aksi" value="valid"        class="btn btn-success"><i class="fas fa-check"></i> Tandai Valid</button>
              <button name="aksi" value="tidak_valid"  class="btn btn-danger"><i class="fas fa-times"></i> Tandai Tidak Valid</button>
              <button name="aksi" value="pending"      class="btn btn-warning"><i class="fas fa-hourglass-half"></i> Reset ke Pending</button>
            </div>
          </form>
        </div>

      <?php else: ?>
        <!-- LIST -->
        <div class="card">
          <div class="card-header"><div class="card-title">Antrian Verifikasi Berkas</div></div>
          <table>
            <thead><tr><th>No. Daftar</th><th>Nama Tim</th><th>Divisi</th><th>Asal PT</th><th>Dokumen</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
              <?php if (!$list): ?>
                <tr class="table-empty"><td colspan="7">Belum ada tim terdaftar.</td></tr>
              <?php else: foreach ($list as $t): ?>
                <tr>
                  <td><strong><?= e($t['no_pendaftaran']) ?></strong></td>
                  <td><?= e($t['nama_tim']) ?></td>
                  <td><span class="badge bg-primary"><?= e($t['singkatan']) ?></span></td>
                  <td><?= e($t['asal_pt']) ?></td>
                  <td><?= $t['has_doc']?'<span class="badge bg-success">Ada</span>':'<span class="badge bg-warning">Belum</span>' ?></td>
                  <td><?= badge_status($t['status']) ?></td>
                  <td><a href="?id=<?= $t['id_tim'] ?>" class="btn btn-sm btn-primary btn-verifikasi">Verifikasi →</a></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>

    </div>
  </div>
</div>
</body>
</html>
