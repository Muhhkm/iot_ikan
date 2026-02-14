-- phpMyAdmin SQL Dump
-- untuk menambah table feeding_schedules ke database iot_aquarium

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Struktur dari tabel `feeding_schedules`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `feeding_schedules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `time` varchar(5) NOT NULL COMMENT 'Format: HH:MM',
  `label` varchar(100) NOT NULL COMMENT 'Nama jadwal (Pagi Hari, Siang, dll)',
  `portion` varchar(50) DEFAULT 'Normal (5g)' COMMENT 'Ukuran porsi',
  `days` varchar(100) DEFAULT 'Mon,Tue,Wed,Thu,Fri,Sat,Sun' COMMENT 'Hari-hari aktif',
  `active` tinyint(1) DEFAULT 1 COMMENT '1=aktif, 0=non-aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Data untuk tabel `feeding_schedules`
-- --------------------------------------------------------

INSERT INTO `feeding_schedules` (`id`, `time`, `label`, `portion`, `days`, `active`, `created_at`) VALUES
(1, '08:00', 'Pagi Hari', 'Normal (5g)', 'Mon,Tue,Wed,Thu,Fri,Sat,Sun', 1, NOW()),
(2, '12:00', 'Siang Hari', 'Normal (5g)', 'Mon,Tue,Wed,Thu,Fri,Sat,Sun', 1, NOW()),
(3, '18:00', 'Sore Hari', 'Normal (5g)', 'Mon,Tue,Wed,Thu,Fri,Sat,Sun', 1, NOW());

COMMIT;
