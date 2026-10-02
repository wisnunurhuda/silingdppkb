<?php
session_start();
include "koneksi.php";
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'kader') {
    header("Location: index.php");
    exit;
}

$wilayah_kader = $_SESSION['kode_wilayah'];
$query = mysqli_query($conn, "SELECT * FROM keluarga_sasaran WHERE kecamatan = '$wilayah_kader' ORDER BY id DESC");
$total_input = mysqli_num_rows($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Kader TPK - SILING</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; }
        .header-top { background: linear-gradient(90deg, #2e7d32, #4caf50); color: white; padding: 12px 25px; }
        .card-stat { border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.04); background: white; border: none; }
    </style>
</head>
<body>
    <div class="header-top d-flex justify-content-between align-items-center">
        <div>
            <h5 class="m-0"><b>Dashboard Kader TPK - Wilayah <?= $wilayah_kader; ?></b></h5>
            <small>Sistem Informasi Lingkup Keluarga Resiko Stunting (SILING)</small>
        </div>
        <div>
            <span class="badge bg-warning text-dark p-2">KADER: <?= $_SESSION['nama']; ?></span>
            <a href="logout.php" class="btn btn-danger btn-sm ms-2"><i class="fa fa-sign-out"></i> Logout</a>
        </div>
    </div>

    <div class="container-fluid px-4 mt-4">
        
        <div class="row mb-4 align-items-center">
            <div class="col-md-8">
                <h4 class="fw-bold text-dark">Data Pendampingan Terinput (Pemutakhiran)</h4>
                <p class="text-muted m-0" style="font-size: 13px;">Riwayat data sasaran keluarga berisiko stunting dan tindak lanjut pendampingan berkala di wilayah <?= $wilayah_kader; ?>.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="form_tambah.php" class="btn btn-success fw-bold px-4 py-2 shadow-sm">
                    <i class="fa fa-plus-circle"></i> + Input Sasaran Baru
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card card-stat p-3">
                    <span class="text-muted" style="font-size: 13px;">Total Riwayat Data di <?= $wilayah_kader; ?></span>
                    <h3 class="fw-bold text-success mb-0"><?= $total_input; ?></h3>
                </div>
            </div>
        </div>

        <div class="card card-stat p-4 mb-5">
            <h5 class="fw-bold text-dark mb-3"><i class="fa fa-table"></i> Rincian Data Lapangan TPK & Tindak Lanjut</h5>
            <hr>
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center align-middle" style="font-size: 11px;">
                    <thead class="table-success align-middle">
                        <tr>
                            <th rowspan="2">Aksi Lanjut</th>
                            <th rowspan="2">Jenis Pendampingan</th>
                            <th rowspan="2">Tanggal</th>
                            <th rowspan="2">Desa</th>
                            <th rowspan="2">Nama Sasaran / NIK</th>
                            <th colspan="5">Jumlah sasaran</th>
                            <th colspan="5">Penapisan KRS</th>
                            <th colspan="7">Jenis Intervensi</th>
                            <th colspan="5">Sumber Intervensi</th>
                            <th rowspan="2">Foto & GPS</th>
                        </tr>
                        <tr>
                            <th>Catin</th><th>Bumil</th><th>Bupas</th><th>Baduta</th><th>Balita</th>
                            <th>ASFR</th><th>Anemia</th><th>Kek</th><th>Jamban</th><th>Air Minum</th>
                            <th>KIE</th><th>Genting</th><th>Rujukan</th><th>Bimwin</th><th>Bansos</th><th>EPPGBM</th><th>PMT</th>
                            <th>Dana Desa</th><th>APBD</th><th>APBN</th><th>Mandiri</th><th>CSR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if(mysqli_num_rows($query) > 0):
                            while($row = mysqli_fetch_assoc($query)): 
                                $is_catin = ($row['jenis_sasaran'] == 'catin') ? '1' : '-';
                                $is_bumil = ($row['jenis_sasaran'] == 'ibu_hamil') ? '1' : '-';
                                $is_bupas = ($row['jenis_sasaran'] == 'ibu_menyusui') ? '1' : '-';
                                $is_baduta = ($row['jenis_sasaran'] == 'baduta') ? '1' : '-';
                                $is_balita = ($row['jenis_sasaran'] == 'balita') ? '1' : '-';

                                $penapisan_asfr = ($row['jenis_sasaran'] == 'catin' && ($row['usia_catin_wanita'] < 21 || $row['usia_catin_pria'] < 25)) ? '✓' : '-';
                                $penapisan_anemia = ($row['status_anemia'] == 'Anemia') ? '✓' : '-';
                                $penapisan_kek = ($row['lila'] > 0 && $row['lila'] < 23.5) ? '✓' : '-';
                                $penapisan_jamban = (strpos($row['sanitasi_jamban'], 'Tidak Layak') !== false || strpos($row['sanitasi_jamban'], 'Tidak Ada') !== false) ? '✓' : '-';
                                $penapisan_air = (strpos($row['sumber_air'], 'Tidak Layak') !== false) ? '✓' : '-';

                                $inv = $row['intervensi'];
                                $src_jamban = $row['sumber_intervensi_jamban'];
                                $src_air = $row['sumber_intervensi_air'];

                                $SesiPendampingan = $row['jenis_pendampingan'] ? $row['jenis_pendampingan'] : 'Pendampingan Baru (Ke-1)';
                        ?>
                        <tr>
                            <td>
                                <a href="form_lanjutan.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning py-0 px-2 fw-bold" style="font-size: 10px;" title="Lanjutkan Pendampingan Berikutnya">
                                    <i class="fa fa-share"></i> + Lanjut
                                </a>
                            </td>
                            <td><span class="badge bg-secondary"><?= $SesiPendampingan; ?></span></td>
                            <td><?= $row['tanggal_input']; ?></td>
                            <td><?= $row['desa']; ?></td>
                            <td class="text-start">
                                <b><?= $row['nama_sasaran']; ?></b><br>
                                <small class="text-muted">NIK: <?= $row['nik']; ?></small>
                            </td>
                            <td><?= $is_catin; ?></td><td><?= $is_bumil; ?></td><td><?= $is_bupas; ?></td><td><?= $is_baduta; ?></td><td><?= $is_balita; ?></td>
                            <td><?= $penapisan_asfr; ?></td><td><?= $penapisan_anemia; ?></td><td><?= $penapisan_kek; ?></td><td><?= $penapisan_jamban; ?></td><td><?= $penapisan_air; ?></td>
                            <td><?= (strpos($inv, 'KIE') !== false) ? '✓' : '-'; ?></td>
                            <td><?= (strpos($inv, 'Genting') !== false) ? '✓' : '-'; ?></td>
                            <td><?= (strpos($inv, 'Rujukan') !== false) ? '✓' : '-'; ?></td>
                            <td><?= (strpos($inv, 'Bimbingan Perkawinan') !== false) ? '✓' : '-'; ?></td>
                            <td><?= (strpos($inv, 'Bansos') !== false) ? '✓' : '-'; ?></td>
                            <td><?= (strpos($inv, 'EPPGBM') !== false) ? '✓' : '-'; ?></td>
                            <td><?= (strpos($inv, 'PMT') !== false) ? '✓' : '-'; ?></td>
                            <td><?= ($src_jamban == 'Dana Desa' || $src_air == 'Dana Desa') ? '✓' : '-'; ?></td>
                            <td><?= ($src_jamban == 'APBD Kabupaten' || $src_air == 'APBD Kabupaten') ? '✓' : '-'; ?></td>
                            <td><?= ($src_jamban == 'APBN / Kementerian' || $src_air == 'APBN / Kementerian') ? '✓' : '-'; ?></td>
                            <td><?= ($src_jamban == 'Swadaya / Mandiri' || $src_air == 'Swadaya / Mandiri') ? '✓' : '-'; ?></td>
                            <td><?= ($src_jamban == 'CSR / Perusahaan' || $src_air == 'CSR / Perusahaan') ? '✓' : '-'; ?></td>
                            <td>
                                <?php if($row['foto_kegiatan']): ?>
                                    <a href="uploads/<?= $row['foto_kegiatan']; ?>" target="_blank" class="btn btn-sm btn-info py-0 px-1" style="font-size: 10px;">Foto</a>
                                <?php else: ?>
                                    <span class="text-danger">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="25" class="text-muted py-3">Belum ada data pendampingan yang diinput untuk wilayah <?= $wilayah_kader; ?>.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>
</html>