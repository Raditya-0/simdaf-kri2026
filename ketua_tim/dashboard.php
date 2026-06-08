<?php
session_start();
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../config/config.php';
require_ketua();

$ketua_active = 'dashboard';
$topbar_title = 'Dashboard Ketua Tim';

$stmt = $pdo->prepare("SELECT t.*, d.nama_divisi, d.singkatan, r.nama_regional
                       FROM tim t
                       LEFT JOIN divisi d   ON t.divisi_id = d.id_divisi
                       LEFT JOIN regional r ON t.regional_id = r.id_regional
                       WHERE t.user_id = ? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$tim = $stmt->fetch();

$progress    = 0;
$tim_id      = $tim['id_tim'] ?? null;
$has_dokumen = false;
$has_laporan = false;

if ($tim) {
    $progress = 25;
    $jum_anggota = $pdo->prepare("SELECT COUNT(*) FROM anggota WHERE tim_id=?");
    $jum_anggota->execute([$tim_id]);
    if ($jum_anggota->fetchColumn() >= 3) $progress = 50;

    $cek_dok = $pdo->prepare("SELECT proposal_teknis,foto_robot,surat_pengantar,ktm_anggota,surat_pernyataan FROM dokumen WHERE tim_id=?");
    $cek_dok->execute([$tim_id]);
    $dok = $cek_dok->fetch();
    if ($dok) {
        $has_dokumen = true;
        $lengkap = !empty($dok['proposal_teknis']) && !empty($dok['foto_robot']) && !empty($dok['surat_pengantar']) && !empty($dok['ktm_anggota']) && !empty($dok['surat_pernyataan']);
        if ($lengkap) $progress = 75;
    }
    if ($tim['status'] === 'valid') $progress = 100;

    $cek_lap = $pdo->prepare("SELECT id_laporan FROM laporan_teknis WHERE tim_id=?");
    $cek_lap->execute([$tim_id]);
    if ($cek_lap->fetch()) $has_laporan = true;
}

$pengumuman = $pdo->query("SELECT * FROM pengumuman WHERE is_published=1 ORDER BY published_at DESC LIMIT 4")->fetchAll();
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

      <?php if (!$tim): ?>
        <div class="card" style="text-align:center;padding:48px;">
          <h2 style="color:#1A4F8A;margin-bottom:10px;">Selamat datang, <?= e($_SESSION['nama']) ?>!</h2>
          <p style="color:#4a5568;margin-bottom:24px;">Anda belum mendaftarkan tim. Mulai dengan mengisi formulir pendaftaran tim Anda.</p>
          <a href="formulir.php" class="btn btn-primary btn-lg"><i class="fas fa-clipboard-list"></i> Isi Formulir Pendaftaran Tim</a>
        </div>
      <?php else: ?>

        <!-- STAT CARDS -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-clipboard-list"></i></div>
            <div><div class="stat-num" style="font-size:1.2rem;font-weight:700;"><?= e($tim['no_pendaftaran']) ?></div><div class="stat-label">No. Pendaftaran</div></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-robot"></i></div>
            <div><div class="stat-num" style="font-size:18px;"><?= e($tim['singkatan']) ?></div><div class="stat-label">Divisi Lomba</div></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon yellow"><i class="fas fa-location-dot"></i></div>
            <div><div class="stat-num" style="font-size:18px;"><?= e($tim['nama_regional']) ?></div><div class="stat-label">Regional</div></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon <?= $tim['status']==='valid'?'green':($tim['status']==='tidak_valid'?'red':'yellow') ?>">
              <?= $tim['status']==='valid'?'<i class="fas fa-check"></i>':($tim['status']==='tidak_valid'?'<i class="fas fa-times"></i>':'<i class="fas fa-hourglass-half"></i>') ?>
            </div>
            <div><div class="stat-num" style="font-size:18px;"><?= badge_status($tim['status']) ?></div><div class="stat-label">Status Berkas</div></div>
          </div>
        </div>

        <!-- PROGRESS -->
        <div class="card">
          <div class="card-header"><div class="card-title">Progress Pendaftaran</div><span style="color:#4a5568;font-size:13px;"><?= $progress ?>% selesai</span></div>
          <div class="progress-bar-wrap" style="height:12px;margin-bottom:24px;"><div class="progress-bar" style="width:<?= $progress ?>%;"></div></div>
          <div class="steps-bar">
            <div class="step <?= $progress>=25?'done':'active' ?>"><div class="step-num"><?= $progress>=25?'<i class="fas fa-check"></i>':'1' ?></div><div class="step-text">Data Tim</div></div>
            <div class="step <?= $progress>=50?'done':($progress>=25?'active':'') ?>"><div class="step-num"><?= $progress>=50?'<i class="fas fa-check"></i>':'2' ?></div><div class="step-text">Anggota</div></div>
            <div class="step <?= $progress>=75?'done':($progress>=50?'active':'') ?>"><div class="step-num"><?= $progress>=75?'<i class="fas fa-check"></i>':'3' ?></div><div class="step-text">Dokumen</div></div>
            <div class="step <?= $progress>=100?'done':($progress>=75?'active':'') ?>"><div class="step-num"><?= $progress>=100?'<i class="fas fa-check"></i>':'4' ?></div><div class="step-text">Verifikasi</div></div>
          </div>
        </div>

        <div class="d-grid-2">
          <!-- INFO TIM -->
          <div class="card">
            <div class="card-header"><div class="card-title">Profil Tim</div><a href="formulir.php" class="btn btn-sm btn-outline">Edit</a></div>
            <table style="width:100%;">
              <tr><td style="color:#4a5568;padding:8px 0;">Nama Tim</td><td><b><?= e($tim['nama_tim']) ?></b></td></tr>
              <tr><td style="color:#4a5568;padding:8px 0;">Asal PT</td><td><?= e($tim['asal_pt']) ?></td></tr>
              <tr><td style="color:#4a5568;padding:8px 0;">Divisi</td><td><?= e($tim['nama_divisi']) ?></td></tr>
              <tr><td style="color:#4a5568;padding:8px 0;">Regional</td><td><?= e($tim['nama_regional']) ?></td></tr>
              <tr><td style="color:#4a5568;padding:8px 0;">Dosen Pembimbing</td><td><?= e($tim['nama_dosen']) ?></td></tr>
              <tr><td style="color:#4a5568;padding:8px 0;">Hasil Seleksi</td><td><?= badge_status($tim['hasil_seleksi']) ?></td></tr>
            </table>
            <?php if ($tim['catatan_verifikasi']): ?>
              <div class="alert alert-warning mt-3" style="margin-top:16px;"><i class="fas fa-comment"></i> Catatan Panitia: <?= e($tim['catatan_verifikasi']) ?></div>
            <?php endif; ?>
          </div>

          <!-- PENGUMUMAN -->
          <div class="card">
            <div class="card-header"><div class="card-title">Pengumuman Terbaru</div><a href="pengumuman.php" style="color:#1A4F8A;font-size:13px;text-decoration:none;font-weight:600;">Lihat semua →</a></div>
            <?php if (!$pengumuman): ?>
              <p style="color:#718096;font-style:italic;">Belum ada pengumuman.</p>
            <?php else: foreach ($pengumuman as $p): ?>
              <div style="padding:14px 0;border-bottom:1px solid #f0f4f8;">
                <span class="badge bg-info" style="margin-bottom:6px;display:inline-block;"><?= strtoupper($p['tahap']) ?></span>
                <div style="font-weight:700;color:#1a1e2e;margin-bottom:4px;font-size:14px;"><?= e($p['judul']) ?></div>
                <div style="color:#4a5568;font-size:13px;line-height:1.6;"><?= e(mb_substr($p['konten'],0,100)) ?>…</div>
                <div style="color:#718096;font-size:12px;margin-top:4px;"><i class="fas fa-calendar"></i> <?= tgl_id($p['published_at']) ?></div>
              </div>
            <?php endforeach; endif; ?>
          </div>
        </div>

        <!-- QUICK ACTIONS -->
        <div class="card">
          <div class="card-title" style="margin-bottom:16px;">Aksi Cepat</div>
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;">
            <a href="dokumen.php" class="btn btn-outline" style="justify-content:center;padding:18px;flex-direction:column;height:auto;"><i class="fas fa-folder-open"></i><span>Upload Dokumen</span></a>
            <?php if ($tim['hasil_seleksi'] === 'lolos'): ?>
              <a href="laporan.php" class="btn btn-secondary" style="justify-content:center;padding:18px;flex-direction:column;height:auto;"><i class="fas fa-chart-bar"></i><span>Submit Laporan Teknis</span></a>
            <?php endif; ?>
            <?php if ($tim['status'] === 'valid'): ?>
              <a href="bukti.php" target="_blank" class="btn btn-primary" style="justify-content:center;padding:18px;flex-direction:column;height:auto;"><i class="fas fa-print"></i><span>Cetak Bukti Pendaftaran</span></a>
            <?php endif; ?>
            <a href="pengumuman.php" class="btn btn-outline" style="justify-content:center;padding:18px;flex-direction:column;height:auto;"><i class="fas fa-bullhorn"></i><span>Lihat Pengumuman</span></a>
          </div>
        </div>

      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
