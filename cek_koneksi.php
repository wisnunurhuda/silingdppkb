<?php
// Panggil file koneksi yang sudah dibuat sebelumnya
include "koneksi.php";

// Cek apakah variabel koneksi aktif
if ($conn) {
    echo "<div style='font-family: Arial; padding: 20px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px;'>";
    echo "<h3>✅ Koneksi Berhasil!</h3>";
    echo "<p>Aplikasi Anda sukses terhubung ke database: <b>db_silingdppkb</b>.</p>";
    
    // Opsional: Tes ambil data dari tabel users untuk memastikan tabelnya juga sudah ada
    $query = mysqli_query($conn, "SELECT * FROM users");
    $jumlah_user = mysqli_num_rows($query);
    echo "<p>Jumlah akun user yang terdaftar di database saat ini: <b>$jumlah_user user</b>.</p>";
    echo "</div>";
} else {
    echo "<div style='font-family: Arial; padding: 20px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<h3>❌ Koneksi Gagal!</h3>";
    echo "<p>Periksa kembali nama database, username, atau password di file <code>koneksi.php</code>.</p>";
    echo "</div>";
}
?>