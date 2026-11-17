<?php

// =============================================
// 1. SESSION DAN KONEKSI DATABASE
// =============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/koneksi.php";

if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
    die("Koneksi database tidak ditemukan.");
}


// =============================================
// 2. PERIKSA REQUEST
// =============================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: daftar_user.php");
    exit;
}


// =============================================
// 3. AMBIL DATA USER
// =============================================

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$role = strtolower(trim($_POST['role'] ?? ''));

$roles_valid = ['admin', 'guru', 'siswa'];

if ($username === '' || $password === '') {
    die("Username dan password wajib diisi.");
}

if (strlen($password) < 6) {
    die("Password minimal 6 karakter.");
}

if (!in_array($role, $roles_valid, true)) {
    die("Role tidak valid.");
}


// =============================================
// 4. VALIDASI DATA BERDASARKAN ROLE
// =============================================

if ($role === 'siswa') {

    $nis = trim($_POST['nis'] ?? '');
    $nama_siswa = trim($_POST['nama_siswa'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');
    $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');

    if (
        $nis === '' ||
        $nama_siswa === '' ||
        $kelas === '' ||
        !in_array($jenis_kelamin, ['L', 'P'], true)
    ) {
        die("Data siswa belum lengkap atau tidak valid.");
    }

} elseif ($role === 'guru') {

    $nip = trim($_POST['nip'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');

    if (
        $nip === '' ||
        $nama === '' ||
        !in_array($jenis_kelamin, ['L', 'P'], true)
    ) {
        die("Data guru belum lengkap atau tidak valid.");
    }
}


// =============================================
// 5. CEK USERNAME SUDAH DIGUNAKAN
// =============================================

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id FROM users WHERE username = ?"
);

if (!$stmt) {
    die("Query username gagal: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_close($stmt);
    die("Username sudah digunakan. Silakan gunakan username lain.");
}

mysqli_stmt_close($stmt);


// =============================================
// 6. TENTUKAN HALAMAN TUJUAN
// =============================================

if ($role === 'siswa') {
    $halaman_tujuan = 'crud_edit_siswa.php';

} elseif ($role === 'guru') {
    $halaman_tujuan = 'daftar_guru.php';

} else {
    $halaman_tujuan = 'daftar_user.php';
}


// =============================================
// 7. MULAI TRANSAKSI
// =============================================

mysqli_begin_transaction($koneksi);

$stmt_users = null;
$stmt_data = null;

try {

    // =========================================
    // SIMPAN TABEL USERS
    // =========================================

    $password_hash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $stmt_users = mysqli_prepare(
        $koneksi,
        "INSERT INTO users
        (username, password, role, created_at)
        VALUES (?, ?, ?, NOW())"
    );

    if (!$stmt_users) {
        throw new Exception(mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param(
        $stmt_users,
        "sss",
        $username,
        $password_hash,
        $role
    );

    if (!mysqli_stmt_execute($stmt_users)) {
        throw new Exception(
            "Gagal menyimpan akun: " .
            mysqli_stmt_error($stmt_users)
        );
    }

    // Ambil ID user
    $user_id = mysqli_insert_id($koneksi);

    mysqli_stmt_close($stmt_users);
    $stmt_users = null;


    // =========================================
    // SIMPAN DATA SISWA
    // =========================================

    if ($role === 'siswa') {

        $stmt_data = mysqli_prepare(
            $koneksi,
            "INSERT INTO siswa
            (nis, nama_siswa, kelas, jenis_kelamin, user_id)
            VALUES (?, ?, ?, ?, ?)"
        );

        if (!$stmt_data) {
            throw new Exception(mysqli_error($koneksi));
        }

        mysqli_stmt_bind_param(
            $stmt_data,
            "ssssi",
            $nis,
            $nama_siswa,
            $kelas,
            $jenis_kelamin,
            $user_id
        );

        if (!mysqli_stmt_execute($stmt_data)) {
            throw new Exception(
                "Gagal menyimpan siswa: " .
                mysqli_stmt_error($stmt_data)
            );
        }

        mysqli_stmt_close($stmt_data);
        $stmt_data = null;
    }


    // =========================================
    // SIMPAN DATA GURU
    // =========================================

    elseif ($role === 'guru') {

        $stmt_data = mysqli_prepare(
            $koneksi,
            "INSERT INTO guru
            (nip, nama, jenis_kelamin, user_id)
            VALUES (?, ?, ?, ?)"
        );

        if (!$stmt_data) {
            throw new Exception(mysqli_error($koneksi));
        }

        mysqli_stmt_bind_param(
            $stmt_data,
            "sssi",
            $nip,
            $nama,
            $jenis_kelamin,
            $user_id
        );

        if (!mysqli_stmt_execute($stmt_data)) {
            throw new Exception(
                "Gagal menyimpan guru: " .
                mysqli_stmt_error($stmt_data)
            );
        }

        mysqli_stmt_close($stmt_data);
        $stmt_data = null;
    }

    // Admin hanya disimpan di tabel users


    // =========================================
    // 8. SELESAIKAN TRANSAKSI
    // =========================================

    mysqli_commit($koneksi);

    echo "<script>
        alert('Data berhasil disimpan!');
        window.location.href = " .
        json_encode($halaman_tujuan) . ";
    </script>";

    exit;


} catch (Throwable $e) {

    mysqli_rollback($koneksi);

    if ($stmt_users instanceof mysqli_stmt) {
        mysqli_stmt_close($stmt_users);
    }

    if ($stmt_data instanceof mysqli_stmt) {
        mysqli_stmt_close($stmt_data);
    }

    die(
        "Proses gagal: " .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );
}

?>
