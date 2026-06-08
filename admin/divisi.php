<?php
session_start();
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../config/config.php';
require_admin();

$admin_active = 'divisi';
$topbar_title = 'Manajemen Divisi Lomba';

if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_verify()) {
    $aksi = $_POST['aksi'] ?? '';
    if ($aksi==='create' || $aksi==='update') {
        $nama   = trim($_POST['nama_divisi'] ?? '');
        $sing   = trim($_POST['singkatan'] ?? '');
        $desk   = trim($_POST['deskripsi'] ?? '');
        $kuota  = (int)($_POST['kuota_nasional'] ?? 0);
        if ($aksi==='create') {
            $pdo->prepare("INSERT INTO divisi (nama_divisi,singkatan,deskripsi,kuota_nasional) VALUES (?,?,?,?)")
                ->execute([$nama,$sing,$desk,$kuota]);
        } else {
            $id = (int)$_POST['id_divisi'];
            $pdo->prepare("UPDATE divisi SET nama_divisi=?,singkatan=?,deskripsi=?,kuota_nasional=? WHERE id_divisi=?")
                ->execute([$nama,$sing,$desk,$kuota,$id]);
        }
    } elseif ($aksi==='delete') {
        try { $pdo->prepare("DELETE FROM divisi WHERE id_divisi=?")->execute([(int)$_POST['id_divisi']]); }
        catch (Throwable $e) { redirect('divisi.php?err=in_use'); }
    }
    redirect('divisi.php?msg=ok');
}

$edit_id = (int)($_GET['edit'] ?? 0);
$edit    = $edit_id ? $pdo->query("SELECT * FROM divisi WHERE id_divisi=$edit_id")->fetch() : null;

$list = $pdo->query("SELECT d.*, (SELECT COUNT(*) FROM tim WHERE divisi_id=d.id_divisi) AS jum_tim FROM divisi d ORDER BY d.id_divisi")->fetchAll();
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
      <?php if (($_GET['err']??'')==='in_use'): ?><div class="alert alert-danger"><i class="fas fa-times"></i> Divisi tidak bisa dihapus karena masih dipakai oleh tim.</div><?php endif; ?>

      <div class="d-grid-2">
        <div class="card">
          <div class="card-header"><div class="card-title"><?= $edit?'Edit Divisi':'Tambah Divisi Baru' ?></div></div>
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi" value="<?= $edit?'update':'create' ?>">
            <?php if ($edit): ?><input type="hidden" name="id_divisi" value="<?= $edit['id_divisi'] ?>"><?php endif; ?>
            <div class="form-group"><label>Nama Divisi <span class="required">*</span></label>
              <input type="text" name="nama_divisi" required value="<?= e($edit['nama_divisi'] ?? '') ?>"></div>
            <div class="form-group"><label>Singkatan <span class="required">*</span></label>
              <input type="text" name="singkatan" required maxlength="30" value="<?= e($edit['singkatan'] ?? '') ?>"></div>
            <div class="form-group"><label>Deskripsi</label>
              <textarea name="deskripsi" rows="3"><?= e($edit['deskripsi'] ?? '') ?></textarea></div>
            <div class="form-group"><label>Kuota Nasional</label>
              <input type="number" name="kuota_nasional" min="0" value="<?= (int)($edit['kuota_nasional'] ?? 0) ?>"></div>
            <div style="display:flex;gap:10px;">
              <button class="btn btn-primary"><?= $edit?'<i class="fas fa-floppy-disk"></i> Update':'+ Tambah' ?></button>
              <?php if ($edit): ?><a href="divisi.php" class="btn btn-outline">Batal</a><?php endif; ?>
            </div>
          </form>
        </div>

        <div class="card">
          <div class="card-header"><div class="card-title">Daftar Divisi (<?= count($list) ?>)</div></div>
          <table>
            <thead><tr><th>Singkatan</th><th>Nama</th><th>Kuota</th><th>Tim</th><th>Aksi</th></tr></thead>
            <tbody>
              <?php foreach ($list as $d): ?>
                <tr>
                  <td><span class="badge bg-primary"><?= e($d['singkatan']) ?></span></td>
                  <td><strong><?= e($d['nama_divisi']) ?></strong></td>
                  <td><?= (int)$d['kuota_nasional'] ?></td>
                  <td><?= (int)$d['jum_tim'] ?></td>
                  <td>
                    <a href="?edit=<?= $d['id_divisi'] ?>" class="btn btn-sm btn-outline">Edit</a>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Hapus divisi ini?');">
                      <?= csrf_field() ?><input type="hidden" name="aksi" value="delete"><input type="hidden" name="id_divisi" value="<?= $d['id_divisi'] ?>">
                      <button class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button>
                    </form>
                  </td>
                </tr>
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
