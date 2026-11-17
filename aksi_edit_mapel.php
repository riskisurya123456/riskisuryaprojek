<?php
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: data_mapel.php");
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$kode_mapel = trim($_POST['kodemapel'] ?? '');
$nama_mapel = trim($_POST['namamapel'] ?? '');
$id_guru = (int) ($_POST['nama_guru'] ?? 0);

// Validasi input
if ($id <= 0 || $kode_mapel === '' ||
    $nama_mapel === '' || $id_guru <= 0) {
    die("Data tidak lengkap atau tidak valid.");
}

if (strlen($kode_mapel) > 10 || strlen($nama_mapel) > 100) {
    die("Kode mapel maksimal 10 karakter dan nama mapel maksimal 100 karakter.");
}

// Pastikan mapel tersedia
$stmt = mysqli_prepare($koneksi,
    "SELECT id FROM mapel WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

if (!mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
    die("Data mapel tidak ditemukan.");
}

// Pastikan guru tersedia
$stmt_guru = mysqli_prepare($koneksi,
    "SELECT id FROM guru WHERE id = ?"
);
mysqli_stmt_bind_param($stmt_guru, "i", $id_guru);
mysqli_stmt_execute($stmt_guru);

if (!mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_guru))) {
    die("Guru tidak ditemukan. Silakan pilih guru yang tersedia.");
}

// Update data mapel
$stmt_update = mysqli_prepare($koneksi,
    "UPDATE mapel
     SET kode_mapel = ?, nama_mapel = ?, nama_guru = ?
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt_update,
    "ssii",
    $kode_mapel,
    $nama_mapel,
    $id_guru,
    $id
);

if (mysqli_stmt_execute($stmt_update)) {
    header("Location: data_mapel.php?status=berhasil_edit");
    exit;
}

die("Gagal mengedit mapel: " . mysqli_stmt_error($stmt_update));
?>