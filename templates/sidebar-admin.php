<?php
// Sidebar untuk role Admin / Panitia
$admin_active = $admin_active ?? '';
?>
<aside class="sidebar">
  <a href="<?= BASE_URL ?>/index.php" class="sidebar-brand">
    <div class="sidebar-brand-name">SIMDAF KRI</div>
    <div class="sidebar-brand-sub">PANITIA BPTI</div>
  </a>
  <div class="sidebar-user">
    <div class="sidebar-avatar" style="background:#10B981;"><?= strtoupper(substr($_SESSION['nama'] ?? 'A', 0, 1)) ?></div>
    <div>
      <div class="sidebar-user-name"><?= e($_SESSION['nama'] ?? '') ?></div>
      <div class="sidebar-user-role">Administrator</div>
    </div>
  </div>
  <nav class="sidebar-nav">
    <div class="sidebar-label">Dashboard</div>
    <a href="<?= BASE_URL ?>/admin/dashboard.php" class="<?= $admin_active==='dashboard'?'active':'' ?>"><i class="fas fa-table-cells"></i> Ringkasan</a>
    <div class="sidebar-label">Manajemen</div>
    <a href="<?= BASE_URL ?>/admin/tim.php"        class="<?= $admin_active==='tim'?'active':'' ?>"><i class="fas fa-users"></i> Data Tim</a>
    <a href="<?= BASE_URL ?>/admin/verifikasi.php" class="<?= $admin_active==='verifikasi'?'active':'' ?>"><i class="fas fa-check"></i> Verifikasi Berkas</a>
    <a href="<?= BASE_URL ?>/admin/seleksi.php"    class="<?= $admin_active==='seleksi'?'active':'' ?>"><i class="fas fa-trophy"></i> Seleksi Tim</a>
    <div class="sidebar-label">Master</div>
    <a href="<?= BASE_URL ?>/admin/divisi.php"     class="<?= $admin_active==='divisi'?'active':'' ?>"><i class="fas fa-robot"></i> Divisi Lomba</a>
    <a href="<?= BASE_URL ?>/admin/users.php"      class="<?= $admin_active==='users'?'active':'' ?>"><i class="fas fa-users"></i> Manajemen User</a>
    <a href="<?= BASE_URL ?>/admin/pengumuman.php" class="<?= $admin_active==='pengumuman'?'active':'' ?>"><i class="fas fa-bullhorn"></i> Pengumuman</a>
    <div class="sidebar-label">Laporan</div>
    <a href="<?= BASE_URL ?>/admin/laporan.php"    class="<?= $admin_active==='laporan'?'active':'' ?>"><i class="fas fa-chart-bar"></i> Laporan & Ekspor</a>
  </nav>
  <div class="sidebar-footer">
    <a href="<?= BASE_URL ?>/logout.php"><i class="fas fa-right-from-bracket"></i> Logout</a>
  </div>
</aside>
