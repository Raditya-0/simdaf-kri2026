<?php
session_start();
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../config/config.php';
require_admin();

$admin_active = 'pengumuman';
$topbar_title = 'Pengumuman';

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    $aksi = $_POST['aksi'] ?? '';
    if ($aksi==='create' || $aksi==='update') {
        $judul   = trim($_POST['judul'] ?? '');
        $konten  = trim($_POST['konten'] ?? '');
        $tahap   = $_POST['tahap'] ?? 'umum';
        $publish = !empty($_POST['publish']) ? 1 : 0;
        $pubdate = $publish ? date('Y-m-d H:i:s') : null;
        if ($aksi==='create') {
            $pdo->prepare("INSERT INTO pengumuman (judul,konten,tahap,is_published,published_at,created_by) VALUES (?,?,?,?,?,?)")
                ->execute([$judul,$konten,$tahap,$publish,$pubdate,$_SESSION['user_id']]);
        } else {
            $id = (int)$_POST['id_pengumuman'];
            $pdo->prepare("UPDATE pengumuman SET judul=?,konten=?,tahap=?,is_published=?,published_at=? WHERE id_pengumuman=?")
                ->execute([$judul,$konten,$tahap,$publish,$pubdate,$id]);
        }
    } elseif ($aksi==='delete') {
        $pdo->prepare("DELETE FROM pengumuman WHERE id_pengumuman=?")->execute([(int)$_POST['id_pengumuman']]);
    }
    redirect('pengumuman.php?msg=ok');
}

$edit_id = (int)($_GET['edit'] ?? 0);
$edit    = $edit_id ? $pdo->query("SELECT * FROM pengumuman WHERE id_pengumuman=$edit_id")->fetch() : null;

$list = $pdo->query("SELECT * FROM pengumuman ORDER BY created_at DESC")->fetchAll();
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

      <?php if (($_GET['msg']??'')==='ok'): ?><div class="alert alert-success"><i class="fas fa-check"></i> Berhasil disimpan.</div><?php endif; ?>

      <div class="card">
        <div class="card-header"><div class="card-title"><?= $edit?'Edit Pengumuman':'Buat Pengumuman Baru' ?></div></div>
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="aksi" value="<?= $edit?'update':'create' ?>">
          <?php if ($edit): ?><input type="hidden" name="id_pengumuman" value="<?= $edit['id_pengumuman'] ?>"><?php endif; ?>
          <div class="form-group"><label>Judul <span class="required">*</span></label>
            <input type="text" name="judul" required maxlength="200" value="<?= e($edit['judul'] ?? '') ?>"></div>
          <div class="form-group"><label>Tahap</label>
            <select name="tahap">
              <option value="umum"   <?= ($edit['tahap']??'')==='umum'?'selected':'' ?>>Umum</option>
              <option value="tahap1" <?= ($edit['tahap']??'')==='tahap1'?'selected':'' ?>>Tahap I</option>
              <option value="tahap2" <?= ($edit['tahap']??'')==='tahap2'?'selected':'' ?>>Tahap II</option>
            </select></div>
          <div class="form-group"><label>Konten <span class="required">*</span></label>
            <textarea name="konten" rows="5" required><?= e($edit['konten'] ?? '') ?></textarea></div>
          <div class="form-group">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
              <input type="checkbox" name="publish" value="1" <?= !empty($edit['is_published'])?'checked':'' ?>>
              Publikasikan sekarang (akan tampil di dashboard ketua tim & landing page)
            </label>
          </div>
          <div style="display:flex;gap:10px;">
            <button class="btn btn-primary"><?= $edit?'<i class="fas fa-floppy-disk"></i> Update':'<i class="fas fa-bullhorn"></i> Publikasikan' ?></button>
            <?php if ($edit): ?><a href="pengumuman.php" class="btn btn-outline">Batal Edit</a><?php endif; ?>
          </div>
        </form>
      </div>

      <div class="card">
        <div class="card-header"><div class="card-title">Daftar Pengumuman (<?= count($list) ?>)</div></div>
        <table>
          <thead><tr><th>Judul</th><th>Tahap</th><th>Status</th><th>Dibuat</th><th>Aksi</th></tr></thead>
          <tbody>
            <?php if (!$list): ?>
              <tr class="table-empty"><td colspan="5">Belum ada pengumuman.</td></tr>
            <?php else: foreach ($list as $p): ?>
              <tr>
                <td><strong><?= e($p['judul']) ?></strong><br><small style="color:#718096;"><?= e(mb_substr($p['konten'],0,90)) ?>…</small></td>
                <td><span class="badge bg-info"><?= strtoupper($p['tahap']) ?></span></td>
                <td><?= $p['is_published']?'<span class="badge bg-success">Published</span>':'<span class="badge bg-secondary">Draft</span>' ?></td>
                <td><?= tgl_id($p['created_at']) ?></td>
                <td>
                  <a href="?edit=<?= $p['id_pengumuman'] ?>" class="btn btn-sm btn-outline">Edit</a>
                  <form method="post" style="display:inline;" onsubmit="return confirm('Hapus pengumuman ini?');">
                    <?= csrf_field() ?><input type="hidden" name="aksi" value="delete"><input type="hidden" name="id_pengumuman" value="<?= $p['id_pengumuman'] ?>">
                    <button class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>
</body>
</html>
