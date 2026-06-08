<?php
// Sidebar untuk role Ketua Tim
$ketua_active = $ketua_active ?? '';
?>
<aside class="sidebar">
  <a href="<?= BASE_URL ?>/index.php" class="sidebar-brand">
    <div class="sidebar-brand-name">SIMDAF KRI</div>
    <div class="sidebar-brand-sub">2026</div>
  </a>
  <div class="sidebar-user">
    <div class="sidebar-avatar"><?= strtoupper(substr($_SESSION['nama'] ?? 'U', 0, 1)) ?></div>
    <div>
      <div class="sidebar-user-name"><?= e($_SESSION['nama'] ?? '') ?></div>
      <div class="sidebar-user-role">Ketua Tim</div>
    </div>
  </div>
  <nav class="sidebar-nav">
    <div class="sidebar-label">Menu Utama</div>
    <a href="<?= BASE_URL ?>/ketua_tim/dashboard.php" class="<?= $ketua_active==='dashboard'?'active':'' ?>"><i class="fas fa-table-cells"></i> Dashboard</a>
    <a href="<?= BASE_URL ?>/ketua_tim/formulir.php"  class="<?= $ketua_active==='formulir'?'active':'' ?>"><i class="fas fa-clipboard-list"></i> Formulir Pendaftaran</a>
    <a href="<?= BASE_URL ?>/ketua_tim/dokumen.php"   class="<?= $ketua_active==='dokumen'?'active':'' ?>"><i class="fas fa-folder-open"></i> Upload Dokumen</a>
    <a href="<?= BASE_URL ?>/ketua_tim/laporan.php"   class="<?= $ketua_active==='laporan'?'active':'' ?>"><i class="fas fa-chart-bar"></i> Laporan Teknis</a>
    <div class="sidebar-label">Informasi</div>
    <a href="<?= BASE_URL ?>/ketua_tim/pengumuman.php" class="<?= $ketua_active==='pengumuman'?'active':'' ?>"><i class="fas fa-bullhorn"></i> Pengumuman</a>
    <a href="<?= BASE_URL ?>/ketua_tim/bukti.php"      class="<?= $ketua_active==='bukti'?'active':'' ?>"><i class="fas fa-print"></i> Cetak Bukti</a>
  </nav>
  <div class="sidebar-footer">
    <a href="<?= BASE_URL ?>/logout.php"><i class="fas fa-right-from-bracket"></i> Logout</a>
  </div>
</aside>
