<?php
include "koneksi.php";

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Rekapitulasi_Global_SILING_Kab_Tangerang.xls");

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

$data = mysqli_query($conn, "SELECT * FROM keluarga_sasaran $where_sql ORDER BY id DESC");
?>
<h3 align="center">PEMERINTAH KABUPATEN TANGERANG</h3>
<h3 align="center">DINAS PENGENDALIAN PENDUDUK DAN KELUARGA BERENCANA (DPPKB)</h3>
<p align="center"><b>Laporan Rekapitulasi Global Hasil Pendampingan TPK (SILING)</b></p>
<br>
<table border="1">
    <thead>
        <tr style="background-color: #d4edda; font-weight: bold; text-align: center;">
            <th rowspan="2">Tanggal</th>
            <th rowspan="2">Kecamatan</th>
            <th rowspan="2">Desa</th>
            <th rowspan="2">Nama Sasaran</th>
            <th rowspan="2">NIK</th>
            <th rowspan="2">Jenis Pendampingan</th>
            <th colspan="5">Jumlah Sasaran</th>
            <th colspan="5">Penapisan KRS</th>
            <th colspan="7">Jenis Intervensi</th>
            <th colspan="5">Sumber Intervensi</th>
        </tr>
        <tr style="background-color: #e2e3e5; font-weight: bold; text-align: center;">
            <th>Catin</th><th>Bumil</th><th>Bupas</th><th>Baduta</th><th>Balita</th>
            <th>ASFR (<21 Thn)</th><th>Anemia</th><th>Kek</th><th>Jamban Tidak Layak</th><th>Air Minum Tidak Layak</th>
            <th>KIE</th><th>Genting</th><th>Rujukan</th><th>Bimwin</th><th>Bansos</th><th>EPPGBM</th><th>PMT</th>
            <th>Dana Desa</th><th>APBD Kab</th><th>APBN</th><th>Mandiri</th><th>CSR</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        while($row = mysqli_fetch_assoc($data)): 
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
            $sesi = $row['jenis_pendampingan'] ? $row['jenis_pendampingan'] : 'Pendampingan Baru (Ke-1)';
        ?>
        <tr>
            <td><?= $row['tanggal_input']; ?></td>
            <td><?= $row['kecamatan']; ?></td>
            <td><?= $row['desa']; ?></td>
            <td><?= $row['nama_sasaran']; ?></td>
            <td>'<?= $row['nik']; ?></td> <!-- Tanda kutip agar NIK tidak terpotong di Excel -->
            <td><?= $sesi; ?></td>
            <td align="center"><?= $is_catin; ?></td><td align="center"><?= $is_bumil; ?></td><td align="center"><?= $is_bupas; ?></td><td align="center"><?= $is_baduta; ?></td><td align="center"><?= $is_balita; ?></td>
            <td align="center"><?= $penapisan_asfr; ?></td><td align="center"><?= $penapisan_anemia; ?></td><td align="center"><?= $penapisan_kek; ?></td><td align="center"><?= $penapisan_jamban; ?></td><td align="center"><?= $penapisan_air; ?></td>
            <td align="center"><?= (strpos($inv, 'KIE') !== false) ? '✓' : '-'; ?></td>
            <td align="center"><?= (strpos($inv, 'Genting') !== false) ? '✓' : '-'; ?></td>
            <td align="center"><?= (strpos($inv, 'Rujukan') !== false) ? '✓' : '-'; ?></td>
            <td align="center"><?= (strpos($inv, 'Bimbingan Perkawinan') !== false) ? '✓' : '-'; ?></td>
            <td align="center"><?= (strpos($inv, 'Bansos') !== false) ? '✓' : '-'; ?></td>
            <td align="center"><?= (strpos($inv, 'EPPGBM') !== false) ? '✓' : '-'; ?></td>
            <td align="center"><?= (strpos($inv, 'PMT') !== false) ? '✓' : '-'; ?></td>
            <td align="center"><?= ($src_jamban == 'Dana Desa' || $src_air == 'Dana Desa') ? '✓' : '-'; ?></td>
            <td align="center"><?= ($src_jamban == 'APBD Kabupaten' || $src_air == 'APBD Kabupaten') ? '✓' : '-'; ?></td>
            <td align="center"><?= ($src_jamban == 'APBN / Kementerian' || $src_air == 'APBN / Kementerian') ? '✓' : '-'; ?></td>
            <td align="center"><?= ($src_jamban == 'Swadaya / Mandiri' || $src_air == 'Swadaya / Mandiri') ? '✓' : '-'; ?></td>
            <td align="center"><?= ($src_jamban == 'CSR / Perusahaan' || $src_air == 'CSR / Perusahaan') ? '✓' : '-'; ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>