<?php
session_start();
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../config/config.php';
require_admin();

$admin_active = 'dashboard';
$topbar_title = 'Dashboard Admin';

$total       = $pdo->query("SELECT COUNT(*) FROM tim")->fetchColumn();
$lolos       = $pdo->query("SELECT COUNT(*) FROM tim WHERE hasil_seleksi='lolos'")->fetchColumn();
$pending     = $pdo->query("SELECT COUNT(*) FROM tim WHERE status='pending'")->fetchColumn();
$tidak_lolos = $pdo->query("SELECT COUNT(*) FROM tim WHERE hasil_seleksi='tidak_lolos' OR status='tidak_valid'")->fetchColumn();

$per_divisi  = $pdo->query("SELECT d.singkatan, COUNT(t.id_tim) AS jml
                            FROM divisi d LEFT JOIN tim t ON t.divisi_id=d.id_divisi
                            GROUP BY d.id_divisi ORDER BY d.id_divisi")->fetchAll();

$per_regional= $pdo->query("SELECT r.nama_regional, COUNT(t.id_tim) AS jml
                            FROM regional r LEFT JOIN tim t ON t.regional_id=r.id_regional
                            GROUP BY r.id_regional ORDER BY r.id_regional")->fetchAll();

$recent = $pdo->query("SELECT t.*, d.singkatan FROM tim t LEFT JOIN divisi d ON t.divisi_id=d.id_divisi ORDER BY t.created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?= $topbar_title ?> — SIMDAF KRI 2026</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/icons/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="dash-wrapper">
  <?php include __DIR__ . '/../templates/sidebar-admin.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../templates/topbar.php'; ?>
    <div class="page-body">

      <div class="stats-grid">
        <div class="stat-card"><div class="stat-icon blue">∑</div><div><div class="stat-num"><?= $total ?></div><div class="stat-label">Total Tim Pendaftar</div></div></div>
        <div class="stat-card"><div class="stat-icon green"><i class="fas fa-check"></i></div><div><div class="stat-num"><?= $lolos ?></div><div class="stat-label">Lolos Seleksi</div></div></div>
        <div class="stat-card"><div class="stat-icon yellow"><i class="fas fa-hourglass-half"></i></div><div><div class="stat-num"><?= $pending ?></div><div class="stat-label">Pending Verifikasi</div></div></div>
        <div class="stat-card"><div class="stat-icon red"><i class="fas fa-times"></i></div><div><div class="stat-num"><?= $tidak_lolos ?></div><div class="stat-label">Tidak Lolos / Tidak Valid</div></div></div>
      </div>

      <div class="d-grid-2">
        <div class="card">
          <div class="card-header"><div class="card-title">Pendaftar per Divisi</div></div>
          <canvas id="chart-divisi" height="220"></canvas>
        </div>
        <div class="card">
          <div class="card-header"><div class="card-title">Pendaftar per Regional</div></div>
          <canvas id="chart-regional" height="220"></canvas>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="card-title">5 Tim Pendaftar Terbaru</div>
          <a href="tim.php" style="color:#1A4F8A;font-size:13px;text-decoration:none;font-weight:600;">Lihat semua →</a>
        </div>
        <table>
          <thead><tr><th>No. Pendaftaran</th><th>Nama Tim</th><th>Asal PT</th><th>Divisi</th><th>Status</th><th>Hasil</th><th>Tanggal</th></tr></thead>
          <tbody>
            <?php if (!$recent): ?>
              <tr class="table-empty"><td colspan="7">Belum ada tim terdaftar.</td></tr>
            <?php else: foreach ($recent as $r): ?>
              <tr>
                <td><strong><?= e($r['no_pendaftaran']) ?></strong></td>
                <td><?= e($r['nama_tim']) ?></td>
                <td><?= e($r['asal_pt']) ?></td>
                <td><span class="badge bg-primary"><?= e($r['singkatan']) ?></span></td>
                <td><?= badge_status($r['status']) ?></td>
                <td><?= badge_status($r['hasil_seleksi']) ?></td>
                <td><?= tgl_id($r['created_at']) ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>

<script>
new Chart(document.getElementById('chart-divisi'), {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_column($per_divisi,'singkatan')) ?>,
    datasets: [{ label: 'Jumlah Tim', data: <?= json_encode(array_map('intval', array_column($per_divisi,'jml'))) ?>, backgroundColor: '#1A4F8A', borderRadius: 6 }]
  },
  options: { responsive:true, plugins:{ legend:{display:false} }, scales:{ y:{ beginAtZero:true, ticks:{ precision:0 } } } }
});
new Chart(document.getElementById('chart-regional'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode(array_column($per_regional,'nama_regional')) ?>,
    datasets: [{ data: <?= json_encode(array_map('intval', array_column($per_regional,'jml'))) ?>,
                 backgroundColor: ['#1A4F8A','#2E75B6','#5FA8D3','#a8cce8','#d6e4f4'] }]
  },
  options: { responsive:true, plugins:{ legend:{ position:'bottom' } } }
});
</script>
</body>
</html>
