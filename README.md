> **Disclaimer:** Proyek ini merupakan tugas akademik mata kuliah Pemrograman Web dan tidak berafiliasi dengan, didukung oleh, atau mewakili Kemendikbudristek RI maupun Balai Pengembangan Talenta Indonesia (BPTI) secara resmi. Nama instansi digunakan semata-mata sebagai konteks simulasi sistem pendaftaran untuk keperluan pembelajaran.

# SIMDAF KRI 2026

[![Live Demo](https://img.shields.io/badge/Live%20Demo-simdaf--kri2026.site-1A4F8A?style=for-the-badge&logo=google-chrome&logoColor=white)](https://simdaf-kri2026.site/)

Sistem Informasi Pendaftaran Kontes Robot Indonesia 2026 adalah platform digital untuk mengelola pendaftaran tim peserta KRI secara online, mulai dari registrasi akun, pengisian formulir, upload dokumen, verifikasi berkas, hingga pengumuman hasil seleksi.

---

## Live Demo

> **https://simdaf-kri2026.site/**

| Role | Email | Password |
|---|---|---|
| Admin Panitia | `admin@bpti.id` | `admin123` |
| Ketua Tim ITS | `budi@its.ac.id` | `ketua123` |
| Ketua Tim UGM | `siti@ugm.ac.id` | `ketua123` |
| Ketua Tim UI | `ahmad@ui.ac.id` | `ketua123` |
| Ketua Tim Unpad | `dewi@unpad.ac.id` | `ketua123` |

---

## Teknologi

| Layer | Stack |
|---|---|
| Front End | HTML5, CSS3, JavaScript, PHP Native |
| Back End | PHP Native ≥ 8.0, Session Authentication |
| Database | MySQL / MariaDB |
| Libraries | Chart.js, Font Awesome 6 (lokal), Plus Jakarta Sans, QR Code API |
| Server | Apache (XAMPP / LAMP) |

---

## Instalasi Lokal

**1. Salin folder project ke htdocs**
```
C:\xampp\htdocs\simdaf-kri2026\
```

**2. Jalankan XAMPP** — pastikan Apache & MySQL aktif di XAMPP Control Panel.

**3. Import database**
- Buka phpMyAdmin → http://localhost/phpmyadmin
- Import file `database.sql` → database `simdaf_kri2026` dibuat otomatis

**4. Jalankan installer (sekali saja)**
```
http://localhost/simdaf-kri2026/install.php
```
> Setelah selesai, **hapus `install.php`** demi keamanan.

**5. Buka aplikasi**
```
http://localhost/simdaf-kri2026/
```

---

## Fitur

### Ketua Tim
- Registrasi & login dengan validasi
- Dashboard progress pendaftaran (4 step)
- Formulir multi-step: data tim, anggota (3–5 orang), dosen pembimbing
- Upload 5 dokumen wajib (PDF/JPG/PNG, maks 2 MB)
- Submit laporan teknis Tahap II
- Cetak bukti pendaftaran dengan QR Code

### Admin / Panitia
- Dashboard statistik + grafik Chart.js
- Manajemen data tim: filter, search, CRUD
- Verifikasi berkas per tim (approve/reject + catatan)
- Seleksi Tahap I & II
- CRUD pengumuman (publish/draft)
- Export rekap data ke Excel

---

## Struktur Database

| Tabel | Keterangan |
|---|---|
| `users` | Akun admin & ketua tim |
| `divisi` | 7 divisi KRI |
| `regional` | 5 regional penyelenggara |
| `tim` | Data tim + status verifikasi + hasil seleksi |
| `anggota` | Anggota tim (min 3, maks 5) |
| `dokumen` | 5 dokumen wajib per tim |
| `laporan_teknis` | Laporan Tahap II |
| `pengumuman` | Pengumuman dari panitia |

---

## Keamanan

- Password hashing — `password_hash()` bcrypt
- SQL Injection — PDO prepared statements
- CSRF protection — token pada seluruh form POST
- XSS filtering — `htmlspecialchars()` di semua output
- Session timeout — 2 jam idle
- Login limiter — maks 5 percobaan gagal / 5 menit
- File upload validation — ekstensi + ukuran ≤ 2 MB

---

## Konfigurasi

`config/config.php`
```php
define('BASE_URL', 'http://localhost/simdaf-kri2026');
define('MAX_FILE_SIZE', 2 * 1024 * 1024);
define('SESSION_TIMEOUT', 7200);
```

`koneksi.php`
```php
$host   = 'localhost';
$dbname = 'simdaf_kri2026';
$user   = 'root';
$pass   = '';
```

---

## Struktur Folder

```
simdaf-kri2026/
├── index.php
├── login.php
├── logout.php
├── koneksi.php
├── database.sql
├── config/config.php
├── templates/
├── admin/
├── ketua_tim/
├── assets/css/style.css
└── uploads/
```

---

Dibuat untuk keperluan Final Project Pemrograman Web
