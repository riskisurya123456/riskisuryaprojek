<?php

require_once "koneksi.php";

if (!isset($_GET['id'])) {
    echo "<script>
            alert('ID user tidak ditemukan');
            window.location.href = 'crud_edit_siswa.php';
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
if (mysqli_query($koneksi, "DELETE FROM siswa WHERE id = $id")) {

    echo "<script>
            alert('User berhasil dihapus');
            window.location.href = 'crud_edit_siswa.php';
          </script>";

} else {

    echo "<script>
            alert('Gagal menghapus data user: " . addslashes(mysqli_error($koneksi)) . "');
            window.location.href = 'crud_edit_siswa.php';
          </script>";
}

exit;

?>