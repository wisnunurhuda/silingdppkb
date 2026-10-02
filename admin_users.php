<?php
session_start();
include "koneksi.php";
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

$pesan = "";

// 1. Proses Tambah Akun Kader Baru
if (isset($_POST['tambah_kader'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password_raw = $_POST['password'];
    $password_hashed = password_hash($password_raw, PASSWORD_DEFAULT); // Enkripsi aman
    $kode_wilayah = mysqli_real_escape_string($conn, $_POST['kode_wilayah']);

    $cek = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if (mysqli_num_rows($cek) > 0) {
        $pesan = "<div class='alert alert-danger'>Gagal: Username '$username' sudah terdaftar!</div>";
    } else {
        $q_insert = "INSERT INTO users (nama, username, password, role, kode_wilayah) VALUES ('$nama', '$username', '$password_hashed', 'kader', '$kode_wilayah')";
        if (mysqli_query($conn, $q_insert)) {
            $pesan = "<div class='alert alert-success'>Akun kader berhasil ditambahkan dengan keamanan enkripsi password!</div>";
        } else {
            $pesan = "<div class='alert alert-danger'>Gagal menyimpan: " . mysqli_error($conn) . "</div>";
        }
    }
}

// 2. Proses Reset / Ganti Password Kader
if (isset($_POST['reset_password'])) {
    $id_user = intval($_POST['user_id']);
    $password_baru_raw = $_POST['password_baru'];
    $password_baru_hashed = password_hash($password_baru_raw, PASSWORD_DEFAULT);

    $q_update = "UPDATE users SET password = '$password_baru_hashed' WHERE id = '$id_user' AND role = 'kader'";
    if (mysqli_query($conn, $q_update)) {
        $pesan = "<div class='alert alert-success'>Password kader berhasil direset!</div>";
    } else {
        $pesan = "<div class='alert alert-danger'>Gagal mereset password: " . mysqli_error($conn) . "</div>";
    }
}

// 3. Proses Hapus Akun Kader
if (isset($_GET['hapus'])) {
    $id_user = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM users WHERE id = '$id_user' AND role = 'kader'");
    header("Location: admin_users.php");
    exit;
}

// Ambil daftar kader
$list_kader = mysqli_query($conn, "SELECT * FROM users WHERE role = 'kader' ORDER BY kode_wilayah ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Akun Kader - SILING</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; }
        .header-top { background: linear-gradient(90deg, #9c27b0, #673ab7); color: white; padding: 12px 25px; }
        .card-custom { border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.04); background: white; border: none; }
    </style>
</head>
<body>
    <div class="header-top d-flex justify-content-between align-items-center">
        <div>
            <h5 class="m-0"><b>Manajemen Akun Kader TPK</b></h5>
            <small>Admin Panel - DPPKB Kabupaten Tangerang</small>
        </div>
        <div>
            <a href="admin_dashboard.php" class="btn btn-light btn-sm fw-bold"><i class="fa fa-arrow-left"></i> Kembali ke Dashboard</a>
            <a href="logout.php" class="btn btn-danger btn-sm ms-2"><i class="fa fa-sign-out"></i> Logout</a>
        </div>
    </div>

    <div class="container my-4">
        <?= $pesan; ?>

        <div class="row g-4">
            <!-- Form Tambah Kader -->
            <div class="col-md-4">
                <div class="card card-custom p-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="fa fa-user-plus text-primary"></i> Tambah Akun Kader Baru</h6>
                    <hr>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold" style="font-size: 12px;">Nama Lengkap / Tim Kader</label>
                            <input type="text" name="nama" class="form-control form-control-sm" placeholder="Contoh: Kader TPK Balaraja 01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" style="font-size: 12px;">Username Login</label>
                            <input type="text" name="username" class="form-control form-control-sm" placeholder="Contoh: 360301_BALARAJA" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" style="font-size: 12px;">Password</label>
                            <input type="text" name="password" class="form-control form-control-sm" value="123456" required>
                            <small class="text-muted" style="font-size: 10px;">Default: 123456 (Akan di-hash otomatis)</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold" style="font-size: 12px;">Wilayah Tugas Kecamatan</label>
                            <select name="kode_wilayah" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Kecamatan --</option>
                                <?php
                                $q_kec = mysqli_query($conn, "SELECT DISTINCT kecamatan FROM master_wilayah ORDER BY kecamatan ASC");
                                while($k = mysqli_fetch_assoc($q_kec)){
                                    echo "<option value='".$k['kecamatan']."'>".$k['kecamatan']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" name="tambah_kader" class="btn btn-primary btn-sm w-100 fw-bold py-2">
                            <i class="fa fa-save"></i> Simpan Akun Kader
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tabel Daftar Akun Kader -->
            <div class="col-md-8">
                <div class="card card-custom p-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="fa fa-users text-success"></i> Daftar Akun Kader Terdaftar (33 Kecamatan)</h6>
                    <hr>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-center align-middle" style="font-size: 12px;">
                            <thead class="table-success">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Kader / TPK</th>
                                    <th>Username</th>
                                    <th>Wilayah Tugas</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                if(mysqli_num_rows($list_kader) > 0):
                                    while($kdr = mysqli_fetch_assoc($list_kader)): 
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td class="text-start fw-bold"><?= $kdr['nama']; ?></td>
                                    <td><code><?= $kdr['username']; ?></code></td>
                                    <td><span class="badge bg-info text-dark"><?= $kdr['kode_wilayah']; ?></span></td>
                                    <td>
                                        <!-- Tombol Pemicu Modal Reset Password -->
                                        <button type="button" class="btn btn-warning btn-sm py-0 px-2" style="font-size: 11px;" data-bs-toggle="modal" data-bs-target="#resetModal<?= $kdr['id']; ?>">
                                            <i class="fa fa-key"></i> Reset PW
                                        </button>
                                        <a href="admin_users.php?hapus=<?= $kdr['id']; ?>" class="btn btn-danger btn-sm py-0 px-2" style="font-size: 11px;" onclick="return confirm('Yakin ingin menghapus akun kader ini?')">
                                            <i class="fa fa-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>

                                <!-- Modal Reset Password Per Kader -->
                                <div class="modal fade" id="resetModal<?= $kdr['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content text-start">
                                            <div class="modal-header">
                                                <h5 class="modal-title fs-6 fw-bold">Reset Password: <?= $kdr['nama']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="user_id" value="<?= $kdr['id']; ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold" style="font-size: 12px;">Masukkan Password Baru</label>
                                                        <input type="text" name="password_baru" class="form-control" placeholder="Contoh: 123456" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" name="reset_password" class="btn btn-warning btn-sm fw-bold">Simpan Password Baru</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; else: ?>
                                <tr><td colspan="5" class="text-muted py-3">Belum ada akun kader terdaftar.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS untuk Modal -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>