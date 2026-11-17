<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['login']) || ($_SESSION['role'] ?? '') !== 'guru') {
    header("Location: login.php");
    exit;
}

$user_id = (int) ($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);
$id = (int) ($_GET['id'] ?? 0);
$kelas = trim($_GET['kelas'] ?? '');
$mapel_id = (int) ($_GET['mapel_id'] ?? 0);

if ($user_id <= 0 || $id <= 0 || $kelas === '' || $mapel_id <= 0) {
    die("Parameter tidak valid.");
}

// Hapus hanya jika nilai termasuk kelas dan mapel yang diajarkan guru
$sql = "DELETE nr
        FROM nilai_rapor nr
        JOIN siswa s ON s.id = nr.siswa_id
        JOIN guru g ON g.user_id = ?
        JOIN guru_mengajar gm
          ON gm.guru_id = g.id
         AND gm.mapel_id = nr.mapel_id
         AND gm.kelas = s.kelas
        WHERE nr.id = ?
          AND nr.mapel_id = ?
          AND s.kelas = ?";

$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, "iiis", $user_id, $id, $mapel_id, $kelas);

if (mysqli_stmt_execute($stmt)) {
    $berhasil = mysqli_stmt_affected_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    if ($berhasil) {
        echo "<script>
            alert('Nilai berhasil dihapus!');
            window.location.href = 'input_nilai.php?kelas=" .
                urlencode($kelas) . "&mapel_id=" . $mapel_id . "';
        </script>";
    } else {
        echo "<script>
            alert('Nilai tidak ditemukan atau akses ditolak.');
            window.location.href = 'input_nilai.php?kelas=" .
                urlencode($kelas) . "&mapel_id=" . $mapel_id . "';
        </script>";
    }
    exit;
}

$error = mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);
die("Gagal menghapus nilai: " . htmlspecialchars($error));