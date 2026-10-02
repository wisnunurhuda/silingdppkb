<?php
session_start();
include "koneksi.php";

$id_sasaran = $_GET['id'] ?? '';
$q_sasaran = mysqli_query($conn, "SELECT * FROM keluarga_sasaran WHERE id = '$id_sasaran'");
$data_lama = mysqli_fetch_assoc($q_sasaran);

if(!$data_lama) {
    echo "<script>alert('Data sasaran tidak ditemukan!'); window.location='kader_dashboard.php';</script>";
    exit;
}

$jenis_sasaran = $data_lama['jenis_sasaran'];
$kunjungan_saat_ini = intval($data_lama['kunjungan_ke'] ?? 1); // Hitung sesi kunjungan

// Tentukan batas maksimal kunjungan sesuai aturan
$max_kunjungan = 999;
$teks_aturan = "Pendampingan rutin bulanan.";
if ($jenis_sasaran == 'catin') {
    $max_kunjungan = 2;
    $teks_aturan = "Sasaran Catin maksimal 2 kali pendampingan.";
} elseif ($jenis_sasaran == 'ibu_hamil') {
    $max_kunjungan = 6;
    $teks_aturan = "Sasaran Ibu Hamil standar minimal 6 kali pendampingan.";
} elseif ($jenis_sasaran == 'ibu_menyusui') {
    $max_kunjungan = 2;
    $teks_aturan = "Sasaran Ibu Menyusui (Bupas) maksimal 2 kali pendampingan.";
}

if ($kunjungan_saat_ini >= $max_kunjungan && $jenis_sasaran != 'baduta' && $jenis_sasaran != 'balita') {
    echo "<script>alert('Sasaran ini telah mencapai batas maksimal " . $max_kunjungan . " kali pendampingan.'); window.location='kader_dashboard.php';</script>";
    exit;
}

