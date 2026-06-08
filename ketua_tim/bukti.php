<?php
session_start();
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../config/config.php';
require_ketua();

$stmt = $pdo->prepare("SELECT t.*, d.nama_divisi, d.singkatan, r.nama_regional, r.kota_penyelenggara, u.email, u.no_hp
                       FROM tim t
                       LEFT JOIN divisi d ON t.divisi_id=d.id_divisi
                       LEFT JOIN regional r ON t.regional_id=r.id_regional
                       LEFT JOIN users u ON t.user_id=u.id
                       WHERE t.user_id=? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$tim = $stmt->fetch();

if (!$tim) redirect('formulir.php');

$stmt = $pdo->prepare("SELECT * FROM anggota WHERE tim_id=? ORDER BY id_anggota");
$stmt->execute([$tim['id_tim']]);
$anggota = $stmt->fetchAll();

// payload qr code
$qr_payload = 'KRI2026|' . $tim['no_pendaftaran'] . '|' . $tim['nama_tim'] . '|' . $tim['singkatan'];
$qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($qr_payload);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Bukti Pendaftaran — <?= e($tim['no_pendaftaran']) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/icons/css/all.min.css">
<style>
@page { size: A4; margin: 18mm; }
.print-wrap { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
.cetak-header { text-align: center; padding-bottom: 24px; border-bottom: 3px double #1A4F8A; margin-bottom: 30px; }
.cetak-header h1 { color:#1A4F8A; font-size:24px; margin-bottom:6px; }
.cetak-header h2 { font-size:18px; color:#2E75B6; margin-bottom:6px; }
.cetak-header p { color:#4a5568; font-size:13px; }
.cetak-section { margin-bottom: 24px; }
.cetak-section h3 { font-size:14px; color:#1A4F8A; margin-bottom:10px; padding-bottom:6px; border-bottom: 2px solid #E8F0F9; text-transform:uppercase; letter-spacing:.06em; }
.cetak-section table { width:100%; }
.cetak-section td { padding:6px 0; font-size:14px; }
.cetak-section td:first-child { color:#4a5568; width:35%; }
.cetak-section td:last-child  { color:#1a1e2e; font-weight:600; }
.cetak-grid { display:grid; grid-template-columns: 1fr auto; gap:30px; align-items:center; background:#F0F4F8; padding:20px; border-radius:10px; }
.qr-box { text-align:center; }
.qr-box img { border:4px solid white; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.1); }
.qr-box p { font-size:11px; color:#718096; margin-top:6px; }
.footer-cetak { text-align:center; margin-top:30px; padding-top:20px; border-top:1px dashed #D1DFF0; font-size:11px; color:#718096; line-height:1.7; }
@media print { body { background:white !important; } .no-print { display:none !important; } .print-wrap { box-shadow:none; padding:0; } }
</style>
</head>
<body style="background:#F0F4F8;padding:30px 16px;">

<div class="no-print" style="max-width:800px;margin:0 auto 20px;display:flex;justify-content:space-between;align-items:center;">
  <a href="dashboard.php" class="btn btn-outline">← Kembali</a>
  <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Cetak / Simpan PDF</button>
</div>

<div class="print-wrap">
  <div class="cetak-header">
    <div style="font-size:11px;color:#718096;letter-spacing:.08em;">KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI RI</div>
    <h2>BUKTI PENDAFTARAN</h2>
    <h1>KONTES ROBOT INDONESIA 2026</h1>
    <p>Balai Pengembangan Talenta Indonesia (BPTI)</p>
  </div>

  <div class="cetak-grid">
    <div>
      <div style="font-size:11px;color:#718096;letter-spacing:.08em;text-transform:uppercase;margin-bottom:4px;">No. Pendaftaran</div>
      <div style="font-size:28px;font-weight:800;color:#1A4F8A;letter-spacing:.04em;"><?= e($tim['no_pendaftaran']) ?></div>
      <div style="font-size:13px;color:#4a5568;margin-top:6px;">Tanggal Pendaftaran: <?= tgl_id($tim['created_at']) ?></div>
      <div style="margin-top:10px;">Status: <?= badge_status($tim['status']) ?> · Hasil: <?= badge_status($tim['hasil_seleksi']) ?></div>
    </div>
    <div class="qr-box">
      <img src="<?= $qr_url ?>" alt="QR Code" width="160" height="160">
      <p>QR Verifikasi</p>
    </div>
  </div>

  <div class="cetak-section" style="margin-top:30px;">
    <h3>Data Tim</h3>
    <table>
      <tr><td>Nama Tim</td><td><?= e($tim['nama_tim']) ?></td></tr>
      <tr><td>Asal Perguruan Tinggi</td><td><?= e($tim['asal_pt']) ?></td></tr>
      <tr><td>Divisi Lomba</td><td><?= e($tim['nama_divisi']) ?> (<?= e($tim['singkatan']) ?>)</td></tr>
      <tr><td>Regional</td><td><?= e($tim['nama_regional']) ?> — <?= e($tim['kota_penyelenggara']) ?></td></tr>
      <tr><td>Tahun Angkatan</td><td><?= e($tim['tahun_angkatan']) ?></td></tr>
      <tr><td>Email Ketua</td><td><?= e($tim['email']) ?></td></tr>
      <tr><td>No. HP Ketua</td><td><?= e($tim['no_hp']) ?></td></tr>
    </table>
  </div>

  <div class="cetak-section">
    <h3>Anggota Tim (<?= count($anggota) ?> orang)</h3>
    <table style="border-collapse:collapse;">
      <thead><tr style="background:#F0F4F8;">
        <th style="padding:8px 10px;font-size:12px;text-align:left;">#</th>
        <th style="padding:8px 10px;font-size:12px;text-align:left;">Nama</th>
        <th style="padding:8px 10px;font-size:12px;text-align:left;">NIM</th>
        <th style="padding:8px 10px;font-size:12px;text-align:left;">Prodi</th>
      </tr></thead>
      <tbody>
        <?php foreach ($anggota as $i => $a): ?>
          <tr style="border-bottom:1px solid #E8F0F9;">
            <td style="padding:8px 10px;font-size:13px;"><?= $i+1 ?></td>
            <td style="padding:8px 10px;font-size:13px;font-weight:600;"><?= e($a['nama_lengkap']) ?></td>
            <td style="padding:8px 10px;font-size:13px;"><?= e($a['nim']) ?></td>
            <td style="padding:8px 10px;font-size:13px;"><?= e($a['prodi']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="cetak-section">
    <h3>Dosen Pembimbing</h3>
    <table>
      <tr><td>Nama</td><td><?= e($tim['nama_dosen']) ?></td></tr>
      <tr><td>NIDN</td><td><?= e($tim['nidn_dosen']) ?></td></tr>
      <tr><td>Email</td><td><?= e($tim['email_dosen']) ?></td></tr>
      <tr><td>No. HP</td><td><?= e($tim['hp_dosen']) ?></td></tr>
    </table>
  </div>

  <div class="footer-cetak">
    Bukti pendaftaran ini sah secara digital. Verifikasi keaslian melalui QR Code di atas.<br>
    Dokumen dicetak pada: <?= tgl_id(date('Y-m-d')) ?> · SIMDAF KRI 2026 · BPTI Kemendikbudristek RI
  </div>
</div>
</body>
</html>
