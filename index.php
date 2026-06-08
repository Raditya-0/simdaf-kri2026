<?php
session_start();
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/config/config.php';

$total_tim = $pdo->query("SELECT COUNT(*) FROM tim")->fetchColumn() ?: 0;
$total_pt  = $pdo->query("SELECT COUNT(DISTINCT asal_pt) FROM tim")->fetchColumn() ?: 0;

$divisi   = $pdo->query("SELECT * FROM divisi ORDER BY id_divisi")->fetchAll();

$news = $pdo->query("SELECT * FROM pengumuman WHERE is_published=1 ORDER BY published_at DESC LIMIT 3")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SIMDAF KRI 2026 — Pendaftaran Kontes Robot Indonesia</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/icons/css/all.min.css">
</head>
<body style="background:white;">

<nav class="navbar-kri">
  <a href="index.php" class="brand">
    <div class="brand-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="#1A4F8A" stroke-width="2.5"><rect x="4" y="6" width="16" height="12" rx="2"/><path d="M9 6V4M15 6V4M9 12h.01M15 12h.01M8 16h8"/></svg>
    </div>
    <div>
      <div class="brand-name">SIMDAF KRI 2026</div>
      <div class="brand-sub">Kemendikbudristek RI</div>
    </div>
  </a>
  <ul class="nav-links">
    <li><a href="#divisi">Divisi</a></li>
    <li><a href="#timeline">Jadwal</a></li>
    <li><a href="#alur">Alur</a></li>
    <li><a href="#faq">FAQ</a></li>
    <li><a href="#kontak">Kontak</a></li>
  </ul>
  <div style="display:flex;gap:10px;align-items:center;">
    <?php if (!empty($_SESSION['user_id'])): ?>
    <div style="position:relative;" id="user-dropdown-wrap">
      <button onclick="toggleDropdown()"
              style="display:flex;align-items:center;gap:10px;background:none;border:none;cursor:pointer;padding:6px 10px;border-radius:10px;">
        <div style="width:38px;height:38px;border-radius:50%;background:#2E75B6;display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:15px;">
          <?= strtoupper(substr($_SESSION['nama'] ?? 'U', 0, 1)) ?>
        </div>
        <div style="text-align:left;">
          <div style="color:white;font-weight:600;font-size:14px;"><?= e($_SESSION['nama'] ?? '') ?></div>
          <div style="color:rgba(255,255,255,0.7);font-size:12px;">
            <?= $_SESSION['role'] === 'admin' ? 'Admin BPTI' : 'Ketua Tim' ?>
          </div>
        </div>
        <i class="fas fa-chevron-down" style="color:rgba(255,255,255,0.7);font-size:11px;"></i>
      </button>

      <div id="user-dropdown" style="display:none;position:absolute;right:0;top:calc(100% + 8px);background:white;border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,0.15);min-width:180px;overflow:hidden;z-index:999;">
        <a href="<?= $_SESSION['role'] === 'admin' ? BASE_URL.'/admin/dashboard.php' : BASE_URL.'/ketua_tim/dashboard.php' ?>"
           style="display:flex;align-items:center;gap:10px;padding:14px 18px;color:#1a1e2e;text-decoration:none;font-size:14px;font-weight:500;">
          <i class="fas fa-gauge" style="color:#2E75B6;width:16px;"></i> Dashboard
        </a>
        <div style="height:1px;background:#f0f4f8;margin:0 12px;"></div>
        <a href="<?= BASE_URL ?>/logout.php"
           style="display:flex;align-items:center;gap:10px;padding:14px 18px;color:#e53e3e;text-decoration:none;font-size:14px;font-weight:500;">
          <i class="fas fa-right-from-bracket" style="color:#e53e3e;width:16px;"></i> Log Out
        </a>
      </div>
    </div>

    <script>
    function toggleDropdown() {
      const d = document.getElementById('user-dropdown');
      d.style.display = d.style.display === 'none' ? 'block' : 'none';
    }
    document.addEventListener('click', function(e) {
      const wrap = document.getElementById('user-dropdown-wrap');
      if (!wrap.contains(e.target)) {
        document.getElementById('user-dropdown').style.display = 'none';
      }
    });
    </script>
    <?php else: ?>
      <a href="login.php" style="color:white;font-weight:600;font-size:14px;text-decoration:none;padding:8px 14px;">Masuk</a>
      <a href="login.php?tab=register" class="btn-daftar">Daftar Sekarang</a>
    <?php endif; ?>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-ring" style="right:-100px;top:50%;transform:translateY(-50%);width:700px;height:700px;"></div>
  <div class="hero-ring" style="right:100px;top:10%;width:380px;height:380px;border-width:50px;"></div>
  <div style="max-width:760px;position:relative;z-index:2;">
    <div class="hero-badge"><span style="display:inline-block;width:8px;height:8px;background:#4ade80;border-radius:50%;margin-right:6px;"></span>Pendaftaran Terbuka 2026</div>
    <h1>Kontes Robot Indonesia 2026</h1>
    <p class="lead">Platform resmi pendaftaran tim peserta KRI 2026 untuk mahasiswa seluruh Indonesia. Daftarkan tim Anda, unggah proposal teknis, dan ikuti seleksi nasional secara digital.</p>
    <div class="hero-btns">
      <a href="login.php?tab=register" class="btn-hero-primary">Daftar Tim Sekarang →</a>
      <a href="#divisi" class="btn-hero-ghost">Lihat Divisi Lomba</a>
    </div>
    <div class="hero-stats">
      <div><div class="hero-stat-num">7</div><div class="hero-stat-label">DIVISI LOMBA</div></div>
      <div><div class="hero-stat-num">5</div><div class="hero-stat-label">REGIONAL</div></div>
      <div><div class="hero-stat-num"><?= $total_tim ?></div><div class="hero-stat-label">TIM TERDAFTAR</div></div>
      <div><div class="hero-stat-num"><?= $total_pt ?></div><div class="hero-stat-label">PERGURUAN TINGGI</div></div>
    </div>
  </div>
