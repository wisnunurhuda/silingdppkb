<?php
// Sertakan file koneksi yang sudah terhubung ke Railway
include "koneksi.php";

echo "<h2>Memulai Inisialisasi & Pembuatan Tabel Database SILING di Railway...</h2><hr>";

$queries = [
    // 1. Tabel Users / Pengguna (Admin & Kader)
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_lengkap VARCHAR(100) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'kader') NOT NULL,
        kode_wilayah VARCHAR(50) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 2. Tabel Master Wilayah (Kecamatan & Desa Kabupaten Tangerang)
    "CREATE TABLE IF NOT EXISTS master_wilayah (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kecamatan VARCHAR(100) NOT NULL,
        desa VARCHAR(100) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 3. Tabel Utama Keluarga Sasaran & Pendampingan TPK
    "CREATE TABLE IF NOT EXISTS keluarga_sasaran (
        id INT AUTO_INCREMENT PRIMARY KEY,
        jenis_pendampingan VARCHAR(100) DEFAULT 'Pendampingan Baru (Ke-1)',
        kunjungan_ke INT DEFAULT 1,
        kecamatan VARCHAR(100) NOT NULL,
        desa VARCHAR(100) NOT NULL,
        jenis_sasaran VARCHAR(50) NOT NULL,
        no_tim_tpk VARCHAR(50) NOT NULL,
        nama_kader VARCHAR(100) NOT NULL,
        nama_kk VARCHAR(100) NOT NULL,
        nama_sasaran VARCHAR(100) NOT NULL,
        nik VARCHAR(16) NOT NULL,
        alamat TEXT NOT NULL,
        no_hp VARCHAR(20) DEFAULT NULL,
        kadar_hb DECIMAL(4,1) DEFAULT NULL,
        tinggi_badan DECIMAL(5,1) DEFAULT NULL,
        berat_badan DECIMAL(5,1) DEFAULT NULL,
        lila DECIMAL(4,1) DEFAULT NULL,
        status_anemia VARCHAR(50) DEFAULT NULL,
        tfu DECIMAL(4,1) DEFAULT NULL,
        menerima_mbg VARCHAR(50) DEFAULT NULL,
        pus_risiko_4t VARCHAR(50) DEFAULT NULL,
        jenis_alat_kontrasepsi VARCHAR(50) DEFAULT NULL,
        intervensi TEXT DEFAULT NULL,
        sanitasi_jamban VARCHAR(50) DEFAULT NULL,
        sumber_intervensi_jamban VARCHAR(100) DEFAULT NULL,
        foto_kegiatan VARCHAR(255) DEFAULT 'offline_default.jpg',
        latitude VARCHAR(50) DEFAULT '0',
        longitude VARCHAR(50) DEFAULT '0',
        tanggal_input DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

$sukses = true;
foreach ($queries as $index => $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'><b>[Berhasil]</b> Tabel ke-" . ($index + 1) . " berhasil dibuat atau sudah tersedia.</p>";
    } else {
        echo "<p style='color: red;'><b>[Gagal]</b> Error pada query ke-" . ($index + 1) . ": " . mysqli_error($conn) . "</p>";
        $sukses = false;
    }
}

// Cek apakah akun Admin default sudah ada, jika belum buatkan
$cek_admin = mysqli_query($conn, "SELECT * FROM users WHERE username = 'admin'");
if (mysqli_num_rows($cek_admin) == 0) {
    // Password default: admin123 (sudah di-hash)
    $password_default = password_hash('admin123', PASSWORD_DEFAULT);
    $insert_admin = "INSERT INTO users (nama_lengkap, username, password, role) VALUES ('Administrator Dinas', 'admin', '$password_default', 'admin')";
    if (mysqli_query($conn, $insert_admin)) {
        echo "<p style='color: blue;'><b>[Info]</b> Akun Administrator default berhasil dibuat! (Username: <b>admin</b>, Password: <b>admin123</b>)</p>";
    }
}

echo "<hr>";
if ($sukses) {
    echo "<h3 style='color: green;'>🎉 Inisialisasi Database Selesai! Seluruh tabel siap digunakan.</h3>";
    echo "<p>Sekarang Anda dapat membuka kembali halaman utama aplikasi:</p>";
    echo "<a href='index.php' style='padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Buka Aplikasi SILING</a>";
} else {
    echo "<h3 style='color: red;'>⚠️ Terdapat beberapa kendala saat membuat tabel. Periksa kembali log di atas.</h3>";
}
?>