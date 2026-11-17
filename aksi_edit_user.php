<?php

$koneksi = mysqli_connect("localhost", "root", "", "db_siacad_smk2");

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: daftar_user.php");
    exit;
}

$id = $_POST['id'];
$username = $_POST['username'];
$password = $_POST['password'] ?? '';


// =============================
// AMBIL DATA USER
// =============================

$queryUser = mysqli_query(
    $koneksi,
    "SELECT * FROM users WHERE id='$id'"
);

if (!$queryUser) {
    die("Query user gagal: " . mysqli_error($koneksi));
}

if (mysqli_num_rows($queryUser) == 0) {
    die("User tidak ditemukan.");
}

$user = mysqli_fetch_assoc($queryUser);
$role = $user['role'];


// =============================
// UPDATE USERNAME + PASSWORD
// =============================

if (!empty($password)) {

    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $query = mysqli_query(
        $koneksi,
        "UPDATE users SET
            username='$username',
            password='$passwordHash'
         WHERE id='$id'"
    );

} else {

    $query = mysqli_query(
        $koneksi,
        "UPDATE users SET
            username='$username'
         WHERE id='$id'"
    );
}


// CEK UPDATE USERS
if (!$query) {
    die("Gagal update username: " . mysqli_error($koneksi));
}


// =============================
// UPDATE SISWA
// =============================

if ($role == 'siswa') {

    $nis = $_POST['nis'];
    $nama_siswa = $_POST['nama_siswa'];
    $kelas = $_POST['kelas'];
    $jenis_kelamin = $_POST['jenis_kelamin'];

    $query = mysqli_query(
        $koneksi,
        "UPDATE siswa SET
            nis='$nis',
            nama_siswa='$nama_siswa',
            kelas='$kelas',
            jenis_kelamin='$jenis_kelamin'
         WHERE user_id='$id'"
    );

    if (!$query) {
        die("Gagal update siswa: " . mysqli_error($koneksi));
    }
}


// =============================
// UPDATE GURU
// =============================

elseif ($role == 'guru') {

    $nip = $_POST['nip'];
    $nama = $_POST['nama'];
    $jenis_kelamin = $_POST['jenis_kelamin'];

    $query = mysqli_query(
        $koneksi,
        "UPDATE guru SET
            nip='$nip',
            nama='$nama',
            jenis_kelamin='$jenis_kelamin'
         WHERE user_id='$id'"
    );

    if (!$query) {
        die("Gagal update guru: " . mysqli_error($koneksi));
    }
}


// =============================
// SELESAI
// =============================

header("Location: daftar_user.php");
exit;

?>