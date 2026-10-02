<?php
session_start();
include "koneksi.php";

$pesan_error = "";

// Tangani Sinkronisasi Data dari LocalStorage (Offline Sync)
if (isset($_POST['sync_offline_data'])) {
    $json_data = $_POST['offline_payload'];
    $data_array = json_decode($json_data, true);
    
    if (!empty($data_array)) {
        $berhasil = 0;
        foreach ($data_array as $row) {
            $kecamatan = mysqli_real_escape_string($conn, $row['kecamatan']);
            $desa = mysqli_real_escape_string($conn, $row['desa']);
            $jenis_sasaran = mysqli_real_escape_string($conn, $row['jenis_sasaran']);
            $no_tim_tpk = mysqli_real_escape_string($conn, $row['no_tim_tpk']);
            $nama_kader = mysqli_real_escape_string($conn, $row['nama_kader']);
            $nama_kk = mysqli_real_escape_string($conn, $row['nama_kk']);
            $nama_sasaran = mysqli_real_escape_string($conn, $row['nama_sasaran']);
            $nik = trim($row['nik']);
            $alamat = mysqli_real_escape_string($conn, $row['alamat']);
            $no_hp = mysqli_real_escape_string($conn, $row['no_hp']);
            $kadar_hb = $row['kadar_hb'] ?: 'NULL';
            $tinggi_badan = $row['tinggi_badan'] ?: 'NULL';
            $berat_badan = $row['berat_badan'] ?: 'NULL';
            $lila = $row['lila'] ?: 'NULL';
            $status_anemia = mysqli_real_escape_string($conn, $row['status_anemia']);
            $intervensi = mysqli_real_escape_string($conn, $row['intervensi']);
            $sanitasi_jamban = mysqli_real_escape_string($conn, $row['sanitasi_jamban']);
            $sumber_intervensi_jamban = mysqli_real_escape_string($conn, $row['sumber_intervensi_jamban']);
            $lat = $row['latitude'] ?: '0';
            $long = $row['longitude'] ?: '0';
            $tanggal_input = date('Y-m-d');
            $jenis_pendampingan = "Pendampingan Baru (Ke-1)";
            $kunjungan_ke = 1;

            // Cek NIK dobel
            $cek_nik = mysqli_query($conn, "SELECT * FROM keluarga_sasaran WHERE nik = '$nik'");
            if (mysqli_num_rows($cek_nik) == 0 && preg_match('/^[0-9]{16}$/', $nik)) {
                $query = "INSERT INTO keluarga_sasaran (jenis_pendampingan, kunjungan_ke, kecamatan, desa, jenis_sasaran, no_tim_tpk, nama_kader, nama_kk, nama_sasaran, nik, alamat, no_hp, kadar_hb, tinggi_badan, berat_badan, lila, status_anemia, sanitasi_jamban, sumber_intervensi_jamban, foto_kegiatan, latitude, longitude, tanggal_input) 
                          VALUES ('$jenis_pendampingan', '$kunjungan_ke', '$kecamatan', '$desa', '$jenis_sasaran', '$no_tim_tpk', '$nama_kader', '$nama_kk', '$nama_sasaran', '$nik', '$alamat', '$no_hp', $kadar_hb, $tinggi_badan, $berat_badan, $lila, '$status_anemia', '$sanitasi_jamban', '$sumber_intervensi_jamban', 'offline_default.jpg', '$lat', '$long', '$tanggal_input')";
                if(mysqli_query($conn, $query)){
                    $berhasil++;
                }
            }
        }
        echo "<script>alert('Berhasil menyinkronkan $berhasil data offline ke server!'); localStorage.removeItem('siling_offline_queue'); window.location='kader_dashboard.php';</script>";
        exit;
    }
}

