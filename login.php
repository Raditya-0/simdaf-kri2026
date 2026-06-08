<?php
session_start();
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/config/config.php';

$error  = '';
$tab    = $_GET['tab'] ?? 'login';
$msg    = $_GET['msg'] ?? '';

// redirect jika login
if (!empty($_SESSION['user_id'])) {
    redirect($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'ketua_tim/dashboard.php');
}

// proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    if (!csrf_verify()) {
        $error = 'Token tidak valid. Refresh halaman.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $pw    = $_POST['password'] ?? '';

        // batas percobaan login
        $_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? ['count'=>0,'last'=>0];
        if ($_SESSION['login_attempts']['count'] >= 5 && (time() - $_SESSION['login_attempts']['last']) < 300) {
            $error = 'Terlalu banyak percobaan gagal. Tunggu 5 menit.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($pw, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['nama']     = $user['nama'];
                $_SESSION['email']    = $user['email'];
                $_SESSION['role']     = $user['role'];
                $_SESSION['last_activity'] = time();
                unset($_SESSION['login_attempts']);

                redirect($user['role'] === 'admin' ? 'admin/dashboard.php' : 'ketua_tim/dashboard.php');
            } else {
                $_SESSION['login_attempts']['count']++;
                $_SESSION['login_attempts']['last'] = time();
                $error = 'Email atau password salah.';
            }
        }
    }
    $tab = 'login';
}

// proses registrasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'register') {
    if (!csrf_verify()) {
        $error = 'Token tidak valid. Refresh halaman.';
    } else {
        $nama    = trim($_POST['nama']    ?? '');
        $email   = trim($_POST['email']   ?? '');
        $pw      = $_POST['password']     ?? '';
        $pw2     = $_POST['password2']    ?? '';
        $hp      = trim($_POST['no_hp']   ?? '');
        $nim     = trim($_POST['nim']     ?? '');
        $pt      = trim($_POST['nama_pt'] ?? '');

        if (!$nama || !$email || !$pw)              $error = 'Field wajib belum lengkap.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Format email tidak valid.';
        elseif (strlen($pw) < 8)                    $error = 'Password minimal 8 karakter.';
        elseif ($pw !== $pw2)                       $error = 'Konfirmasi password tidak sama.';
        elseif (!ctype_digit(str_replace(['/','-'],'',$nim))) $error = 'NIM hanya boleh angka (atau / dan -).';
        else {
            // cek email duplikat
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) $error = 'Email sudah terdaftar. Silakan login.';
            else {
                $hash = password_hash($pw, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (nama,email,password,role,no_hp,nim,nama_pt) VALUES (?,?,?,'ketua_tim',?,?,?)");
                $stmt->execute([$nama,$email,$hash,$hp,$nim,$pt]);
                redirect('login.php?tab=login&msg=register_success');
            }
        }
    }
    $tab = 'register';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login / Daftar — SIMDAF KRI 2026</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/icons/css/all.min.css">
</head>
<body>
<a href="<?= BASE_URL ?>/index.php" style="position:fixed;top:20px;left:28px;color:white;text-decoration:none;font-size:14px;display:flex;align-items:center;gap:6px;opacity:0.85;">
  <i class="fas fa-arrow-left"></i> Beranda
</a>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="logo-circle">
        <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="white" stroke-width="2.5"><rect x="4" y="6" width="16" height="12" rx="2"/><path d="M9 6V4M15 6V4M9 12h.01M15 12h.01M8 16h8"/></svg>
      </div>
      <h1>SIMDAF KRI 2026</h1>
      <p>Sistem Pendaftaran Kontes Robot Indonesia</p>
    </div>


    <?php if ($msg === 'register_success'): ?>
      <div class="alert alert-success"><i class="fas fa-check"></i> Pendaftaran berhasil. Silakan login dengan email & password Anda.</div>
    <?php elseif ($msg === 'login_required'): ?>
      <div class="alert alert-warning">Silakan login terlebih dahulu.</div>
    <?php elseif ($msg === 'session_expired'): ?>
      <div class="alert alert-warning">Sesi habis. Silakan login ulang.</div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-danger"><i class="fas fa-times"></i> <?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($tab === 'login'): ?>
      <form method="post" autocomplete="off">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="login">
        <div class="form-group">
          <label>Email <span class="required">*</span></label>
          <input type="email" name="email" required placeholder="email@kampus.ac.id" value="<?= e($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Password <span class="required">*</span></label>
          <input type="password" name="password" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center;">Masuk</button>
        <p class="text-center mt-3" style="font-size:13px;color:#718096;">
          Belum punya akun? <a href="?tab=register" style="color:#1A4F8A;font-weight:700;">Daftar di sini</a>
        </p>
      </form>
    <?php else: ?>
      <form method="post" autocomplete="off">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="register">
        <div class="form-group">
          <label>Nama Lengkap Ketua Tim <span class="required">*</span></label>
          <input type="text" name="nama" required maxlength="100" value="<?= e($_POST['nama'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Email Aktif <span class="required">*</span></label>
          <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
        </div>
        <div class="d-grid-2">
          <div class="form-group">
            <label>Password <span class="required">*</span></label>
            <input type="password" name="password" required minlength="8">
            <p class="form-hint">Min. 8 karakter</p>
          </div>
          <div class="form-group">
            <label>Konfirmasi Password <span class="required">*</span></label>
            <input type="password" name="password2" required minlength="8">
          </div>
        </div>
        <div class="d-grid-2">
          <div class="form-group">
            <label>Nomor HP / WhatsApp <span class="required">*</span></label>
            <input type="tel" name="no_hp" required value="<?= e($_POST['no_hp'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>NIM <span class="required">*</span></label>
            <input type="text" name="nim" required value="<?= e($_POST['nim'] ?? '') ?>">
          </div>
        </div>
        <div class="form-group">
          <label>Nama Perguruan Tinggi <span class="required">*</span></label>
          <input type="text" name="nama_pt" required value="<?= e($_POST['nama_pt'] ?? '') ?>">
        </div>
        <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center;">Daftar Akun</button>
        <p class="text-center mt-3" style="font-size:13px;color:#718096;">
          Sudah punya akun? <a href="?tab=login" style="color:#1A4F8A;font-weight:700;">Masuk</a>
        </p>
      </form>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
