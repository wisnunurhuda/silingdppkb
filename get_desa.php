<?php
include "koneksi.php";
$kecamatan = $_GET['kecamatan'] ?? '';
if($kecamatan != '') {
    $query = mysqli_query($conn, "SELECT desa FROM master_wilayah WHERE kecamatan = '$kecamatan' ORDER BY desa ASC");
    echo "<option value=''>-- Pilih Desa / Kelurahan --</option>";
    while($row = mysqli_fetch_assoc($query)){
        echo "<option value='".$row['desa']."'>".$row['desa']."</option>";
    }
}
?>