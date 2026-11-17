<?php
session_start();
require_once 'koneksi.php';

// Pastikan form dikirim menggunakan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: crud_edit_siswa.php");
    exit;
}

// Ambil data dari form
$id            = (int) ($_POST['id'] ?? 0);
$nis           = trim($_POST['nis'] ?? '');
$nama_siswa    = trim($_POST['nama_siswa'] ?? '');
$kelas         = trim($_POST['kelas'] ?? '');
$jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
$username      = trim($_POST['username'] ?? '');
$password      = $_POST['password'] ?? '';

// Validasi input
if (
    $id <= 0 ||
    $nis === '' ||
    $nama_siswa === '' ||
    $kelas === '' ||
    $username === '' ||
    !in_array($jenis_kelamin, ['L', 'P'], true)
) {
    echo "<script>
        alert('Data belum lengkap atau tidak valid!');
        history.back();
    </script>";
    exit;
}

// Mulai transaksi database
mysqli_begin_transaction($koneksi);

try {
    // Pastikan akun tersebut memang akun siswa
    $cek = mysqli_prepare(
        $koneksi,
        "SELECT id FROM users WHERE id = ? AND role = 'siswa'"
    );

    mysqli_stmt_bind_param($cek, "i", $id);
    mysqli_stmt_execute($cek);

    if (mysqli_num_rows(mysqli_stmt_get_result($cek)) === 0) {
        throw new Exception("Akun siswa tidak ditemukan!");
    }

    // Pastikan profil siswa tersedia
    $cekSiswa = mysqli_prepare(
        $koneksi,
        "SELECT user_id FROM siswa WHERE user_id = ?"
    );

    mysqli_stmt_bind_param($cekSiswa, "i", $id);
    mysqli_stmt_execute($cekSiswa);

    if (mysqli_num_rows(mysqli_stmt_get_result($cekSiswa)) === 0) {
        throw new Exception("Profil siswa tidak ditemukan!");
    }

    // Cek username agar tidak dipakai akun lain
    $cekUsername = mysqli_prepare(
        $koneksi,
        "SELECT id FROM users WHERE username = ? AND id != ?"
    );

    mysqli_stmt_bind_param($cekUsername, "si", $username, $id);
    mysqli_stmt_execute($cekUsername);

    if (mysqli_num_rows(mysqli_stmt_get_result($cekUsername)) > 0) {
        throw new Exception("Username sudah digunakan akun lain!");
    }

    // Update data profil siswa
    $updateSiswa = mysqli_prepare(
        $koneksi,
        "UPDATE siswa
         SET nis = ?, nama_siswa = ?, kelas = ?, jenis_kelamin = ?
         WHERE user_id = ?"
    );

    mysqli_stmt_bind_param(
        $updateSiswa,
        "ssssi",
        $nis,
        $nama_siswa,
        $kelas,
        $jenis_kelamin,
        $id
    );

    if (!mysqli_stmt_execute($updateSiswa)) {
        throw new Exception("Gagal mengubah data siswa!");
    }

    // Update username
    $updateUser = mysqli_prepare(
        $koneksi,
        "UPDATE users SET username = ? WHERE id = ?"
    );

    mysqli_stmt_bind_param($updateUser, "si", $username, $id);

    if (!mysqli_stmt_execute($updateUser)) {
        throw new Exception("Gagal mengubah username!");
    }

    // Ganti password jika kolomnya diisi
    if ($password !== '') {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $updatePassword = mysqli_prepare(
            $koneksi,
            "UPDATE users SET password = ? WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $updatePassword,
            "si",
            $passwordHash,
            $id
        );

        if (!mysqli_stmt_execute($updatePassword)) {
            throw new Exception("Gagal mengubah password!");
        }
    }

    // Simpan semua perubahan
    mysqli_commit($koneksi);

    echo "<script>
        alert('Data siswa berhasil diperbarui!');
        window.location.href = 'crud_edit_siswa.php';
    </script>";
    exit;

} catch (Throwable $e) {
    mysqli_rollback($koneksi);

    echo "<script>
        alert(" . json_encode($e->getMessage()) . ");
        window.location.href = 'crud_edit_siswa.php';
    </script>";
    exit;
}
?>