if (isset($_POST['submit'])) {
    $nama_kader = $_POST['nama_kader'];
    $no_hp = $_POST['no_hp'];
    $kadar_hb = $_POST['kadar_hb'];
    $tinggi_badan = $_POST['tinggi_badan'];
    $berat_badan = $_POST['berat_badan'];
    $lila = $_POST['lila'];
    $status_anemia = $_POST['status_anemia'];
    $intervensi = isset($_POST['intervensi']) ? implode(", ", $_POST['intervensi']) : '';
    $sanitasi_jamban = $_POST['sanitasi_jamban'];
    $sumber_intervensi_jamban = $_POST['sumber_intervensi_jamban'];
    
    $kunjungan_berikutnya = $kunjungan_saat_ini + 1;
    $jenis_pendampingan = "Pendampingan Ke-" . $kunjungan_berikutnya;
    $tanggal_input = date('Y-m-d');
    $lat = $_POST['latitude'];
    $long = $_POST['longitude'];

    // Upload Foto Baru
    $foto = $_FILES['foto_kegiatan']['name'];
    $tmp = $_FILES['foto_kegiatan']['tmp_name'];
    if($foto != "") {
        $path = "uploads/" . basename($foto);
        move_uploaded_file($tmp, $path);
        $update_foto = ", foto_kegiatan = '$foto'";
    } else {
        $update_foto = "";
    }

    // UPDATE data sasaran yang sama (tidak menambah baris baru)
    $query = "UPDATE keluarga_sasaran SET 
                nama_kader = '$nama_kader',
                no_hp = '$no_hp',
                kadar_hb = '$kadar_hb',
                tinggi_badan = '$tinggi_badan',
                berat_badan = '$berat_badan',
                lila = '$lila',
                status_anemia = '$status_anemia',
                intervensi = '$intervensi',
                sanitasi_jamban = '$sanitasi_jamban',
                sumber_intervensi_jamban = '$sumber_intervensi_jamban',
                jenis_pendampingan = '$jenis_pendampingan',
                kunjungan_ke = '$kunjungan_berikutnya',
                tanggal_input = '$tanggal_input',
                latitude = '$lat',
                longitude = '$long'
                $update_foto
              WHERE id = '$id_sasaran'";
    
    if(mysqli_query($conn, $query)){
        echo "<script>alert('Pendampingan Ke-$kunjungan_berikutnya berhasil diperbarui!'); window.location='kader_dashboard.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui: " . mysqli_error($conn) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Pendampingan Lanjutan - SILING</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    document.getElementById('lat').value = position.coords.latitude;
                    document.getElementById('long').value = position.coords.longitude;
                    document.getElementById('gps-status').innerHTML = "Lokasi GPS: Terdeteksi ✓";
                    document.getElementById('gps-status').classList.remove('text-danger');
                    document.getElementById('gps-status').classList.add('text-success');
                });
            }
        }
        window.onload = getLocation;
    </script>
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="card p-4 shadow-sm border-0 rounded-4">
            <h4 class="text-dark fw-bold">🔄 Form Pemutakhiran / Pendampingan Lanjutan</h4>
            <p class="text-muted mb-1" style="font-size: 13px;">Sasaran: <b><?= $data_lama['nama_sasaran']; ?></b> (NIK: <?= $data_lama['nik']; ?>)</p>
            <p class="text-primary fw-bold" style="font-size: 12px;">Pembaruan Menuju: Pendampingan Ke-<?= ($kunjungan_saat_ini + 1); ?> (<?= $teks_aturan; ?>)</p>
            <hr>
            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold" style="font-size: 13px;">Nama Kader TPK</label>
                        <input type="text" name="nama_kader" class="form-control" value="<?= $data_lama['nama_kader']; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold" style="font-size: 13px;">No. Handphone (WA)</label>
                        <input type="text" name="no_hp" class="form-control" value="<?= $data_lama['no_hp']; ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold" style="font-size: 13px;">Kadar HB Terbaru (g/dL)</label>
                        <input type="number" step="0.1" name="kadar_hb" class="form-control" value="<?= $data_lama['kadar_hb']; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold" style="font-size: 13px;">Tinggi Badan (cm)</label>
                        <input type="number" step="0.1" name="tinggi_badan" class="form-control" value="<?= $data_lama['tinggi_badan']; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold" style="font-size: 13px;">Berat Badan (kg)</label>
                        <input type="number" step="0.1" name="berat_badan" class="form-control" value="<?= $data_lama['berat_badan']; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold" style="font-size: 13px;">LiLA (cm)</label>
                        <input type="number" step="0.1" name="lila" class="form-control" value="<?= $data_lama['lila']; ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold" style="font-size: 13px;">Intervensi Terbaru yang Diterima</label>
                        <div class="d-flex flex-wrap gap-3 mt-1">
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="intervensi[]" value="KIE"><label class="form-check-label">KIE</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="intervensi[]" value="Genting"><label class="form-check-label">Genting</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="intervensi[]" value="Rujukan"><label class="form-check-label">Rujukan</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="intervensi[]" value="Bansos"><label class="form-check-label">Bansos</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="intervensi[]" value="PMT"><label class="form-check-label">PMT</label></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold" style="font-size: 13px;">Sanitasi Jamban</label>
                        <select name="sanitasi_jamban" class="form-select">
                            <option value="Jamban Layak">Jamban Layak</option>
                            <option value="Jamban Tidak Layak">Jamban Tidak Layak</option>
                            <option value="Tidak Ada Jamban">Tidak Ada Jamban</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold" style="font-size: 13px;">Sumber Intervensi Jamban</label>
                        <select name="sumber_intervensi_jamban" class="form-select">
                            <option value="">-- Pilih Sumber --</option>
                            <option value="Dana Desa">Dana Desa</option>
                            <option value="APBD Kabupaten">APBD Kabupaten</option>
                            <option value="Swadaya / Mandiri">Swadaya / Mandiri</option>
                            <option value="CSR / Perusahaan">CSR / Perusahaan</option>
                        </select>
                    </div>

                    <div class="col-12 mt-4">
                        <div class="p-3 border rounded bg-warning bg-opacity-10">
                            <label class="fw-bold mb-2">📸 Unggah Foto Kunjungan Pendampingan Terbaru</label>
                            <input type="file" name="foto_kegiatan" class="form-control mb-2" accept="image/*">
                            <small id="gps-status" class="text-danger fw-bold">Lokasi GPS: Belum terdeteksi</small>
                            <input type="hidden" name="latitude" id="lat">
                            <input type="hidden" name="longitude" id="long">
                        </div>
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" name="submit" class="btn btn-primary w-100 py-2 fw-bold">💾 Perbarui & Simpan Pendampingan Ke-<?= ($kunjungan_saat_ini + 1); ?></button>
                        <a href="kader_dashboard.php" class="btn btn-secondary w-100 mt-2">Batal</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>