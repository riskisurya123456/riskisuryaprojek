<?php
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $kode_mapel = trim($_POST['kode_mapel'] ?? '');
    $nama_mapel = trim($_POST['nama_mapel'] ?? '');
    $nama_guru  = (int) ($_POST['nama_guru'] ?? 0);

    if ($kode_mapel === '' || $nama_mapel === '' || $nama_guru <= 0) {
        die("Semua field wajib diisi dengan benar.");
    }

    $sql = "INSERT INTO mapel
            (kode_mapel, nama_mapel, nama_guru)
            VALUES (?, ?, ?)";

    $stmt = mysqli_prepare($koneksi, $sql);

    if (!$stmt) {
        die("Query gagal disiapkan: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ssi",
        $kode_mapel,
        $nama_mapel,
        $nama_guru
    );

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);

        echo "<script>
            alert('Data mapel berhasil disimpan!');
            window.location.href='daftar_mapel.php';
        </script>";
        exit;
    } else {
        die("Gagal menyimpan data mapel: " . mysqli_stmt_error($stmt));
    }
}
?>
