<?php
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: daftar_guru.php");
    exit;
}

$id = (int)($_POST['user_id'] ?? 0);

if ($id <= 0) {
    die("ID guru tidak valid.");
}

$stmt = mysqli_prepare(
    $koneksi,
    "DELETE FROM guru WHERE user_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo "<script>
            alert('Data guru berhasil dihapus!');
            window.location.href='daftar_guru.php';
        </script>";
    } else {
        echo "<script>
            alert('Data guru tidak ditemukan.');
            window.location.href='daftar_guru.php';
        </script>";
    }
} else {
    die("Gagal menghapus data guru: " . mysqli_error($koneksi));
}