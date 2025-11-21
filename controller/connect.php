<?php
declare(strict_types=1);

/*
 * connect.php
 * Simple PDO-based MySQL connection for Laragon project
 * Edit DB_* constants to match your environment
 */

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'iot_ikan');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHAR', 'utf8mb4');

function connect(): PDO
{
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', DB_HOST, DB_PORT, DB_NAME, DB_CHAR);
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // Pada produksi, log error daripada menampilkan langsung
        exit('Koneksi database gagal: ' . $e->getMessage());
    }
}

// Contoh: dapat dipakai di file lain dengan include/require
$pdo = connect();