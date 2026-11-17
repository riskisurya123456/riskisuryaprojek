<?php
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: daftar_guru.php");
    exit;
}

// Ambil data form
$user_id = (int) ($_POST['user_id'] ?? 0);
$nip = trim($_POST['nip'] ?? '');
$nama = trim($_POST['nama'] ?? '');
$jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validasi data
if (
    $user_id <= 0 ||
    $nip === '' ||
    $nama === '' ||
    $username === '' ||
    !in_array($jenis_kelamin, ['L', 'P'], true)
) {
    die("Data tidak lengkap atau tidak valid.");
}

mysqli_begin_transaction($koneksi);

try {

    // Pastikan akun adalah guru
    $cek = mysqli_prepare($koneksi, "
        SELECT id
        FROM users
        WHERE id = ? AND role = 'guru'
        LIMIT 1
    ");

    mysqli_stmt_bind_param($cek, "i", $user_id);
    mysqli_stmt_execute($cek);
    mysqli_stmt_store_result($cek);

    if (mysqli_stmt_num_rows($cek) !== 1) {
        throw new Exception("Akun guru tidak ditemukan.");
    }

    mysqli_stmt_close($cek);

    // UPDATE DATA PROFIL GURU
    $stmt = mysqli_prepare($koneksi, "
        UPDATE guru
        SET nip = ?,
            nama = ?,
            jenis_kelamin = ?
        WHERE user_id = ?
    ");

    mysqli_stmt_bind_param(
        $stmt,
        "sssi",
        $nip,
        $nama,
        $jenis_kelamin,
        $user_id
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(
            "Gagal memperbarui profil guru: " .
            mysqli_stmt_error($stmt)
        );
    }

    if (mysqli_stmt_affected_rows($stmt) === 0) {
        // Nol perubahan bisa berarti nilainya memang sama.
        // Pastikan profil guru tetap ada.
        mysqli_stmt_close($stmt);

        $cek_profil = mysqli_prepare($koneksi, "
            SELECT user_id FROM guru
            WHERE user_id = ?
            LIMIT 1
        ");

        mysqli_stmt_bind_param($cek_profil, "i", $user_id);
        mysqli_stmt_execute($cek_profil);
        mysqli_stmt_store_result($cek_profil);

        if (mysqli_stmt_num_rows($cek_profil) !== 1) {
            throw new Exception("Profil guru tidak ditemukan.");
        }

        mysqli_stmt_close($cek_profil);
    } else {
        mysqli_stmt_close($stmt);
    }

    // UPDATE USERNAME
    $stmt = mysqli_prepare($koneksi, "
        UPDATE users
        SET username = ?
        WHERE id = ? AND role = 'guru'
    ");

    mysqli_stmt_bind_param(
        $stmt,
        "si",
        $username,
        $user_id
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(
            "Gagal memperbarui username: " .
            mysqli_stmt_error($stmt)
        );
    }

    mysqli_stmt_close($stmt);

    // UPDATE PASSWORD JIKA DIISI
    if ($password !== '') {

        $hash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $stmt = mysqli_prepare($koneksi, "
            UPDATE users
            SET password = ?
            WHERE id = ? AND role = 'guru'
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "si",
            $hash,
            $user_id
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(
                "Gagal memperbarui password: " .
                mysqli_stmt_error($stmt)
            );
        }

        mysqli_stmt_close($stmt);
    }

    // SIMPAN SEMUA PERUBAHAN
    mysqli_commit($koneksi);

    echo "<script>
        alert('Data guru berhasil diperbarui!');
        window.location.href = 'daftar_guru.php';
    </script>";

    exit;

} catch (Throwable $e) {

    mysqli_rollback($koneksi);

    die(
        "Gagal memperbarui data: " .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );
}
?>