if (isset($_POST['submit'])) {
    $kecamatan = $_POST['kecamatan'];
    $desa = $_POST['desa'];
    $jenis_sasaran = $_POST['jenis_sasaran'];
    $no_tim_tpk = $_POST['no_tim_tpk'];
    $nama_kader = $_POST['nama_kader'];
    $nama_kk = $_POST['nama_kk'];
    $nama_sasaran = $_POST['nama_sasaran'];
    $nik = trim($_POST['nik']);
    $alamat = $_POST['alamat'];
    $no_hp = $_POST['no_hp'];
    $kadar_hb = $_POST['kadar_hb'];
    $tinggi_badan = $_POST['tinggi_badan'];
    $berat_badan = $_POST['berat_badan'];
    $lila = $_POST['lila'];
    $status_anemia = $_POST['status_anemia'];
    $tfu = $_POST['tfu'];
    $menerima_mbg = $_POST['menerima_mbg'];
    $pus_risiko_4t = $_POST['pus_risiko_4t'];
    $jenis_alat_kontrasepsi = $_POST['jenis_alat_kontrasepsi'];
    
    $intervensi = isset($_POST['intervensi']) ? implode(", ", $_POST['intervensi']) : '';
    $sanitasi_jamban = $_POST['sanitasi_jamban'];
    $sumber_intervensi_jamban = $_POST['sumber_intervensi_jamban'];
    
    $lat = $_POST['latitude'];
    $long = $_POST['longitude'];
    $tanggal_input = date('Y-m-d');
    $jenis_pendampingan = "Pendampingan Baru (Ke-1)";
    $kunjungan_ke = 1;

    if (!preg_match('/^[0-9]{16}$/', $nik)) {
        $pesan_error = "Gagal: NIK Sasaran wajib berupa angka persis 16 digit!";
    } else {
        $cek_nik = mysqli_query($conn, "SELECT * FROM keluarga_sasaran WHERE nik = '$nik'");
        if (mysqli_num_rows($cek_nik) > 0) {
            $pesan_error = "Gagal: NIK '$nik' sudah terdaftar dalam database!";
        } else {
            $foto = $_FILES['foto_kegiatan']['name'];
            $tmp = $_FILES['foto_kegiatan']['tmp_name'];
            
            if ($foto == "") {
                $pesan_error = "Gagal: Foto kegiatan pendampingan wajib diunggah!";
            } else {
                if(!is_dir('uploads')) { mkdir('uploads', 0777, true); }
                move_uploaded_file($tmp, "uploads/" . basename($foto));

                $query = "INSERT INTO keluarga_sasaran (jenis_pendampingan, kunjungan_ke, kecamatan, desa, jenis_sasaran, no_tim_tpk, nama_kader, nama_kk, nama_sasaran, nik, alamat, no_hp, kadar_hb, tinggi_badan, berat_badan, lila, status_anemia, tfu, menerima_mbg, pus_risiko_4t, jenis_alat_kontrasepsi, intervensi, sanitasi_jamban, sumber_intervensi_jamban, foto_kegiatan, latitude, longitude, tanggal_input) 
                          VALUES ('$jenis_pendampingan', '$kunjungan_ke', '$kecamatan', '$desa', '$jenis_sasaran', '$no_tim_tpk', '$nama_kader', '$nama_kk', '$nama_sasaran', '$nik', '$alamat', '$no_hp', '$kadar_hb', '$tinggi_badan', '$berat_badan', '$lila', '$status_anemia', '$tfu', '$menerima_mbg', '$pus_risiko_4t', '$jenis_alat_kontrasepsi', '$intervensi', '$sanitasi_jamban', '$sumber_intervensi_jamban', '$foto', '$lat', '$long', '$tanggal_input')";
                
                if(mysqli_query($conn, $query)){
                    echo "<script>alert('Data sasaran baru berhasil disimpan!'); window.location='kader_dashboard.php';</script>";
                    exit;
                } else {
                    $pesan_error = "Gagal menyimpan ke database: " . mysqli_error($conn);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Input Pendampingan TPK - SILING (Mode Offline Support)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function updateOnlineStatus() {
            const indicator = document.getElementById('network-status');
            const syncBtnContainer = document.getElementById('sync-container');
            if (navigator.onLine) {
                indicator.innerHTML = '<span class="badge bg-success">Status: Online (Terhubung ke Server)</span>';
                checkOfflineQueue();
            } else {
                indicator.innerHTML = '<span class="badge bg-danger">Status: Offline (Minim Sinyal / Tanpa Internet) - Data akan disimpan di HP</span>';
                syncBtnContainer.style.display = 'none';
            }
        }

        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);

        window.onload = function() {
            updateOnlineStatus();
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    document.getElementById('lat').value = position.coords.latitude;
                    document.getElementById('long').value = position.coords.longitude;
                    document.getElementById('gps-status').innerHTML = "Lokasi GPS: Terdeteksi ✓";
                    document.getElementById('gps-status').className = "text-success fw-bold";
                });
            }
        };

        function checkOfflineQueue() {
            let queue = JSON.parse(localStorage.getItem('siling_offline_queue')) || [];
            if (queue.length > 0) {
                document.getElementById('sync-container').style.display = 'block';
                document.getElementById('offline-count').innerText = queue.length;
                document.getElementById('offline_payload').value = JSON.stringify(queue);
            } else {
                document.getElementById('sync-container').style.display = 'none';
            }
        }

        function handleFormSubmit(event) {
            if (!navigator.onLine) {
                event.preventDefault(); // Cegah submit standar jika offline
                
                let formData = {
                    kecamatan: document.getElementById('kecamatan').value,
                    desa: document.getElementById('desa').value,
                    jenis_sasaran: document.querySelector('[name="jenis_sasaran"]').value,
                    no_tim_tpk: document.querySelector('[name="no_tim_tpk"]').value,
                    nama_kader: document.querySelector('[name="nama_kader"]').value,
                    nama_kk: document.querySelector('[name="nama_kk"]').value,
                    nama_sasaran: document.querySelector('[name="nama_sasaran"]').value,
                    nik: document.querySelector('[name="nik"]').value,
                    alamat: document.querySelector('[name="alamat"]').value,
                    no_hp: document.querySelector('[name="no_hp"]').value,
                    kadar_hb: document.querySelector('[name="kadar_hb"]').value,
                    tinggi_badan: document.querySelector('[name="tinggi_badan"]').value,
                    berat_badan: document.querySelector('[name="berat_badan"]').value,
                    lila: document.querySelector('[name="lila"]').value,
                    status_anemia: document.querySelector('[name="status_anemia"]').value,
                    sanitasi_jamban: document.querySelector('[name="sanitasi_jamban"]').value,
                    sumber_intervensi_jamban: document.querySelector('[name="sumber_intervensi_jamban"]').value,
                    latitude: document.getElementById('lat').value,
                    longitude: document.getElementById('long').value
                };

                let nikVal = formData.nik;
                if (!/^\d{16}$/.test(nikVal)) {
                    alert('Gagal: NIK Sasaran wajib berupa angka persis 16 digit!');
                    return false;
                }

                let queue = JSON.parse(localStorage.getItem('siling_offline_queue')) || [];
                queue.push(formData);
                localStorage.setItem('siling_offline_queue', JSON.stringify(queue));

                alert('Anda sedang offline. Data berhasil disimpan sementara di memori HP Anda! Silakan sinkronkan nanti saat mendapat sinyal.');
                window.location = 'kader_dashboard.php';
            }
        }

        function loadDesa(kecamatan) {
            var desaSelect = document.getElementById("desa");
            if (kecamatan === "") return;
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "get_desa.php?kecamatan=" + encodeURIComponent(kecamatan), true);
            xhr.onload = function() {
                if (xhr.status === 200) { desaSelect.innerHTML = xhr.responseText; }
            };
            xhr.send();
        }

        function validateNIK(input) {
            input.value = input.value.replace(/\D/g, '').slice(0, 16);
        }
    </script>
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="card p-4 shadow-sm border-0 rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h4 class="text-dark fw-bold m-0">📝 Formulir Input Pendampingan TPK</h4>
                <div id="network-status"></div>
            </div>
            <p class="text-muted" style="font-size: 13px;">Aplikasi mendukung mode offline otomatis saat kader berada di wilayah minim sinyal.</p>
            
            <!-- Tombol Sinkronisasi Muncul Otomatis Jika Ada Data Tertunda & Kembali Online -->
            <div id="sync-container" class="alert alert-warning mb-3" style="display:none;">
                <form method="POST">
                    <b>Perhatian:</b> Terdapat <span id="offline-count">0</span> data tersimpan secara offline di HP Anda.
                    <input type="hidden" name="offline_payload" id="offline_payload">
                    <button type="submit" name="sync_offline_data" class="btn btn-dark btn-sm ms-3 fw-bold">🔄 Sinkronkan Sekarang ke Server</button>
                </form>
            </div>

            <hr>

            <?php if(!empty($pesan_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa fa-exclamation-triangle"></i> <?= $pesan_error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" onsubmit="return handleFormSubmit(event)">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold" style="font-size: 13px;">Kecamatan <span class="text-danger">*</span></label>
                        <select name="kecamatan" id="kecamatan" class="form-select" required onchange="loadDesa(this.value)">
                            <?php
                            $wilayah_kader = $_SESSION['kode_wilayah'] ?? '';
                            if($_SESSION['role'] == 'kader' && !empty($wilayah_kader)) {
                                echo "<option value='".$wilayah_kader."' selected>".$wilayah_kader." (Wilayah Tugas)</option>";
                            } else {
                                echo "<option value=''>-- Pilih Kecamatan --</option>";
                                $q_kec = mysqli_query($conn, "SELECT DISTINCT kecamatan FROM master_wilayah ORDER BY kecamatan ASC");
                                while($k = mysqli_fetch_assoc($q_kec)){
                                    echo "<option value='".$k['kecamatan']."'>".$k['kecamatan']."</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold" style="font-size: 13px;">Desa / Kelurahan <span class="text-danger">*</span></label>
                        <select name="desa" id="desa" class="form-select" required>
                            <option value="">-- Pilih Kecamatan Terlebih Dahulu --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold" style="font-size: 13px;">Jenis Sasaran <span class="text-danger">*</span></label>
                        <select name="jenis_sasaran" class="form-select" required>
                            <option value="">-- Pilih Jenis Sasaran --</option>
                            <option value="catin">Calon Pengantin (Catin)</option>
                            <option value="ibu_hamil">Ibu Hamil</option>
                            <option value="ibu_menyusui">Ibu Menyusui</option>
                            <option value="baduta">Baduta</option>
                            <option value="balita">Balita</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold" style="font-size: 13px;">No Tim TPK <span class="text-danger">*</span></label>
                        <input type="text" name="no_tim_tpk" class="form-control" placeholder="Contoh: TPK-01" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold" style="font-size: 13px;">Nama Kader TPK <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kader" class="form-control" placeholder="Nama Lengkap Kader" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold" style="font-size: 13px;">Nama Kepala Keluarga (KK) <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kk" class="form-control" placeholder="Nama KK" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold" style="font-size: 13px;">Nama Sasaran <span class="text-danger">*</span></label>
                        <input type="text" name="nama_sasaran" class="form-control" placeholder="Nama Sasaran" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold" style="font-size: 13px;">No NIK Sasaran (16 Digit) <span class="text-danger">*</span></label>
                        <input type="text" name="nik" class="form-control" placeholder="16 digit angka NIK" required 
                               maxlength="16" minlength="16" oninput="validateNIK(this)">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold" style="font-size: 13px;">Alamat Sasaran <span class="text-danger">*</span></label>
                        <input type="text" name="alamat" class="form-control" placeholder="Kampung/RT/RW" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold" style="font-size: 13px;">No. Handphone (WA)</label>
                        <input type="text" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold" style="font-size: 13px;">Kadar HB (g/dL)</label>
                        <input type="number" step="0.1" name="kadar_hb" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold" style="font-size: 13px;">Tinggi Badan (TB cm)</label>
                        <input type="number" step="0.1" name="tinggi_badan" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold" style="font-size: 13px;">Berat Badan (BB kg)</label>
                        <input type="number" step="0.1" name="berat_badan" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold" style="font-size: 13px;">LiLA (cm)</label>
                        <input type="number" step="0.1" name="lila" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold" style="font-size: 13px;">Status Anemia</label>
                        <select name="status_anemia" class="form-select">
                            <option value="Tidak Anemia">Tidak Anemia</option>
                            <option value="Anemia">Anemia</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold" style="font-size: 13px;">Sanitasi Jamban <span class="text-danger">*</span></label>
                        <select name="sanitasi_jamban" class="form-select" required>
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
                            <label class="fw-bold mb-2">📸 Unggah Foto Kegiatan Pendampingan <span class="text-danger">*</span></label>
                            <input type="file" name="foto_kegiatan" class="form-control mb-2" accept="image/*" required>
                            <small id="gps-status" class="text-danger fw-bold">Lokasi GPS: Belum terdeteksi</small>
                            <input type="hidden" name="latitude" id="lat">
                            <input type="hidden" name="longitude" id="long">
                        </div>
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" name="submit" class="btn btn-success w-100 py-2 fw-bold">🚀 Kirim Data Baru</button>
                        <a href="kader_dashboard.php" class="btn btn-secondary w-100 mt-2">Batal</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>