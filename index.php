<?php

session_start();

// Jika belum login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

// Ambil role user yang login
$role = $_SESSION['role'] ?? '';

// Cek role user kemudian arahkan
// ke halaman sesuai dengan role
switch ($role) {

    case 'admin':
        header("Location: daftar_user.php");
        exit;

    case 'guru':
        header("Location: daftar_guru.php");
        exit;

    case 'siswa':
        header("Location: data_teman.php");
        exit;

    default:
        // Menghapus session yang ada
        session_destroy();

        header("Location: login.php");
        exit;
}

?>