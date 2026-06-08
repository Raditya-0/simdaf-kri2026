<?php
session_start();
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../config/config.php';
require_ketua();

$ketua_active = 'dokumen';
$topbar_title = 'Upload Dokumen';

$stmt = $pdo->prepare("SELECT * FROM tim WHERE user_id=? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$tim = $stmt->fetch();

if (!$tim) {
    redirect('formulir.php?need_tim=1');
}

$stmt = $pdo->prepare("SELECT * FROM dokumen WHERE tim_id=?");
$stmt->execute([$tim['id_tim']]);
$dok = $stmt->fetch() ?: [];

$msg = ''; $err = '';
$fields = [
    'proposal_teknis'  => ['Proposal Teknis Robot',    'PDF',          EXT_DOC,  '<i class="fas fa-file-pdf"></i>'],
    'foto_robot'       => ['Foto Desain Robot',        'JPG / PNG',    EXT_IMG,  '<i class="fas fa-image"></i>'],
    'surat_pengantar'  => ['Surat Pengantar PT',       'PDF',          EXT_DOC,  '<i class="fas fa-scroll"></i>'],
    'ktm_anggota'      => ['KTM Seluruh Anggota',      'PDF / JPG',    EXT_BOTH, '<i class="fas fa-id-card"></i>'],
    'surat_pernyataan' => ['Surat Pernyataan Keaslian','PDF',          EXT_DOC,  '<i class="fas fa-signature"></i>'],
];

if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!csrf_verify()) $err = 'Token tidak valid.';
    else {
        $updates = [];
        $params  = [];

        foreach ($fields as $key => $meta) {
            if (!empty($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK) {
                $fname = upload_file($_FILES[$key], $tim['no_pendaftaran'] . '_' . $key, $meta[2]);
                if ($fname === false) {
                    $err = "Gagal upload {$meta[0]} (cek format & ukuran ≤ 2 MB).";
                    break;
                }
                $updates[] = "$key = ?";
                $params[]  = $fname;
            }
        }

        if (!$err && $updates) {
            if (!empty($dok)) {
                $params[] = $tim['id_tim'];
                $sql = "UPDATE dokumen SET " . implode(',', $updates) . " WHERE tim_id = ?";
                $pdo->prepare($sql)->execute($params);
            } else {
                $cols  = array_map(fn($u)=>explode(' ',$u)[0], $updates);
                $place = implode(',', array_fill(0, count($cols), '?'));
                $sql = "INSERT INTO dokumen (tim_id," . implode(',', $cols) . ") VALUES (?,$place)";
                $pdo->prepare($sql)->execute(array_merge([$tim['id_tim']], $params));
            }
            // reset status pending
            $pdo->prepare("UPDATE tim SET status='pending' WHERE id_tim=?")->execute([$tim['id_tim']]);
            redirect('dokumen.php?msg=uploaded');
        }
    }
}

if (($_GET['msg'] ?? '') === 'uploaded') $msg = 'Dokumen berhasil diunggah!';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?= $topbar_title ?> — SIMDAF KRI 2026</title>
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

      <div class="alert alert-info">
        <i class="fas fa-circle-info"></i> <b>Ketentuan Upload:</b> Maks. 2 MB per file. Format sesuai keterangan. Tim: <b><?= e($tim['nama_tim']) ?></b> · No. Pendaftaran: <b><?= e($tim['no_pendaftaran']) ?></b>
      </div>

      <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="d-grid-2">
          <?php foreach ($fields as $key => $meta):
              [$label, $format, $exts, $icon] = $meta;
              $uploaded = !empty($dok[$key]);
          ?>
            <div class="card" style="margin-bottom:0;">
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <div>
                  <div style="font-size:15px;font-weight:700;color:#1a1e2e;"><?= $icon ?> <?= e($label) ?></div>
                  <div style="font-size:12px;color:#718096;">Format: <?= e($format) ?> · Maks 2 MB</div>
                </div>
                <?php if ($uploaded): ?>
                  <span class="badge bg-success"><i class="fas fa-check"></i> Terunggah</span>
                <?php else: ?>
                  <span class="badge bg-warning">Belum</span>
                <?php endif; ?>
              </div>
              <?php if ($uploaded): ?>
                <div style="background:#d1fae5;padding:10px 14px;border-radius:8px;font-size:13px;color:#065f46;margin-bottom:12px;display:flex;align-items:center;justify-content:space-between;">
                  <span><i class="fas fa-paperclip"></i> <?= e($dok[$key]) ?></span>
                  <a href="<?= UPLOAD_URL . e($dok[$key]) ?>" target="_blank"
                     style="background:#1A4F8A;color:white;padding:4px 12px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:600;white-space:nowrap;margin-left:10px;">
                    Lihat
                  </a>
                </div>
              <?php endif; ?>
              <input type="file" name="<?= $key ?>" accept=".<?= implode(',.', $exts) ?>" style="padding:10px;">
            </div>
          <?php endforeach; ?>
        </div>

        <div class="card mt-4" style="margin-top:24px;">
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
              <b style="color:#1A4F8A;">Sudah lengkap?</b>
              <p style="color:#4a5568;font-size:13px;margin-top:4px;">Klik tombol di samping untuk menyimpan & mengirim dokumen ke panitia.</p>
            </div>
            <div style="display:flex;gap:10px;">
              <a href="dashboard.php" class="btn btn-outline">Kembali</a>
              <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-floppy-disk"></i> Simpan Dokumen</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
