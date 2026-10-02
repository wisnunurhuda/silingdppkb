<?php
session_start();
include "koneksi.php";
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

$filter_tahun = $_GET['tahun'] ?? '';
$filter_bulan = $_GET['bulan'] ?? '';
$filter_kecamatan = $_GET['kecamatan'] ?? '';
$filter_desa = $_GET['desa'] ?? '';

$where_clauses = [];
if ($filter_tahun != '') { $where_clauses[] = "YEAR(tanggal_input) = '$filter_tahun'"; }
if ($filter_bulan != '') { $where_clauses[] = "MONTH(tanggal_input) = '$filter_bulan'"; }
if ($filter_kecamatan != '') { $where_clauses[] = "kecamatan = '$filter_kecamatan'"; }
if ($filter_desa != '') { $where_clauses[] = "desa = '$filter_desa'"; }

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";
// Deteksi ASFR: Catin Wanita < 21 tahun ATAU Pria < 25 tahun
$asfr = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM keluarga_sasaran $where_sql " . ($where_sql ? "AND" : "WHERE") . " jenis_sasaran='catin' AND (usia_catin_wanita < 21 OR usia_catin_pria < 25)"));

$total_krs = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM keluarga_sasaran $where_sql"));
$kader_aktif = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE role='kader'"));

$asfr = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM keluarga_sasaran $where_sql " . ($where_sql ? "AND" : "WHERE") . " jenis_sasaran='catin'"));
$bumil_kek = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM keluarga_sasaran $where_sql " . ($where_sql ? "AND" : "WHERE") . " jenis_sasaran='ibu_hamil' AND lila < 23.5"));
$balita_stunting = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM keluarga_sasaran $where_sql " . ($where_sql ? "AND" : "WHERE") . " (jenis_sasaran='baduta' OR jenis_sasaran='balita')"));
$air_tidak_layak = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM keluarga_sasaran $where_sql " . ($where_sql ? "AND" : "WHERE") . " sumber_air LIKE '%Tidak Layak%'"));
$jamban_tidak_layak = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM keluarga_sasaran $where_sql " . ($where_sql ? "AND" : "WHERE") . " (sanitasi_jamban LIKE '%Tidak Layak%' OR sanitasi_jamban LIKE '%Tidak Ada%')"));
$dapat_intervensi = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM keluarga_sasaran $where_sql " . ($where_sql ? "AND" : "WHERE") . " intervensi != ''"));

