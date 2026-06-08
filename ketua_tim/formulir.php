<?php
session_start();
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../config/config.php';
require_ketua();

$ketua_active = 'formulir';
$topbar_title = 'Formulir Pendaftaran Tim';

$divisi   = $pdo->query("SELECT * FROM divisi ORDER BY id_divisi")->fetchAll();
$regional = $pdo->query("SELECT * FROM regional ORDER BY id_regional")->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM tim WHERE user_id=? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$tim = $stmt->fetch();

$anggota = [];
if ($tim) {
    $stmt = $pdo->prepare("SELECT * FROM anggota WHERE tim_id=? ORDER BY id_anggota");
    $stmt->execute([$tim['id_tim']]);
    $anggota = $stmt->fetchAll();
}

$msg = ''; $err = '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!csrf_verify()) { $err = 'Token tidak valid.'; }
    else {
        $nama_tim    = trim($_POST['nama_tim'] ?? '');
        $asal_pt     = trim($_POST['asal_pt'] ?? '');
        $divisi_id   = (int)($_POST['divisi_id'] ?? 0);
        $regional_id = (int)($_POST['regional_id'] ?? 0);
        $tahun       = trim($_POST['tahun_angkatan'] ?? '');
        $nama_dosen  = trim($_POST['nama_dosen'] ?? '');
        $nidn_dosen  = trim($_POST['nidn_dosen'] ?? '');
        $email_dosen = trim($_POST['email_dosen'] ?? '');
        $hp_dosen    = trim($_POST['hp_dosen'] ?? '');

        $anggota_nama  = $_POST['anggota_nama']  ?? [];
        $anggota_nim   = $_POST['anggota_nim']   ?? [];
        $anggota_prodi = $_POST['anggota_prodi'] ?? [];
        $anggota_hp    = $_POST['anggota_hp']    ?? [];

        $valid_anggota = 0;
        foreach ($anggota_nama as $i => $n) {
            if (trim($n)!=='' && trim($anggota_nim[$i]??'')!=='') $valid_anggota++;
        }

        if (!$nama_tim || !$asal_pt || !$divisi_id || !$regional_id) $err = 'Data tim wajib lengkap.';
        elseif ($valid_anggota < 3) $err = 'Minimal 3 anggota tim wajib diisi (maks 5).';
        elseif ($valid_anggota > 5) $err = 'Maksimal 5 anggota tim.';
        else {
            try {
                $pdo->beginTransaction();
                if ($tim) {
                    $stmt = $pdo->prepare("UPDATE tim SET nama_tim=?,asal_pt=?,divisi_id=?,regional_id=?,tahun_angkatan=?,nama_dosen=?,nidn_dosen=?,email_dosen=?,hp_dosen=? WHERE id_tim=?");
                    $stmt->execute([$nama_tim,$asal_pt,$divisi_id,$regional_id,$tahun,$nama_dosen,$nidn_dosen,$email_dosen,$hp_dosen,$tim['id_tim']]);
                    $tim_id = $tim['id_tim'];
                    $pdo->prepare("DELETE FROM anggota WHERE tim_id=?")->execute([$tim_id]);
                } else {
                    $no_daftar = 'KRI2026-' . str_pad((string)(rand(1,99999)), 5, '0', STR_PAD_LEFT);
                    $stmt = $pdo->prepare("INSERT INTO tim (no_pendaftaran,user_id,nama_tim,asal_pt,divisi_id,regional_id,tahun_angkatan,nama_dosen,nidn_dosen,email_dosen,hp_dosen) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                    $stmt->execute([$no_daftar,$_SESSION['user_id'],$nama_tim,$asal_pt,$divisi_id,$regional_id,$tahun,$nama_dosen,$nidn_dosen,$email_dosen,$hp_dosen]);
                    $tim_id = $pdo->lastInsertId();
                }

                $stmt = $pdo->prepare("INSERT INTO anggota (tim_id,nama_lengkap,nim,prodi,no_hp) VALUES (?,?,?,?,?)");
                foreach ($anggota_nama as $i => $n) {
                    if (trim($n)==='' || trim($anggota_nim[$i]??'')==='') continue;
                    $stmt->execute([$tim_id, trim($n), trim($anggota_nim[$i]), trim($anggota_prodi[$i]??''), trim($anggota_hp[$i]??'')]);
                }

                $pdo->commit();
                redirect('formulir.php?msg=saved');
            } catch (Throwable $e) {
                $pdo->rollBack();
                $err = 'Gagal menyimpan: ' . $e->getMessage();
            }
        }
    }
}

