<?php
session_start();
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../config/config.php';
require_ketua();

$ketua_active = 'pengumuman';
$topbar_title = 'Pengumuman';

$list = $pdo->query("SELECT p.*, u.nama AS author FROM pengumuman p
                     LEFT JOIN users u ON p.created_by=u.id
                     WHERE p.is_published=1 ORDER BY p.published_at DESC")->fetchAll();
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

      <?php if (!$list): ?>
        <div class="card" style="text-align:center;padding:48px;">
          <div style="font-size:48px;margin-bottom:12px;"><i class="fas fa-envelope-open"></i></div>
          <p style="color:#718096;">Belum ada pengumuman dari panitia.</p>
        </div>
      <?php else: foreach ($list as $p): ?>
        <div class="card">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
            <div>
              <span class="badge <?= $p['tahap']==='tahap1'?'bg-info':($p['tahap']==='tahap2'?'bg-primary':'bg-secondary') ?>" style="margin-bottom:8px;display:inline-block;">
                <?= strtoupper($p['tahap']) ?>
              </span>
              <h3 style="font-size:20px;font-weight:700;color:#1a1e2e;"><?= e($p['judul']) ?></h3>
            </div>
            <div style="text-align:right;font-size:12px;color:#718096;">
              <i class="fas fa-calendar"></i> <?= tgl_id($p['published_at']) ?><br>
              <?php if ($p['author']): ?>oleh <?= e($p['author']) ?><?php endif; ?>
            </div>
          </div>
          <div style="color:#4a5568;line-height:1.8;white-space:pre-line;"><?= e($p['konten']) ?></div>
        </div>
      <?php endforeach; endif; ?>

    </div>
  </div>
</div>
</body>
</html>
