<?php
session_start();
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../config/config.php';
require_ketua();

$ketua_active = 'laporan';
$topbar_title = 'Submit Laporan Teknis (Tahap II)';

$stmt = $pdo->prepare("SELECT * FROM tim WHERE user_id=? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$tim = $stmt->fetch();

if (!$tim) redirect('formulir.php');

$lap = [];
$stmt = $pdo->prepare("SELECT * FROM laporan_teknis WHERE tim_id=?");
$stmt->execute([$tim['id_tim']]);
$lap = $stmt->fetch() ?: [];

$err=''; $msg='';

// cek eligibilitas submit
$bisa_submit = $tim['hasil_seleksi'] === 'lolos';

if ($_SERVER['REQUEST_METHOD']==='POST' && $bisa_submit) {
    if (!csrf_verify()) $err = 'Token tidak valid.';
    else {
        $link_video = trim($_POST['link_video'] ?? '');
        $file_lap   = $lap['file_laporan']     ?? null;
        $foto_fin   = $lap['foto_robot_final'] ?? null;

        if (!empty($_FILES['file_laporan']) && $_FILES['file_laporan']['error']===UPLOAD_ERR_OK) {
            $r = upload_file($_FILES['file_laporan'], $tim['no_pendaftaran'].'_laporan', EXT_DOC);
            if (!$r) { $err='Gagal upload file laporan.'; }
            else $file_lap = $r;
        }
        if (!$err && !empty($_FILES['foto_robot_final']) && $_FILES['foto_robot_final']['error']===UPLOAD_ERR_OK) {
            $r = upload_file($_FILES['foto_robot_final'], $tim['no_pendaftaran'].'_robot_final', EXT_IMG);
            if (!$r) { $err='Gagal upload foto.'; }
            else $foto_fin = $r;
        }

        if (!$err) {
            if (!empty($lap['id_laporan'])) {
                $pdo->prepare("UPDATE laporan_teknis SET file_laporan=?,link_video=?,foto_robot_final=? WHERE tim_id=?")
                    ->execute([$file_lap,$link_video,$foto_fin,$tim['id_tim']]);
            } else {
                $pdo->prepare("INSERT INTO laporan_teknis (tim_id,file_laporan,link_video,foto_robot_final) VALUES (?,?,?,?)")
                    ->execute([$tim['id_tim'],$file_lap,$link_video,$foto_fin]);
            }
            redirect('laporan.php?msg=submitted');
        }
    }
}
if (($_GET['msg']??'')==='submitted') $msg='Laporan teknis berhasil dikirim!';
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
  <?php include __DIR__ . '/../templates/sidebar-ketua.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../templates/topbar.php'; ?>
    <div class="page-body">
      <?php if ($msg): ?><div class="alert alert-success"><i class="fas fa-check"></i> <?= e($msg) ?></div><?php endif; ?>
      <?php if ($err): ?><div class="alert alert-danger"><i class="fas fa-times"></i> <?= e($err) ?></div><?php endif; ?>

      <?php if (!$bisa_submit): ?>
        <div class="card" style="text-align:center;padding:48px;">
          <div style="font-size:48px;margin-bottom:12px;"><i class="fas fa-hourglass-half"></i></div>
          <h2 style="color:#1A4F8A;margin-bottom:10px;">Belum Lolos Tahap I</h2>
          <p style="color:#4a5568;line-height:1.7;">
            Submit Laporan Teknis hanya tersedia untuk tim yang dinyatakan <b>Lolos Seleksi Tahap I</b>.<br>
            Status seleksi Anda saat ini: <?= badge_status($tim['hasil_seleksi']) ?>
          </p>
          <a href="dashboard.php" class="btn btn-primary mt-3" style="margin-top:24px;">Kembali ke Dashboard</a>
        </div>
      <?php else: ?>
        <div class="alert alert-info">
          <i class="fas fa-star"></i> Selamat! Tim <b><?= e($tim['nama_tim']) ?></b> dinyatakan <b>Lolos Tahap I</b>. Silakan submit laporan teknis & demo robot untuk Seleksi Tahap II.
        </div>

        <form method="post" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <div class="card">
            <div class="card-header"><div class="card-title"><i class="fas fa-file-pdf"></i> Laporan Teknis</div></div>
            <div class="form-group">
              <label>File Laporan Teknis (PDF, maks 2 MB) <span class="required">*</span></label>
              <?php if (!empty($lap['file_laporan'])): ?>
                <div style="background:#d1fae5;padding:10px 14px;border-radius:8px;color:#065f46;margin-bottom:8px;font-size:13px;"><i class="fas fa-paperclip"></i> <?= e($lap['file_laporan']) ?></div>
              <?php endif; ?>
              <input type="file" name="file_laporan" accept=".pdf">
            </div>
          </div>

          <div class="card">
            <div class="card-header"><div class="card-title"><i class="fas fa-film"></i> Video Demo Robot</div></div>
            <div class="form-group">
              <label>Link Video (YouTube / Google Drive) <span class="required">*</span></label>
              <input type="url" name="link_video" placeholder="https://youtube.com/watch?v=..." value="<?= e($lap['link_video'] ?? '') ?>">
              <p class="form-hint">Pastikan video dapat diakses publik atau "siapa saja dengan tautan".</p>
            </div>
          </div>

          <div class="card">
            <div class="card-header"><div class="card-title"><i class="fas fa-camera"></i> Foto Robot Final</div></div>
            <div class="form-group">
              <label>Foto Robot (JPG / PNG, maks 2 MB)</label>
              <?php if (!empty($lap['foto_robot_final'])): ?>
                <div style="background:#d1fae5;padding:10px 14px;border-radius:8px;color:#065f46;margin-bottom:8px;font-size:13px;"><i class="fas fa-paperclip"></i> <?= e($lap['foto_robot_final']) ?></div>
              <?php endif; ?>
              <input type="file" name="foto_robot_final" accept=".jpg,.jpeg,.png">
            </div>
          </div>

          <?php if (!empty($lap['hasil_tahap2']) && $lap['hasil_tahap2']!=='pending'): ?>
            <div class="card">
              <div class="card-title">Hasil Penilaian Juri</div>
              <p style="margin-top:12px;">Status: <?= badge_status($lap['hasil_tahap2']) ?></p>
              <?php if (!empty($lap['catatan_juri'])): ?>
                <p style="margin-top:8px;color:#4a5568;">Catatan: <?= e($lap['catatan_juri']) ?></p>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <div style="display:flex;gap:12px;">
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-upload"></i> Submit Laporan</button>
            <a href="dashboard.php" class="btn btn-outline btn-lg">Kembali</a>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
