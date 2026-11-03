-- ==============================================================
-- STUDI KASUS PRAKTIKUM PEMROGRAMAN WEB - PAKET 2 (TIPE D)
-- SISTEM PASIEN KLINIK HEWAN
-- NIM: 2401020137
-- Basis Data: klinik_hewan_2401020137
-- ==============================================================

CREATE DATABASE IF NOT EXISTS `klinik_hewan_2401020137`
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `klinik_hewan_2401020137`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `pasien_hewan`;
DROP TABLE IF EXISTS `jenis_hewan`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------------
-- 1. Tabel Master: jenis_hewan
-- --------------------------------------------------------------
CREATE TABLE `jenis_hewan` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama_jenis` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_nama_jenis` (`nama_jenis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------
-- 2. Tabel Utama: pasien_hewan
-- Ketentuan:
-- - kode_pasien CHAR(10) NOT NULL UNIQUE
-- - nomor_rekam VARCHAR(30) NOT NULL UNIQUE
-- - CHECK (usia_tahun BETWEEN 0 AND 30)
-- - FK jenis_hewan_id REFERENCES jenis_hewan(id) ON UPDATE CASCADE ON DELETE RESTRICT
-- --------------------------------------------------------------
CREATE TABLE `pasien_hewan` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `kode_pasien` CHAR(10) NOT NULL,
    `nama_hewan` VARCHAR(100) NOT NULL,
    `nomor_rekam` VARCHAR(30) NOT NULL,
    `usia_tahun` TINYINT UNSIGNED NOT NULL,
    `jenis_hewan_id` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_kode_pasien` (`kode_pasien`),
    UNIQUE KEY `uk_nomor_rekam` (`nomor_rekam`),
    KEY `idx_pasien_jenis` (`jenis_hewan_id`),
    CONSTRAINT `fk_pasien_jenis` FOREIGN KEY (`jenis_hewan_id`)
        REFERENCES `jenis_hewan` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT `chk_usia` CHECK (`usia_tahun` BETWEEN 0 AND 30)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------
-- 3. Data Awal Wajib
-- Master: Kucing, Anjing
-- --------------------------------------------------------------
INSERT INTO `jenis_hewan` (`id`, `nama_jenis`) VALUES
(1, 'Kucing'),
(2, 'Anjing');

-- --------------------------------------------------------------
-- Pasien Hewan:
-- PH00000001 | Milo  | RMH-0001 | 3 | Kucing
-- PH00000002 | Bruno | RMH-0002 | 5 | Anjing
-- PH00000003 | Luna  | RMH-0003 | 2 | Kucing
-- --------------------------------------------------------------
INSERT INTO `pasien_hewan` (`id`, `kode_pasien`, `nama_hewan`, `nomor_rekam`, `usia_tahun`, `jenis_hewan_id`, `created_at`, `updated_at`) VALUES
(1, 'PH00000001', 'Milo', 'RMH-0001', 3, 1, NOW(), NOW()),
(2, 'PH00000002', 'Bruno', 'RMH-0002', 5, 2, NOW(), NOW()),
(3, 'PH00000003', 'Luna', 'RMH-0003', 2, 1, NOW(), NOW());
