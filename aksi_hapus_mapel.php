<?php
require_once 'koneksi.php';

// Pastikan request menggunakan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: daftar_mapel.php");
    exit;
}

// Ambil ID mapel
$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    echo "<script>
        alert('ID mapel tidak valid!');
        window.location.href = 'daftar_mapel.php';
    </script>";
    exit;
}

// Hapus data mapel berdasarkan ID
$query = mysqli_prepare(
    $koneksi,
    "DELETE FROM mapel WHERE id = ?"
);

mysqli_stmt_bind_param($query, "i", $id);

if (mysqli_stmt_execute($query)) {

    if (mysqli_stmt_affected_rows($query) > 0) {
        mysqli_stmt_close($query);

        echo "<script>
            alert('Data mapel berhasil dihapus!');
            window.location.href = 'daftar_mapel.php';
        </script>";
        exit;
    }

    mysqli_stmt_close($query);

    echo "<script>
        alert('Data mapel tidak ditemukan!');
        window.location.href = 'daftar_mapel.php';
    </script>";
    exit;

} else {
    $error = mysqli_stmt_error($query);
    mysqli_stmt_close($query);

    die("Gagal menghapus data mapel: " .
        htmlspecialchars($error));
}
?>