</section>

<!-- DIVISI -->
<section id="divisi" style="background:white;">
  <div style="text-align:center;max-width:680px;margin:0 auto 50px;">
    <div class="section-eyebrow">7 Divisi Lomba</div>
    <h2 class="section-title">Pilih Divisi Tim Anda</h2>
    <p class="section-sub">Setiap divisi mempertandingkan jenis robot berbeda dengan kuota nasional masing-masing.</p>
  </div>
  <div class="divisi-grid">
    <?php foreach ($divisi as $d): ?>
      <div class="divisi-card">
        <span class="chip"><?= e($d['singkatan']) ?></span>
        <h3><?= e($d['nama_divisi']) ?></h3>
        <p><?= e($d['deskripsi']) ?></p>
        <div class="kuota"><i class="fas fa-chart-bar"></i> Kuota Nasional: <?= (int)$d['kuota_nasional'] ?> tim</div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- TIMELINE -->
<section id="timeline" style="background:var(--blue-card);">
  <div style="text-align:center;max-width:680px;margin:0 auto 50px;">
    <div class="section-eyebrow">Timeline</div>
    <h2 class="section-title">Jadwal Kegiatan KRI 2026</h2>
  </div>
  <div class="timeline">
    <div class="timeline-item"><div class="timeline-dot done">1</div><div class="timeline-content"><h4>Pembukaan Pendaftaran</h4><span>1 — 28 Februari 2026</span></div></div>
    <div class="timeline-item"><div class="timeline-dot done">2</div><div class="timeline-content"><h4>Upload Proposal Teknis</h4><span>1 — 31 Maret 2026</span></div></div>
    <div class="timeline-item"><div class="timeline-dot">3</div><div class="timeline-content"><h4>Verifikasi Berkas oleh Panitia</h4><span>1 — 15 April 2026</span></div></div>
    <div class="timeline-item"><div class="timeline-dot">4</div><div class="timeline-content"><h4>Pengumuman Seleksi Tahap I</h4><span>20 April 2026</span></div></div>
    <div class="timeline-item"><div class="timeline-dot">5</div><div class="timeline-content"><h4>Submit Laporan Teknis (Tahap II)</h4><span>25 April — 25 Mei 2026</span></div></div>
    <div class="timeline-item"><div class="timeline-dot">6</div><div class="timeline-content"><h4>Pengumuman Seleksi Tahap II</h4><span>5 Juni 2026</span></div></div>
    <div class="timeline-item"><div class="timeline-dot">7</div><div class="timeline-content"><h4>Kontes Regional</h4><span>Juni — Juli 2026</span></div></div>
    <div class="timeline-item"><div class="timeline-dot">8</div><div class="timeline-content"><h4>Kontes Nasional</h4><span>Agustus 2026</span></div></div>
  </div>
</section>

<!-- ALUR PENDAFTARAN -->
<section id="alur" style="background:white;">
  <div style="text-align:center;max-width:680px;margin:0 auto 50px;">
    <div class="section-eyebrow">Step by Step</div>
    <h2 class="section-title">Alur Pendaftaran Tim</h2>
  </div>
  <div class="alur-grid">
    <div class="alur-item"><div class="alur-num">1</div><p>Registrasi Akun Ketua Tim</p></div>
    <div class="alur-item"><div class="alur-num">2</div><p>Login ke Sistem</p></div>
    <div class="alur-item"><div class="alur-num">3</div><p>Isi Formulir Tim</p></div>
    <div class="alur-item"><div class="alur-num">4</div><p>Upload Dokumen</p></div>
    <div class="alur-item"><div class="alur-num">5</div><p>Verifikasi Panitia</p></div>
    <div class="alur-item"><div class="alur-num">6</div><p>Pengumuman Hasil</p></div>
  </div>
</section>