if (($_GET['msg'] ?? '') === 'saved') $msg = 'Formulir berhasil disimpan!';
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
      <?php if ($msg): ?><div class="alert alert-success"><i class="fas fa-check"></i> <?= e($msg) ?></div><?php endif; ?>
      <?php if ($err): ?><div class="alert alert-danger"><i class="fas fa-times"></i> <?= e($err) ?></div><?php endif; ?>

      <form method="post" id="formulir-tim">
        <?= csrf_field() ?>

        <!-- DATA TIM -->
        <div class="card">
          <div class="card-header"><div class="card-title">1. Data Tim</div></div>
          <div class="d-grid-2">
            <div class="form-group"><label>Nama Tim <span class="required">*</span></label>
              <input type="text" name="nama_tim" required maxlength="100" value="<?= e($tim['nama_tim'] ?? '') ?>"></div>
            <div class="form-group"><label>Tahun Angkatan</label>
              <input type="text" name="tahun_angkatan" maxlength="10" value="<?= e($tim['tahun_angkatan'] ?? '') ?>" placeholder="2021"></div>
          </div>
          <div class="form-group"><label>Asal Perguruan Tinggi <span class="required">*</span></label>
            <input type="text" name="asal_pt" required maxlength="150" value="<?= e($tim['asal_pt'] ?? $_SESSION['nama_pt'] ?? '') ?>"></div>
          <div class="d-grid-2">
            <div class="form-group"><label>Divisi Lomba <span class="required">*</span></label>
              <select name="divisi_id" required>
                <option value="">-- Pilih Divisi --</option>
                <?php foreach ($divisi as $d): ?>
                  <option value="<?= $d['id_divisi'] ?>" <?= (($tim['divisi_id']??0)==$d['id_divisi'])?'selected':'' ?>>
                    <?= e($d['singkatan']) ?> — <?= e($d['nama_divisi']) ?>
                  </option>
                <?php endforeach; ?>
              </select></div>
            <div class="form-group"><label>Regional <span class="required">*</span></label>
              <select name="regional_id" required>
                <option value="">-- Pilih Regional --</option>
                <?php foreach ($regional as $r): ?>
                  <option value="<?= $r['id_regional'] ?>" <?= (($tim['regional_id']??0)==$r['id_regional'])?'selected':'' ?>>
                    <?= e($r['nama_regional']) ?> — <?= e($r['kota_penyelenggara']) ?>
                  </option>
                <?php endforeach; ?>
              </select></div>
          </div>
        </div>

        <!-- ANGGOTA -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">2. Data Anggota Tim <span style="color:#718096;font-weight:500;font-size:13px;">(Min 3, Maks 5)</span></div>
            <button type="button" id="add-anggota" class="btn btn-sm btn-outline">+ Tambah</button>
          </div>
          <div id="anggota-list">
            <?php
            $rows = !empty($anggota) ? $anggota : [[],[],[]];
            foreach ($rows as $i => $a): ?>
              <div class="anggota-row" style="padding:16px;background:#F0F4F8;border-radius:10px;margin-bottom:12px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                  <b style="color:#1A4F8A;">Anggota #<?= $i+1 ?></b>
                  <button type="button" class="btn-remove-anggota" style="background:none;border:none;color:#EF4444;cursor:pointer;font-weight:600;"><i class="fas fa-times"></i> Hapus</button>
                </div>
                <div class="d-grid-2">
                  <div class="form-group" style="margin-bottom:10px;"><label>Nama Lengkap</label>
                    <input type="text" name="anggota_nama[]" value="<?= e($a['nama_lengkap'] ?? '') ?>"></div>
                  <div class="form-group" style="margin-bottom:10px;"><label>NIM</label>
                    <input type="text" name="anggota_nim[]" value="<?= e($a['nim'] ?? '') ?>"></div>
                  <div class="form-group" style="margin-bottom:0;"><label>Program Studi</label>
                    <input type="text" name="anggota_prodi[]" value="<?= e($a['prodi'] ?? '') ?>"></div>
                  <div class="form-group" style="margin-bottom:0;"><label>No. HP</label>
                    <input type="tel" name="anggota_hp[]" value="<?= e($a['no_hp'] ?? '') ?>"></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- DOSEN -->
        <div class="card">
          <div class="card-header"><div class="card-title">3. Dosen Pembimbing</div></div>
          <div class="d-grid-2">
            <div class="form-group"><label>Nama Lengkap Dosen</label>
              <input type="text" name="nama_dosen" maxlength="100" value="<?= e($tim['nama_dosen'] ?? '') ?>"></div>
            <div class="form-group"><label>NIDN</label>
              <input type="text" name="nidn_dosen" maxlength="20" value="<?= e($tim['nidn_dosen'] ?? '') ?>"></div>
            <div class="form-group"><label>Email Dosen</label>
              <input type="email" name="email_dosen" value="<?= e($tim['email_dosen'] ?? '') ?>"></div>
            <div class="form-group"><label>No. HP Dosen</label>
              <input type="tel" name="hp_dosen" value="<?= e($tim['hp_dosen'] ?? '') ?>"></div>
          </div>
        </div>

        <div style="display:flex;gap:12px;">
          <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-floppy-disk"></i> Simpan Formulir</button>
          <a href="dashboard.php" class="btn btn-outline btn-lg">Batal</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const list  = document.getElementById('anggota-list');
const addBt = document.getElementById('add-anggota');

function rebuildLabels() {
  list.querySelectorAll('.anggota-row').forEach((row, i) => {
    row.querySelector('b').textContent = 'Anggota #' + (i+1);
  });
}
function attachRemove(btn) {
  btn.addEventListener('click', () => {
    if (list.querySelectorAll('.anggota-row').length <= 1) return;
    btn.closest('.anggota-row').remove();
    rebuildLabels();
  });
}
document.querySelectorAll('.btn-remove-anggota').forEach(attachRemove);

addBt.addEventListener('click', () => {
  const rows = list.querySelectorAll('.anggota-row');
  if (rows.length >= 5) return alert('Maksimal 5 anggota.');
  const tmpl = rows[0].cloneNode(true);
  tmpl.querySelectorAll('input').forEach(i => i.value = '');
  list.appendChild(tmpl);
  attachRemove(tmpl.querySelector('.btn-remove-anggota'));
  rebuildLabels();
});
</script>
</body>
</html>
