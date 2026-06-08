<?php
session_start();
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../config/config.php';
require_admin();

// ekspor excel
if (($_GET['export'] ?? '') === 'excel') {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="data_tim_KRI2026_' . date('Ymd_His') . '.xls"');
    echo "\xEF\xBB\xBF"; // BOM UTF-8 supaya Excel baca karakter
    echo "<table border='1'>";
    echo "<tr><th>No</th><th>No. Pendaftaran</th><th>Nama Tim</th><th>Asal PT</th><th>Divisi</th><th>Regional</th><th>Dosen</th><th>Status</th><th>Hasil Tahap 1</th><th>Hasil Tahap 2</th><th>Tanggal Daftar</th></tr>";
    $rows = $pdo->query("SELECT t.*, d.nama_divisi, d.singkatan, r.nama_regional, l.hasil_tahap2
                         FROM tim t LEFT JOIN divisi d ON t.divisi_id=d.id_divisi
                         LEFT JOIN regional r ON t.regional_id=r.id_regional
                         LEFT JOIN laporan_teknis l ON l.tim_id=t.id_tim
                         ORDER BY t.created_at DESC")->fetchAll();
    foreach ($rows as $i => $r) {
        echo "<tr>";
        echo "<td>" . ($i+1) . "</td>";
        echo "<td>" . e($r['no_pendaftaran']) . "</td>";
        echo "<td>" . e($r['nama_tim']) . "</td>";
        echo "<td>" . e($r['asal_pt']) . "</td>";
        echo "<td>" . e($r['singkatan']) . " - " . e($r['nama_divisi']) . "</td>";
        echo "<td>" . e($r['nama_regional']) . "</td>";
        echo "<td>" . e($r['nama_dosen']) . "</td>";
        echo "<td>" . e($r['status']) . "</td>";
        echo "<td>" . e($r['hasil_seleksi']) . "</td>";
        echo "<td>" . e($r['hasil_tahap2'] ?? '-') . "</td>";
        echo "<td>" . tgl_id($r['created_at']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    exit;
}

$admin_active = 'laporan';
$topbar_title = 'Laporan & Ekspor Data';

$tot          = $pdo->query("SELECT COUNT(*) FROM tim")->fetchColumn();
$tot_anggota  = $pdo->query("SELECT COUNT(*) FROM anggota")->fetchColumn();
$tot_pt       = $pdo->query("SELECT COUNT(DISTINCT asal_pt) FROM tim")->fetchColumn();
$tot_dok      = $pdo->query("SELECT COUNT(*) FROM dokumen")->fetchColumn();
$tot_lap      = $pdo->query("SELECT COUNT(*) FROM laporan_teknis")->fetchColumn();

$per_status   = $pdo->query("SELECT status, COUNT(*) AS jml FROM tim GROUP BY status")->fetchAll();
$per_hasil    = $pdo->query("SELECT hasil_seleksi, COUNT(*) AS jml FROM tim GROUP BY hasil_seleksi")->fetchAll();
$per_divisi   = $pdo->query("SELECT d.nama_divisi, d.singkatan, COUNT(t.id_tim) AS jml
                              FROM divisi d LEFT JOIN tim t ON t.divisi_id=d.id_divisi
                              GROUP BY d.id_divisi ORDER BY jml DESC")->fetchAll();
$per_regional = $pdo->query("SELECT r.nama_regional, COUNT(t.id_tim) AS jml
                              FROM regional r LEFT JOIN tim t ON t.regional_id=r.id_regional
                              GROUP BY r.id_regional ORDER BY r.id_regional")->fetchAll();
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

      <div class="card">
        <div class="card-header"><div class="card-title">Ekspor Data</div></div>
        <p style="color:#4a5568;margin-bottom:16px;">Unduh seluruh data tim peserta KRI 2026 dalam format Excel atau cetak PDF rekapitulasi.</p>
        <div style="display:flex;gap:12px;">
          <a href="?export=excel" class="btn btn-success btn-lg"><i class="fas fa-chart-bar"></i> Export Excel (.xls)</a>
          <button onclick="window.print()" class="btn btn-primary btn-lg"><i class="fas fa-print"></i> Cetak Rekapitulasi (PDF)</button>
        </div>
      </div>

      <div class="stats-grid">
        <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-users"></i></div><div><div class="stat-num"><?= $tot ?></div><div class="stat-label">Total Tim</div></div></div>
        <div class="stat-card"><div class="stat-icon green"><i class="fas fa-user"></i></div><div><div class="stat-num"><?= $tot_anggota ?></div><div class="stat-label">Total Anggota</div></div></div>
        <div class="stat-card"><div class="stat-icon yellow"><i class="fas fa-school"></i></div><div><div class="stat-num"><?= $tot_pt ?></div><div class="stat-label">Perguruan Tinggi</div></div></div>
        <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-folder-open"></i></div><div><div class="stat-num"><?= $tot_dok ?></div><div class="stat-label">Dokumen Diunggah</div></div></div>
        <div class="stat-card"><div class="stat-icon green"><i class="fas fa-chart-bar"></i></div><div><div class="stat-num"><?= $tot_lap ?></div><div class="stat-label">Laporan Teknis</div></div></div>
      </div>

      <div class="d-grid-2">
        <div class="card">
          <div class="card-header"><div class="card-title">Rekap per Divisi</div></div>
          <table>
            <thead><tr><th>Divisi</th><th>Singkatan</th><th>Jumlah Tim</th></tr></thead>
            <tbody>
              <?php foreach ($per_divisi as $d): ?>
                <tr><td><?= e($d['nama_divisi']) ?></td><td><span class="badge bg-primary"><?= e($d['singkatan']) ?></span></td><td><strong><?= (int)$d['jml'] ?></strong></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="card">
          <div class="card-header"><div class="card-title">Rekap per Regional</div></div>
          <table>
            <thead><tr><th>Regional</th><th>Jumlah Tim</th></tr></thead>
            <tbody>
              <?php foreach ($per_regional as $r): ?>
                <tr><td><?= e($r['nama_regional']) ?></td><td><strong><?= (int)$r['jml'] ?></strong></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="d-grid-2">
        <div class="card">
          <div class="card-header"><div class="card-title">Status Verifikasi Berkas</div></div>
          <table>
            <thead><tr><th>Status</th><th>Jumlah</th></tr></thead>
            <tbody>
              <?php foreach ($per_status as $s): ?>
                <tr><td><?= badge_status($s['status']) ?></td><td><strong><?= (int)$s['jml'] ?></strong></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="card">
          <div class="card-header"><div class="card-title">Hasil Seleksi Tahap I</div></div>
          <table>
            <thead><tr><th>Hasil</th><th>Jumlah</th></tr></thead>
            <tbody>
              <?php foreach ($per_hasil as $h): ?>
                <tr><td><?= badge_status($h['hasil_seleksi']) ?></td><td><strong><?= (int)$h['jml'] ?></strong></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>
</body>
</html>
