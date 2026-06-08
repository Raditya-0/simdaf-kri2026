<?php
$host    = 'localhost';
$dbname  = 'simdaf_kri2026';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    die('
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="assets/icons/css/all.min.css">
        <title>Koneksi Gagal — SIMDAF KRI 2026</title>
        <style>
            body { font-family: sans-serif; background:#f8fafc; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
            .box { background:white; border-radius:12px; padding:40px; max-width:500px; box-shadow:0 4px 24px rgba(0,0,0,.1); text-align:center; }
            h2 { color:#1A4F8A; margin-bottom:12px; }
            p  { color:#4a5568; line-height:1.6; }
            code { background:#f0f4f8; padding:4px 8px; border-radius:6px; font-size:13px; color:#c53030; }
            .steps { text-align:left; background:#f0f4f8; border-radius:8px; padding:16px 20px; margin-top:20px; }
            .steps li { color:#2d3748; margin-bottom:8px; font-size:14px; }
        </style>
    </head>
    <body>
        <div class="box">
            <h2><i class="fas fa-triangle-exclamation"></i> Koneksi Database Gagal</h2>
            <p>Tidak dapat terhubung ke database <code>simdaf_kri2026</code>.</p>
            <div class="steps">
                <strong>Langkah perbaikan:</strong>
                <ol>
                    <li>Pastikan XAMPP berjalan (Apache + MySQL aktif)</li>
                    <li>Import file <code>database.sql</code> via phpMyAdmin</li>
                    <li>Sesuaikan konfigurasi di <code>koneksi.php</code> jika diperlukan</li>
                </ol>
            </div>
            <p style="margin-top:16px;font-size:13px;color:#718096;">Error: ' . htmlspecialchars($e->getMessage()) . '</p>
        </div>
    </body>
    </html>
    ');
}
?>
