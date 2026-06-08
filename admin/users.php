<?php
session_start();
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../config/config.php';
require_admin();

$admin_active = 'users';
$topbar_title = 'Manajemen User';

$form_err = null;
$old      = ['nama' => '', 'email' => '', 'no_hp' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah_admin') {
        $nama  = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $hp    = trim($_POST['no_hp'] ?? '');
        $old   = ['nama' => $nama, 'email' => $email, 'no_hp' => $hp];

        // validasi input dasar
        if ($nama === '' || $email === '' || $pass === '') {
            $form_err = 'Nama, email, dan password wajib diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $form_err = 'Format email tidak valid.';
        } elseif (strlen($pass) < 8) {
            $form_err = 'Password minimal 8 karakter.';
        } else {
            // cek email unik
            $cek = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $cek->execute([$email]);
            if ($cek->fetch()) {
                $form_err = 'Email sudah terdaftar.';
            } else {
                // hash password baru
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $pdo->prepare("INSERT INTO users (nama, email, password, role, no_hp) VALUES (?, ?, ?, 'admin', ?)")
                    ->execute([$nama, $email, $hash, $hp]);
                redirect('users.php?msg=ok');
            }
        }

    } elseif ($aksi === 'delete') {
        $id = (int)($_POST['id'] ?? 0);

        // cegah hapus diri
        if ($id === (int)($_SESSION['user_id'] ?? 0)) {
            redirect('users.php?err=self');
        }

        // ambil data target
        $cari = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $cari->execute([$id]);
        $target = $cari->fetch();

        // hanya ketua tim
        if (!$target || $target['role'] !== 'ketua_tim') {
            redirect('users.php?err=invalid');
        }

        // hapus berurutan transaksi
        $pdo->beginTransaction();
        try {
            $tims = $pdo->prepare("SELECT id_tim FROM tim WHERE user_id = ?");
            $tims->execute([$id]);
            $tim_ids = array_column($tims->fetchAll(), 'id_tim');

            if ($tim_ids) {
                $in = implode(',', array_fill(0, count($tim_ids), '?'));
                // hapus dokumen terkait
                $pdo->prepare("DELETE FROM dokumen WHERE tim_id IN ($in)")->execute($tim_ids);
                $pdo->prepare("DELETE FROM laporan_teknis WHERE tim_id IN ($in)")->execute($tim_ids);
                $pdo->prepare("DELETE FROM anggota WHERE tim_id IN ($in)")->execute($tim_ids);
                $pdo->prepare("DELETE FROM tim WHERE user_id = ?")->execute([$id]);
            }
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);

            // commit jika sukses
            $pdo->commit();
            redirect('users.php?msg=deleted');
        } catch (Throwable $e) {
            // rollback jika gagal
            $pdo->rollBack();
            $err = 'Gagal menghapus user.';
        }
    }
}

// filter berdasarkan role
$f_role = $_GET['role'] ?? '';
if (!in_array($f_role, ['admin', 'ketua_tim'], true)) $f_role = '';
$q = trim($_GET['q'] ?? '');

$sql    = "SELECT * FROM users WHERE 1=1";
$params = [];
if ($f_role !== '') { $sql .= " AND role = ?"; $params[] = $f_role; }
if ($q !== '') { $sql .= " AND (nama LIKE ? OR email LIKE ?)"; $params[] = "%$q%"; $params[] = "%$q%"; }
$sql .= " ORDER BY created_at DESC";

// ambil daftar user
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
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
      <?php if (($_GET['msg']??'')==='deleted'): ?><div class="alert alert-success"><i class="fas fa-check"></i> User berhasil dihapus.</div><?php endif; ?>
      <?php if (($_GET['err']??'')==='self'): ?><div class="alert alert-danger"><i class="fas fa-times"></i> Tidak bisa menghapus akun sendiri.</div><?php endif; ?>
      <?php if (($_GET['err']??'')==='invalid'): ?><div class="alert alert-danger"><i class="fas fa-times"></i> User tidak ditemukan atau tidak bisa dihapus lewat sini.</div><?php endif; ?>
      <?php if (isset($err)): ?><div class="alert alert-danger"><i class="fas fa-times"></i> <?= e($err) ?></div><?php endif; ?>

      <div class="d-grid-2">
        <div class="card">
          <div class="card-header"><div class="card-title">Tambah Admin Baru</div></div>
          <?php if ($form_err): ?><div class="alert alert-danger"><i class="fas fa-times"></i> <?= e($form_err) ?></div><?php endif; ?>
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi" value="tambah_admin">
            <div class="form-group"><label>Nama <span class="required">*</span></label>
              <input type="text" name="nama" required value="<?= e($old['nama']) ?>"></div>
            <div class="form-group"><label>Email <span class="required">*</span></label>
              <input type="email" name="email" required value="<?= e($old['email']) ?>"></div>
            <div class="form-group"><label>Password <span class="required">*</span></label>
              <input type="password" name="password" required minlength="8">
              <small style="color:#718096;">Minimal 8 karakter.</small></div>
            <div class="form-group"><label>No. HP</label>
              <input type="text" name="no_hp" value="<?= e($old['no_hp']) ?>"></div>
            <button class="btn btn-primary"><i class="fas fa-user-plus"></i> Tambah Admin</button>
          </form>
        </div>

        <div class="card">
          <div class="card-header"><div class="card-title">Daftar User (<?= count($users) ?>)</div></div>

          <form method="get" style="display:grid;grid-template-columns:1fr 160px auto;gap:12px;margin-bottom:18px;">
            <input type="text" name="q" placeholder="Cari nama atau email..." value="<?= e($q) ?>">
            <select name="role">
              <option value="">Semua Role</option>
              <option value="admin"     <?= $f_role==='admin'?'selected':'' ?>>Admin</option>
              <option value="ketua_tim" <?= $f_role==='ketua_tim'?'selected':'' ?>>Ketua Tim</option>
            </select>
            <button class="btn btn-primary">Filter</button>
          </form>

          <table>
            <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>No. HP</th><th>Daftar</th><th>Aksi</th></tr></thead>
            <tbody>
              <?php if (!$users): ?>
                <tr class="table-empty"><td colspan="6">Tidak ada user yang cocok.</td></tr>
              <?php else: foreach ($users as $u): ?>
                <tr>
                  <td><strong><?= e($u['nama']) ?></strong></td>
                  <td><?= e($u['email']) ?></td>
                  <td><?php if ($u['role']==='admin'): ?>
                        <span class="badge bg-primary">Admin</span>
                      <?php else: ?>
                        <span class="badge bg-info">Ketua Tim</span>
                      <?php endif; ?></td>
                  <td><?= e($u['no_hp'] ?? '-') ?></td>
                  <td><?= tgl_id($u['created_at']) ?></td>
                  <td>
                    <?php if ($u['role']==='ketua_tim'): ?>
                      <form method="post" style="display:inline;" onsubmit="return confirm('Hapus user ini? Data tim, anggota, dan dokumen terkait akan ikut terhapus.');">
                        <?= csrf_field() ?><input type="hidden" name="aksi" value="delete"><input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <button class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
