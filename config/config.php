<?php

define('BASE_URL',    'http://localhost/simdaf-kri2026');
define('APP_NAME',    'SIMDAF KRI 2026');
define('APP_VERSION', '1.0.0');

define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', BASE_URL . '/uploads/');

define('MAX_FILE_SIZE', 2 * 1024 * 1024);

define('ALLOWED_DOC',  ['application/pdf']);
define('ALLOWED_IMG',  ['image/jpeg', 'image/png']);
define('ALLOWED_BOTH', ['application/pdf', 'image/jpeg', 'image/png']);

define('EXT_DOC',  ['pdf']);
define('EXT_IMG',  ['jpg', 'jpeg', 'png']);
define('EXT_BOTH', ['pdf', 'jpg', 'jpeg', 'png']);

// timeout sesi 2 jam
define('SESSION_TIMEOUT', 7200);

date_default_timezone_set('Asia/Jakarta');

// token csrf
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function csrf_verify(): bool {
    return isset($_POST['csrf_token'])
        && hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token']);
}

// sanitasi output
function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

// helper redirect
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

// upload file
function upload_file(array $file, string $prefix, array $allowed_ext): string|false {
    if ($file['error'] !== UPLOAD_ERR_OK)       return false;
    if ($file['size'] > MAX_FILE_SIZE)           return false;

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_ext))           return false;

    // nama file unik
    $filename = $prefix . '_' . uniqid() . '.' . $ext;
    $dest = UPLOAD_DIR . $filename;

    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return $filename;
    }
    return false;
}

// format tanggal
function tgl_id(string $date): string {
    $bulan = ['', 'Januari','Februari','Maret','April','Mei','Juni',
              'Juli','Agustus','September','Oktober','November','Desember'];
    $ts = strtotime($date);
    return date('j', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

// badge status html
function badge_status(string $status): string {
    $map = [
        'pending'      => ['warning', 'Pending'],
        'valid'        => ['success', 'Valid'],
        'tidak_valid'  => ['danger',  'Tidak Valid'],
        'lolos'        => ['primary', 'Lolos'],
        'cadangan'     => ['info',    'Cadangan'],
        'tidak_lolos'  => ['secondary','Tidak Lolos'],
    ];
    $m = $map[$status] ?? ['light', $status];
    return "<span class=\"badge bg-{$m[0]}\">{$m[1]}</span>";
}

// nomor pendaftaran unik
function gen_no_pendaftaran(): string {
    return 'KRI2026-' . strtoupper(substr(uniqid(), -5));
}

// cek autentikasi
function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        redirect(BASE_URL . '/login.php?msg=login_required');
    }
    if (isset($_SESSION['last_activity'])
        && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        redirect(BASE_URL . '/login.php?msg=session_expired');
    }
    $_SESSION['last_activity'] = time();
}

function require_admin(): void {
    require_login();
    if ($_SESSION['role'] !== 'admin') {
        redirect(BASE_URL . '/ketua_tim/dashboard.php');
    }
}

function require_ketua(): void {
    require_login();
    if ($_SESSION['role'] !== 'ketua_tim') {
        redirect(BASE_URL . '/admin/dashboard.php');
    }
}
?>
