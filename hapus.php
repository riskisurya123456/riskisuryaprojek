<?php

require_once "koneksi.php";

if (!isset($_GET['id'])) {
    echo "<script>
            alert('ID user tidak ditemukan');
            window.location.href = 'daftar_user.php';
          </script>";
    exit;
}

$id = (int) $_GET['id'];


// ==========================
// HAPUS DATA SISWA
// ==========================

mysqli_query(
    $koneksi,
    "DELETE FROM siswa WHERE user_id = $id"
);


// ==========================
// HAPUS DATA GURU
// ==========================

mysqli_query(
    $koneksi,
    "DELETE FROM guru WHERE user_id = $id"
);


// ==========================
// HAPUS DATA USERS
// ==========================

if (mysqli_query($koneksi, "DELETE FROM users WHERE id = $id")) {

    echo "<script>
            alert('User berhasil dihapus');
            window.location.href = 'daftar_user.php';
          </script>";

} else {

    echo "<script>
            alert('Gagal menghapus data user: " . addslashes(mysqli_error($koneksi)) . "');
            window.location.href = 'daftar_user.php';
          </script>";
}

exit;

?>