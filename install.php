<?php
/*
SIMDAF KRI 2026 — Installer
Jalankan SEKALI setelah import database.sql untuk membuat:
- 1 akun admin (admin@bpti.id / admin123)
- 4 akun ketua tim contoh (password: ketua123)
- 4 tim contoh + anggota + dokumen + pengumuman
*/

require_once __DIR__ . '/koneksi.php';

$messages = [];
$ok = true;

try {
    // Cek apakah sudah pernah di-install
    $cek = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($cek > 0) {
        $messages[] = "<span style='color:#92400e;'><i class="fas fa-triangle-exclamation"></i> Tabel users sudah berisi $cek baris. Drop tabel & re-import database.sql jika ingin reset.</span>";
        $ok = false;
    } else {
        // Admin
        $admin_pw = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (nama,email,password,role,no_hp) VALUES (?,?,?,?,?)");
        $stmt->execute(['Admin BPTI', 'admin@bpti.id', $admin_pw, 'admin', '021-5790-0800']);
        $messages[] = "<i class="fas fa-check"></i> Admin dibuat: <b>admin@bpti.id</b> / <b>admin123</b>";

        // Ketua Tim Contoh
        $ketua_pw = password_hash('ketua123', PASSWORD_DEFAULT);
        $ketua = [
            ['Budi Santoso',    'budi@its.ac.id',    '082345678901', '5025201001',         'Institut Teknologi Sepuluh Nopember'],
            ['Siti Rahayu',     'siti@ugm.ac.id',    '083456789012', '19/437281/TK/47890', 'Universitas Gadjah Mada'],
            ['Ahmad Fauzi',     'ahmad@ui.ac.id',    '084567890123', '2006521001',         'Universitas Indonesia'],
            ['Dewi Anggraini',  'dewi@unpad.ac.id',  '085678901234', '140310180001',       'Universitas Padjadjaran'],
        ];
        $stmt = $pdo->prepare("INSERT INTO users (nama,email,password,role,no_hp,nim,nama_pt) VALUES (?,?,?,'ketua_tim',?,?,?)");
        $ketua_ids = [];
        foreach ($ketua as $k) {
            $stmt->execute([$k[0], $k[1], $ketua_pw, $k[2], $k[3], $k[4]]);
            $ketua_ids[] = $pdo->lastInsertId();
        }
        $messages[] = "<i class="fas fa-check"></i> 4 ketua tim contoh dibuat (password: <b>ketua123</b>)";

        // Tim
        $tim = [
            ['KRI2026-00001', $ketua_ids[0], 'Garuda Nusantara', 'Institut Teknologi Sepuluh Nopember', 1, 3, '2021',
                'Dr. Eko Mulyadi, M.T.',       '0012127701', 'eko@its.ac.id',    '08111234567', 'valid',       'lolos'],
            ['KRI2026-00002', $ketua_ids[1], 'Elang Jogja',      'Universitas Gadjah Mada',             2, 4, '2021',
                'Prof. Slamet Riyadi, Ph.D.',  '0005126501', 'slamet@ugm.ac.id', '08222345678', 'valid',       'pending'],
            ['KRI2026-00003', $ketua_ids[2], 'Rajawali UI',      'Universitas Indonesia',               3, 2, '2022',
                'Dr. Hendra Kurniawan, M.Sc.', '0018128001', 'hendra@ui.ac.id',  '08333456789', 'pending',     'pending'],
            ['KRI2026-00004', $ketua_ids[3], 'Mawar Unpad',      'Universitas Padjadjaran',             5, 2, '2021',
                'Dr. Yanti Kusuma, M.T.',      '0007128101', 'yanti@unpad.ac.id','08444567890', 'tidak_valid', 'pending'],
        ];
        $stmt = $pdo->prepare("INSERT INTO tim (no_pendaftaran,user_id,nama_tim,asal_pt,divisi_id,regional_id,tahun_angkatan,nama_dosen,nidn_dosen,email_dosen,hp_dosen,status,hasil_seleksi) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $tim_ids = [];
        foreach ($tim as $t) { $stmt->execute($t); $tim_ids[] = $pdo->lastInsertId(); }
        $messages[] = "<i class="fas fa-check"></i> 4 tim contoh dibuat";

        // Anggota
        $anggota = [
            [$tim_ids[0], 'Budi Santoso',   '5025201001', 'Teknik Elektro',     '082345678901'],
            [$tim_ids[0], 'Rizky Pratama',  '5025201002', 'Teknik Elektro',     '082345678902'],
            [$tim_ids[0], 'Faisal Akbar',   '5025201003', 'Teknik Komputer',    '082345678903'],
            [$tim_ids[0], 'Linda Susanti',  '5025201004', 'Teknik Elektro',     '082345678904'],
            [$tim_ids[1], 'Siti Rahayu',    '19/437281/TK/47890', 'Teknik Elektro',  '083456789012'],
            [$tim_ids[1], 'Rudi Hermawan',  '19/437282/TK/47891', 'Teknik Mesin',    '083456789013'],
            [$tim_ids[1], 'Ayu Wulandari',  '19/437283/TK/47892', 'Teknik Komputer', '083456789014'],
            [$tim_ids[2], 'Ahmad Fauzi',    '2006521001', 'Teknik Elektro',     '084567890123'],
            [$tim_ids[2], 'Bagas Nugroho',  '2006521002', 'Teknik Komputer',    '084567890124'],
            [$tim_ids[2], 'Cindy Marlina',  '2006521003', 'Sistem Informasi',   '084567890125'],
            [$tim_ids[3], 'Dewi Anggraini', '140310180001','Teknik Elektro',    '085678901234'],
            [$tim_ids[3], 'Eko Prasetyo',   '140310180002','Teknik Informatika','085678901235'],
            [$tim_ids[3], 'Fajar Setiawan', '140310180003','Teknik Elektro',    '085678901236'],
        ];
        $stmt = $pdo->prepare("INSERT INTO anggota (tim_id,nama_lengkap,nim,prodi,no_hp) VALUES (?,?,?,?,?)");
        foreach ($anggota as $a) $stmt->execute($a);
        $messages[] = "<i class="fas fa-check"></i> 13 anggota tim dibuat";

        // Dokumen (tim 1 & 2 valid)
        $stmt = $pdo->prepare("INSERT INTO dokumen (tim_id,proposal_teknis,foto_robot,surat_pengantar,ktm_anggota,surat_pernyataan) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$tim_ids[0], 'proposal_demo_1.pdf', 'foto_demo_1.jpg', 'surat_demo_1.pdf', 'ktm_demo_1.pdf', 'pernyataan_demo_1.pdf']);
        $stmt->execute([$tim_ids[1], 'proposal_demo_2.pdf', 'foto_demo_2.jpg', 'surat_demo_2.pdf', 'ktm_demo_2.pdf', 'pernyataan_demo_2.pdf']);
        $messages[] = "<i class="fas fa-check"></i> Dokumen contoh dibuat (placeholder)";

        // Laporan Teknis (tim 1 lolos tahap I)
        $stmt = $pdo->prepare("INSERT INTO laporan_teknis (tim_id,file_laporan,link_video,foto_robot_final) VALUES (?,?,?,?)");
        $stmt->execute([$tim_ids[0], 'laporan_demo_1.pdf', 'https://youtube.com/watch?v=demo001', 'foto_final_1.jpg']);
        $messages[] = "<i class="fas fa-check"></i> Laporan teknis tim Garuda Nusantara dibuat";

        // Pengumuman
        $stmt = $pdo->prepare("INSERT INTO pengumuman (judul,konten,tahap,is_published,published_at,created_by) VALUES (?,?,?,1,NOW(),1)");
        $stmt->execute(['Pendaftaran KRI 2026 Resmi Dibuka',
            'Pendaftaran tim peserta Kontes Robot Indonesia 2026 resmi dibuka mulai tanggal 1 Februari 2026. Segera daftarkan tim Anda dan raih prestasi di tingkat nasional!', 'umum']);
        $stmt->execute(['Batas Akhir Pengumpulan Dokumen',
            'Seluruh tim peserta diwajibkan menyelesaikan upload dokumen paling lambat 31 Maret 2026 pukul 23.59 WIB. Dokumen yang tidak lengkap tidak akan diproses.', 'umum']);
        $stmt->execute(['Pengumuman Lolos Seleksi Tahap I',
            'Berikut adalah daftar tim yang dinyatakan lolos Seleksi Tahap I (seleksi proposal teknis) KRI 2026. Tim yang lolos diwajibkan melanjutkan ke Tahap II dengan mengupload laporan teknis.', 'tahap1']);
        $messages[] = "<i class="fas fa-check"></i> 3 pengumuman dibuat";
    }
} catch (Throwable $e) {
    $ok = false;
    $messages[] = "<span style='color:#991b1b;'><i class="fas fa-times"></i> Error: " . htmlspecialchars($e->getMessage()) . "</span>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="assets/icons/css/all.min.css">
<title>Installer — SIMDAF KRI 2026</title>
<style>
body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:#F0F4F8;margin:0;padding:40px 16px;min-height:100vh;}
.wrap{max-width:680px;margin:0 auto;background:white;border-radius:16px;padding:40px;box-shadow:0 8px 32px rgba(26,79,138,.14);}
h1{color:#1A4F8A;font-size:26px;margin-bottom:6px;}
.sub{color:#4a5568;margin-bottom:28px;font-size:14px;}
ul{list-style:none;padding:0;margin:0 0 28px;}
li{padding:12px 16px;background:#F0F4F8;border-radius:8px;margin-bottom:8px;color:#1a1e2e;font-size:14px;line-height:1.6;}
.warn{background:#fef3c7;color:#92400e;padding:16px 20px;border-radius:10px;font-size:14px;margin-bottom:20px;line-height:1.6;}
.success{background:#d1fae5;color:#065f46;padding:16px 20px;border-radius:10px;font-size:14px;margin-bottom:20px;line-height:1.6;}
.btn{display:inline-block;background:#1A4F8A;color:white;padding:12px 28px;border-radius:10px;text-decoration:none;font-weight:700;font-size:14px;margin-right:10px;}
.btn-ghost{background:white;color:#1A4F8A;border:1.5px solid #1A4F8A;}
code{background:#e8f0f9;padding:2px 8px;border-radius:6px;font-size:13px;color:#1A4F8A;}
</style>
</head>
<body>
<div class="wrap">
  <h1><i class="fas fa-screwdriver-wrench"></i> Installer SIMDAF KRI 2026</h1>
  <p class="sub">Membuat akun default & data contoh untuk demo aplikasi.</p>

  <?php if ($ok): ?>
    <div class="success"><b><i class="fas fa-circle-check"></i> Instalasi berhasil!</b></div>
  <?php endif; ?>

  <ul>
    <?php foreach ($messages as $m): ?>
      <li><?= $m ?></li>
    <?php endforeach; ?>
  </ul>

  <?php if ($ok): ?>
  <div class="warn">
    <b><i class="fas fa-triangle-exclamation"></i> PENTING:</b> Demi keamanan, segera <b>hapus file <code>install.php</code></b> setelah instalasi selesai!
  </div>
  <div style="background:#e8f0f9;padding:16px 20px;border-radius:10px;font-size:14px;margin-bottom:24px;">
    <b style="color:#1A4F8A;">Akun Demo:</b><br>
    <i class="fas fa-user-tie"></i> Admin: <code>admin@bpti.id</code> / <code>admin123</code><br>
    <i class="fas fa-user-graduate"></i> Ketua Tim: <code>budi@its.ac.id</code> / <code>ketua123</code>
  </div>
  <?php endif; ?>

  <a href="login.php" class="btn">→ Buka Halaman Login</a>
  <a href="index.php" class="btn btn-ghost">Beranda</a>
</div>
</body>
</html>