<!-- PENGUMUMAN -->
<?php if ($news): ?>
<section id="berita" style="background:var(--blue-card);">
  <div style="text-align:center;max-width:680px;margin:0 auto 50px;">
    <div class="section-eyebrow">Berita Terbaru</div>
    <h2 class="section-title">Pengumuman dari Panitia</h2>
  </div>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:22px;max-width:1100px;margin:0 auto;">
    <?php foreach ($news as $n): ?>
      <div class="divisi-card">
        <span class="chip"><?= $n['tahap']==='umum'?'UMUM':strtoupper($n['tahap']) ?></span>
        <h3><?= e($n['judul']) ?></h3>
        <p><?= e(mb_substr($n['konten'],0,140)) ?>…</p>
        <div class="kuota"><i class="fas fa-calendar"></i> <?= tgl_id($n['published_at']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- FAQ -->
<section id="faq" style="background:white;">
  <div style="text-align:center;max-width:680px;margin:0 auto 50px;">
    <div class="section-eyebrow">FAQ</div>
    <h2 class="section-title">Pertanyaan yang Sering Diajukan</h2>
  </div>
  <div class="faq-list" style="margin:0 auto;">
    <div class="faq-item"><div class="faq-q" onclick="this.classList.toggle('open');this.nextElementSibling.classList.toggle('open');">Siapa yang berhak mendaftar KRI 2026?<span class="arrow">▾</span></div><div class="faq-a">Mahasiswa aktif jenjang D3/D4/S1 dari perguruan tinggi di seluruh Indonesia yang terdaftar di PDDikti.</div></div>
    <div class="faq-item"><div class="faq-q" onclick="this.classList.toggle('open');this.nextElementSibling.classList.toggle('open');">Berapa jumlah anggota tim?<span class="arrow">▾</span></div><div class="faq-a">Setiap tim terdiri dari minimal 3 dan maksimal 5 mahasiswa, didampingi oleh 1 dosen pembimbing.</div></div>
    <div class="faq-item"><div class="faq-q" onclick="this.classList.toggle('open');this.nextElementSibling.classList.toggle('open');">Apakah pendaftaran berbayar?<span class="arrow">▾</span></div><div class="faq-a">Tidak. Pendaftaran KRI 2026 GRATIS untuk seluruh tim peserta.</div></div>
    <div class="faq-item"><div class="faq-q" onclick="this.classList.toggle('open');this.nextElementSibling.classList.toggle('open');">Apa saja dokumen wajib?<span class="arrow">▾</span></div><div class="faq-a">Proposal teknis robot, foto desain robot, surat pengantar PT, KTM seluruh anggota, dan surat pernyataan keaslian karya.</div></div>
    <div class="faq-item"><div class="faq-q" onclick="this.classList.toggle('open');this.nextElementSibling.classList.toggle('open');">Bagaimana sistem seleksinya?<span class="arrow">▾</span></div><div class="faq-a">Dua tahap: Tahap I (seleksi proposal teknis) dan Tahap II (seleksi laporan teknis & demo robot).</div></div>
  </div>
</section>

<!-- KONTAK / FOOTER -->
<footer id="kontak" class="footer-landing">
  <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:40px;max-width:1100px;margin:0 auto;">
    <div>
      <div class="footer-brand">SIMDAF KRI 2026</div>
      <p style="line-height:1.7;margin-bottom:16px;">Sistem Informasi Pendaftaran Kontes Robot Indonesia 2026.</p>
      <p style="font-size:13px;">Balai Pengembangan Talenta Indonesia (BPTI)<br>Kementerian Pendidikan, Kebudayaan, Riset, dan Teknologi RI</p>
    </div>
    <div>
      <h4 style="color:white;font-size:14px;margin-bottom:14px;text-transform:uppercase;letter-spacing:.05em;">Navigasi</h4>
      <div style="display:flex;flex-direction:column;gap:8px;font-size:14px;">
        <a href="#divisi">Divisi Lomba</a>
        <a href="#timeline">Jadwal Kegiatan</a>
        <a href="#alur">Alur Pendaftaran</a>
        <a href="#faq">FAQ</a>
      </div>
    </div>
    <div>
      <h4 style="color:white;font-size:14px;margin-bottom:14px;text-transform:uppercase;letter-spacing:.05em;">Kontak</h4>
      <div style="font-size:14px;line-height:1.8;">
        <i class="fas fa-location-dot"></i> Jakarta, Indonesia<br>
        <i class="fas fa-phone"></i> (021) 5790-0800<br>
        <i class="fas fa-envelope"></i> kri@kemdikbud.go.id
      </div>
    </div>
  </div>
  <div class="footer-bottom" style="max-width:1100px;margin:36px auto 0;">
    <span>© 2026 BPTI · Kemendikbudristek RI</span>
    <span>Versi <?= APP_VERSION ?></span>
  </div>
  <p style="font-size:11px;color:rgba(255,255,255,0.4);margin-top:16px;text-align:center;">
    Proyek akademik mata kuliah Pemrograman Web, bukan sistem resmi Kemendikbudristek RI atau BPTI.
  </p>
</footer>

<script>
window.addEventListener('scroll', () => {
  document.querySelector('.navbar-kri').classList.toggle('scrolled', window.scrollY > 20);
});
</script>
</body>
</html>
