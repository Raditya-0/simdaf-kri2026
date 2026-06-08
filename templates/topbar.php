<?php
$topbar_title = $topbar_title ?? 'Dashboard';
?>
<header class="topbar">
  <div class="topbar-title"><?= e($topbar_title) ?></div>
  <div class="topbar-right">
    <span style="font-size:13px;color:#718096;"><i class="fas fa-hand"></i> Halo, <b style="color:#1A4F8A;"><?= e($_SESSION['nama']) ?></b></span>
  </div>
</header>