$opt_tahun = mysqli_query($conn, "SELECT DISTINCT YEAR(tanggal_input) as tahun FROM keluarga_sasaran WHERE tanggal_input IS NOT NULL ORDER BY tahun DESC");
$opt_kecamatan = mysqli_query($conn, "SELECT DISTINCT kecamatan FROM master_wilayah ORDER BY kecamatan ASC");
// Ambil data jumlah pendampingan per kecamatan untuk grafik batang
$q_chart = mysqli_query($conn, "SELECT kecamatan, COUNT(*) as total FROM keluarga_sasaran $where_sql GROUP BY kecamatan ORDER BY total DESC");
$chart_kecamatan = [];
$chart_total = [];
while($row_c = mysqli_fetch_assoc($q_chart)){
    if($row_c['kecamatan']) {
        $chart_kecamatan[] = $row_c['kecamatan'];
        $chart_total[] = $row_c['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin Monitoring - SILING</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; }
        .header-top { background: linear-gradient(90deg, #9c27b0, #673ab7); color: white; padding: 12px 25px; }
        .card-stat { border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.04); background: white; border: none; }
    </style>
    <script>
        function filterLoadDesa(kecamatan) {
            var desaSelect = document.getElementById("filter_desa");
            desaSelect.innerHTML = '<option value="">Memuat desa...</option>';
            if (kecamatan === "") {
                desaSelect.innerHTML = '<option value="">Semua Desa / Kelurahan</option>';
                return;
            }
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "get_desa.php?kecamatan=" + encodeURIComponent(kecamatan), true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    desaSelect.innerHTML = '<option value="">Semua Desa / Kelurahan</option>' + xhr.responseText;
                }
            };
            xhr.send();
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="header-top d-flex justify-content-between align-items-center">
        <div>
            <h5 class="m-0"><b>Sistem Informasi Lingkup Keluarga Resiko Stunting</b></h5>
            <small>DPPKB Kabupaten Tangerang</small>
        </div>
        <div>
            <span class="badge bg-success p-2">ADMIN PANEL</span>
            <a href="logout.php" class="btn btn-danger btn-sm ms-2"><i class="fa fa-sign-out"></i> Logout</a>
            <a href="admin_users.php" class="btn btn-warning btn-sm ms-2 fw-bold text-dark"><i class="fa fa-users-gear"></i> Kelola Akun Kader</a>
        </div>
    </div>

    <div class="container-fluid px-4 mt-4">
        
        <!-- Filter Monitoring Pendampingan -->
        <div class="card p-3 mb-4 shadow-sm border-0 rounded-4">
            <h6 class="fw-bold text-secondary mb-3"><i class="fa fa-filter"></i> FILTER MONITORING PENDAMPINGAN</h6>
            <form method="GET" action="">
                <div class="row g-2">
                    <div class="col-md">
                        <select name="tahun" class="form-select form-select-sm">
                            <option value="">Semua Tahun</option>
                            <?php while($t = mysqli_fetch_assoc($opt_tahun)): if($t['tahun']): ?>
                                <option value="<?= $t['tahun']; ?>" <?= ($filter_tahun == $t['tahun']) ? 'selected' : ''; ?>><?= $t['tahun']; ?></option>
                            <?php endif; endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md">
                        <select name="bulan" class="form-select form-select-sm">
                            <option value="">Semua Bulan</option>
                            <?php 
                            $bulan_arr = [1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'];
                            foreach($bulan_arr as $num => $nama):
                            ?>
                                <option value="<?= $num; ?>" <?= ($filter_bulan == $num) ? 'selected' : ''; ?>><?= $nama; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md">
                        <select name="kecamatan" class="form-select form-select-sm" onchange="filterLoadDesa(this.value)">
                            <option value="">Semua Kecamatan</option>
                            <?php while($kec = mysqli_fetch_assoc($opt_kecamatan)): ?>
                                <option value="<?= $kec['kecamatan']; ?>" <?= ($filter_kecamatan == $kec['kecamatan']) ? 'selected' : ''; ?>><?= $kec['kecamatan']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md">
                        <select name="desa" id="filter_desa" class="form-select form-select-sm">
                            <option value="">Semua Desa / Kelurahan</option>
                            <?php 
                            if($filter_kecamatan != '') {
                                $q_ds = mysqli_query($conn, "SELECT desa FROM master_wilayah WHERE kecamatan = '$filter_kecamatan' ORDER BY desa ASC");
                                while($ds = mysqli_fetch_assoc($q_ds)){
                                    $selected = ($filter_desa == $ds['desa']) ? 'selected' : '';
                                    echo "<option value='".$ds['desa']."' $selected>".$ds['desa']."</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fa fa-search"></i> Filter</button>
                        <a href="admin_dashboard.php" class="btn btn-secondary btn-sm"><i class="fa fa-rotate-left"></i> Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Statistik Kartu -->
        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="card card-stat p-3"><span class="text-muted">Jumlah Sasaran KRS</span><h3 class="fw-bold text-primary mb-0"><?= $total_krs; ?></h3></div></div>
            <div class="col-md-3"><div class="card card-stat p-3"><span class="text-muted">Kader Aktif TPK</span><h3 class="fw-bold text-success mb-0"><?= $kader_aktif; ?></h3></div></div>
            <div class="col-md-3"><div class="card card-stat p-3"><span class="text-muted">Harus Intervensi</span><h3 class="fw-bold text-warning mb-0"><?= $total_krs; ?></h3></div></div>
            <div class="col-md-3"><div class="card card-stat p-3"><span class="text-muted">Telah Intervensi</span><h3 class="fw-bold text-success mb-0"><?= $dapat_intervensi; ?></h3></div></div>
        </div>
        <!-- Grafik Batang Pencapaian Pendampingan Per Kecamatan -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card card-stat p-4">
                    <h5 class="fw-bold text-dark mb-3"><i class="fa fa-chart-bar text-primary"></i> Grafik Pencapaian Pendampingan TPK Per-Kecamatan</h5>
                    <hr>
                    <div style="position: relative; height: 320px; width: 100%;">
                        <canvas id="barChartKecamatan"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Script untuk Merender Grafik Batang Chart.js -->
        <script>
            const ctx = document.getElementById('barChartKecamatan').getContext('2d');
            const barChartKecamatan = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($chart_kecamatan); ?>,
                    datasets: [{
                        label: 'Jumlah Keluarga Didampingi (Sasaran KRS)',
                        data: <?= json_encode($chart_total); ?>,
                        backgroundColor: 'rgba(103, 59, 183, 0.7)',
                        borderColor: 'rgba(103, 59, 183, 1)',
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        </script>

        <!-- Tabel Rekapitulasi Hasil Pendampingan TPK -->
        <div class="card card-stat p-4 mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark m-0"><i class="fa fa-table"></i> Daftar Rekapitulasi Hasil Pendampingan TPK</h5>
                <div>
                    <!-- Tombol Unduh Excel & Cetak PDF dengan membawa parameter filter yang sedang aktif -->
                    <a href="export_excel.php?tahun=<?= $filter_tahun; ?>&bulan=<?= $filter_bulan; ?>&kecamatan=<?= $filter_kecamatan; ?>&desa=<?= $filter_desa; ?>" class="btn btn-success btn-sm me-1" target="_blank">
                        <i class="fa fa-file-excel"></i> Unduh Excel
                    </a>
                    <a href="export_pdf.php?tahun=<?= $filter_tahun; ?>&bulan=<?= $filter_bulan; ?>&kecamatan=<?= $filter_kecamatan; ?>&desa=<?= $filter_desa; ?>" class="btn btn-danger btn-sm" target="_blank">
                        <i class="fa fa-file-pdf"></i> Cetak PDF
                    </a>
                </div>
            </div>
            <hr>
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center align-middle" style="font-size: 11px;">
                    <thead class="table-success align-middle">
                        <tr>
                            <th rowspan="2">Tanggal</th>
                            <th rowspan="2">Kecamatan / Desa</th>
                            <th colspan="5">Jumlah sasaran</th>
                            <th colspan="5">Penapisan KRS</th> <!-- Diperbarui dari 4 menjadi 5 kolom -->
                            <th colspan="7">Jenis Intervensi</th>
                            <th colspan="5">Sumber Intervensi</th>
                        </tr>
                        <tr>
                            <!-- Jumlah Sasaran -->
                            <th>Catin</th><th>Bumil</th><th>Bupas</th><th>Baduta</th><th>Balita</th>
                            <!-- Penapisan KRS (Ditambahkan ASFR) -->
                            <th>ASFR (<21 Thn)</th>
                            <th>Anemia</th>
                            <th>Kek</th>
                            <th>Jamban Tidak layak</th>
                            <th>Air Minum Tidak Layak</th>
                            <!-- Jenis Intervensi -->
                            <th>KIE</th><th>Genting</th><th>Rujukan</th><th>Bimbingan Perkawinan</th><th>Bansos</th><th>EPPGBM</th><th>PMT</th>
                            <!-- Sumber Intervensi -->
                            <th>Dana Desa</th><th>APBD Kabupaten</th><th>APBN / Kementerian</th><th>Swadaya / Mandiri</th><th>CSR / Perusahaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $data = mysqli_query($conn, "SELECT * FROM keluarga_sasaran $where_sql ORDER BY id DESC");
                        if(mysqli_num_rows($data) > 0):
                            while($row = mysqli_fetch_assoc($data)): 
                                $is_catin = ($row['jenis_sasaran'] == 'catin') ? '1' : '-';
                                $is_bumil = ($row['jenis_sasaran'] == 'ibu_hamil') ? '1' : '-';
                                $is_bupas = ($row['jenis_sasaran'] == 'ibu_menyusui') ? '1' : '-';
                                $is_baduta = ($row['jenis_sasaran'] == 'baduta') ? '1' : '-';
                                $is_balita = ($row['jenis_sasaran'] == 'balita') ? '1' : '-';

                                // Deteksi ASFR otomatis: Catin Wanita < 21 atau Pria < 25
                                $penapisan_asfr = ($row['jenis_sasaran'] == 'catin' && ($row['usia_catin_wanita'] < 21 || $row['usia_catin_pria'] < 25)) ? '✓' : '-';
                                
                                $penapisan_anemia = ($row['status_anemia'] == 'Anemia') ? '✓' : '-';
                                $penapisan_kek = ($row['lila'] > 0 && $row['lila'] < 23.5) ? '✓' : '-';
                                $penapisan_jamban = (strpos($row['sanitasi_jamban'], 'Tidak Layak') !== false || strpos($row['sanitasi_jamban'], 'Tidak Ada') !== false) ? '✓' : '-';
                                $penapisan_air = (strpos($row['sumber_air'], 'Tidak Layak') !== false) ? '✓' : '-';

                                $inv = $row['intervensi'];
                                $src_jamban = $row['sumber_intervensi_jamban'];
                                $src_air = $row['sumber_intervensi_air'];
                        ?>
                        <tr>
                            <td><?= $row['tanggal_input']; ?></td>
                            <td class="text-start"><?= $row['kecamatan'] . ' / ' . $row['desa']; ?></td>
                            <!-- Jumlah Sasaran -->
                            <td><?= $is_catin; ?></td><td><?= $is_bumil; ?></td><td><?= $is_bupas; ?></td><td><?= $is_baduta; ?></td><td><?= $is_balita; ?></td>
                            <!-- Penapisan KRS (Termasuk ASFR) -->
                            <td><?= $penapisan_asfr; ?></td>
                            <td><?= $penapisan_anemia; ?></td>
                            <td><?= $penapisan_kek; ?></td>
                            <td><?= $penapisan_jamban; ?></td>
                            <td><?= $penapisan_air; ?></td>
                            <!-- Jenis Intervensi -->
                            <td><?= (strpos($inv, 'KIE') !== false) ? '✓' : '-'; ?></td>
                            <td><?= (strpos($inv, 'Genting') !== false) ? '✓' : '-'; ?></td>
                            <td><?= (strpos($inv, 'Rujukan') !== false) ? '✓' : '-'; ?></td>
                            <td><?= (strpos($inv, 'Bimbingan Perkawinan') !== false) ? '✓' : '-'; ?></td>
                            <td><?= (strpos($inv, 'Bansos') !== false) ? '✓' : '-'; ?></td>
                            <td><?= (strpos($inv, 'EPPGBM') !== false) ? '✓' : '-'; ?></td>
                            <td><?= (strpos($inv, 'PMT') !== false) ? '✓' : '-'; ?></td>
                            <!-- Sumber Intervensi -->
                            <td><?= ($src_jamban == 'Dana Desa' || $src_air == 'Dana Desa') ? '✓' : '-'; ?></td>
                            <td><?= ($src_jamban == 'APBD Kabupaten' || $src_air == 'APBD Kabupaten') ? '✓' : '-'; ?></td>
                            <td><?= ($src_jamban == 'APBN / Kementerian' || $src_air == 'APBN / Kementerian') ? '✓' : '-'; ?></td>
                            <td><?= ($src_jamban == 'Swadaya / Mandiri' || $src_air == 'Swadaya / Mandiri') ? '✓' : '-'; ?></td>
                            <td><?= ($src_jamban == 'CSR / Perusahaan' || $src_air == 'CSR / Perusahaan') ? '✓' : '-'; ?></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="23" class="text-muted py-3">Belum ada data rekapitulasi pendampingan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>
</html>