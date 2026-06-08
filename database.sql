-- SIMDAF KRI 2026 — Database Schema
-- Cara Import:
-- 1. Buka phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Klik "Import" -> pilih file database.sql ini -> Go
-- 3. Buka http://localhost/simdaf-kri2026/install.php  (sekali saja)
--    untuk membuat akun admin & data contoh dengan password ter-hash.

DROP DATABASE IF EXISTS simdaf_kri2026;
CREATE DATABASE simdaf_kri2026
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE simdaf_kri2026;

-- 1. users 
CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nama        VARCHAR(100)  NOT NULL,
    email       VARCHAR(100)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,
    role        ENUM('admin','ketua_tim') DEFAULT 'ketua_tim',
    no_hp       VARCHAR(20),
    nim         VARCHAR(30),
    nama_pt     VARCHAR(150),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. divisi 
CREATE TABLE divisi (
    id_divisi       INT AUTO_INCREMENT PRIMARY KEY,
    nama_divisi     VARCHAR(100) NOT NULL,
    singkatan       VARCHAR(30)  NOT NULL,
    deskripsi       TEXT,
    kuota_nasional  INT DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. regional
CREATE TABLE regional (
    id_regional         INT AUTO_INCREMENT PRIMARY KEY,
    nama_regional       VARCHAR(50)  NOT NULL,
    kota_penyelenggara  VARCHAR(100),
    kuota               INT DEFAULT 0,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. tim
CREATE TABLE tim (
    id_tim              INT AUTO_INCREMENT PRIMARY KEY,
    no_pendaftaran      VARCHAR(30) UNIQUE,
    user_id             INT NOT NULL,
    nama_tim            VARCHAR(100) NOT NULL,
    asal_pt             VARCHAR(150) NOT NULL,
    divisi_id           INT NOT NULL,
    regional_id         INT NOT NULL,
    tahun_angkatan      VARCHAR(10),
    nama_dosen          VARCHAR(100),
    nidn_dosen          VARCHAR(20),
    email_dosen         VARCHAR(100),
    hp_dosen            VARCHAR(20),
    status              ENUM('pending','valid','tidak_valid') DEFAULT 'pending',
    hasil_seleksi       ENUM('pending','lolos','cadangan','tidak_lolos') DEFAULT 'pending',
    catatan_verifikasi  TEXT,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)     REFERENCES users(id)             ON DELETE CASCADE,
    FOREIGN KEY (divisi_id)   REFERENCES divisi(id_divisi)     ON DELETE RESTRICT,
    FOREIGN KEY (regional_id) REFERENCES regional(id_regional) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. anggota
CREATE TABLE anggota (
    id_anggota   INT AUTO_INCREMENT PRIMARY KEY,
    tim_id       INT NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    nim          VARCHAR(30)  NOT NULL,
    prodi        VARCHAR(100),
    no_hp        VARCHAR(20),
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tim_id) REFERENCES tim(id_tim) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. dokumen
CREATE TABLE dokumen (
    id_dokumen       INT AUTO_INCREMENT PRIMARY KEY,
    tim_id           INT NOT NULL UNIQUE,
    proposal_teknis  VARCHAR(255),
    foto_robot       VARCHAR(255),
    surat_pengantar  VARCHAR(255),
    ktm_anggota      VARCHAR(255),
    surat_pernyataan VARCHAR(255),
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tim_id) REFERENCES tim(id_tim) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. laporan_teknis 
CREATE TABLE laporan_teknis (
    id_laporan       INT AUTO_INCREMENT PRIMARY KEY,
    tim_id           INT NOT NULL UNIQUE,
    file_laporan     VARCHAR(255),
    link_video       VARCHAR(255),
    foto_robot_final VARCHAR(255),
    hasil_tahap2     ENUM('pending','lolos','cadangan','tidak_lolos') DEFAULT 'pending',
    catatan_juri     TEXT,
    submitted_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tim_id) REFERENCES tim(id_tim) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. pengumuman 
CREATE TABLE pengumuman (
    id_pengumuman INT AUTO_INCREMENT PRIMARY KEY,
    judul         VARCHAR(200) NOT NULL,
    konten        TEXT NOT NULL,
    tahap         ENUM('umum','tahap1','tahap2') DEFAULT 'umum',
    is_published  TINYINT(1) DEFAULT 0,
    published_at  TIMESTAMP NULL,
    created_by    INT,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SEED: divisi & regional (data master)
INSERT INTO divisi (nama_divisi, singkatan, deskripsi, kuota_nasional) VALUES
('Kontes Robot ABU Indonesia',                 'KRAI',          'Kompetisi robot otomatis bertemakan misi ABU Robocon internasional', 10),
('Kontes Robot SAR Indonesia',                 'KRSRI',         'Kompetisi robot pencarian dan penyelamatan (Search and Rescue)', 12),
('Kontes Robot Sepak Bola Indonesia Beroda',   'KRSBI Beroda',  'Kompetisi robot sepak bola dengan roda', 12),
('Kontes Robot Sepak Bola Indonesia Humanoid', 'KRSBI Humanoid','Kompetisi robot sepak bola berbentuk humanoid', 10),
('Kontes Robot Seni Tari Indonesia',           'KRSTI',         'Kompetisi robot yang menampilkan seni tari tradisional Indonesia', 10),
('Kontes Robot Tematik Indonesia',             'KRTMI',         'Kompetisi robot dengan tema yang ditentukan setiap tahun', 15),
('Kontes Robot Bawah Air Indonesia',           'KRBAI',         'Kompetisi robot yang beroperasi di dalam air (underwater)', 10);

INSERT INTO regional (nama_regional, kota_penyelenggara, kuota) VALUES
('Regional 1', 'Medan',      20),
('Regional 2', 'Bandung',    25),
('Regional 3', 'Surabaya',   25),
('Regional 4', 'Yogyakarta', 20),
('Regional 5', 'Makassar',   15);

-- Akun & data tim contoh dibuat oleh install.php
-- (agar password ter-hash dengan benar via password_hash PHP)
