<?php
// Aktifkan pelaporan error agar terlihat di layar
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$db   = getenv('MYSQLDATABASE') ?: 'railway';
$port = getenv('MYSQLPORT') ?: '3306';

// Cek apakah ekstensi mysqli aktif
if (!function_exists('mysqli_connect')) {
    die("KRITIS: Ekstensi MySQLi belum aktif di server PHP ini!");
}

$conn = mysqli_connect($host, $user, $pass, $db, (int)$port);

if (!$conn) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}
// Jika berhasil, tampilkan pesan sukses sementara
echo "Koneksi database berhasil terhubung ke server!";
?>