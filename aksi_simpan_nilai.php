<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['login']) || ($_SESSION['role'] ?? '') !== 'guru') {
    header("Location: login.php");
    exit;
}

$user_id = (int) ($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);
$kelas = trim($_POST['kelas'] ?? '');
$mapel_id = (int) ($_POST['mapel_id'] ?? 0);

if (
    $user_id <= 0 || $kelas === '' || $mapel_id <= 0 ||
    !isset($_POST['siswa_id']) || !is_array($_POST['siswa_id'])
) {
    die("Data tidak lengkap atau tidak valid.");
}

// Cari profil guru
$stmt = mysqli_prepare($koneksi, "SELECT id FROM guru WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$guru) {
    die("Profil guru tidak ditemukan.");
}
$guru_id = (int)$guru['id'];

// Verifikasi penugasan guru
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id FROM guru_mengajar
     WHERE guru_id = ? AND kelas = ? AND mapel_id = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "isi", $guru_id, $kelas, $mapel_id);
mysqli_stmt_execute($stmt);
$penugasan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$penugasan) {
    http_response_code(403);
    die("Akses ditolak. Kelas atau mata pelajaran bukan penugasan lu.");
}

$siswa_ids = array_unique(array_map('intval', $_POST['siswa_id']));

mysqli_begin_transaction($koneksi);

try {
    foreach ($siswa_ids as $sid) {
        if ($sid <= 0) {
            throw new Exception("ID siswa tidak valid.");
        }

        $kkm = $_POST['nilai_kkm'][$sid] ?? '';
        $pengetahuan = $_POST['nilai_pengetahuan'][$sid] ?? '';
        $keterampilan = $_POST['nilai_keterampilan'][$sid] ?? '';
        $keterangan = trim($_POST['keterangan'][$sid] ?? '');

        if (
            $kkm === '' || $pengetahuan === '' || $keterampilan === '' ||
            !is_numeric($kkm) || !is_numeric($pengetahuan) || !is_numeric($keterampilan)
        ) {
            throw new Exception("Nilai harus diisi untuk semua siswa.");
        }

        $kkm = (float)$kkm;
        $pengetahuan = (float)$pengetahuan;
        $keterampilan = (float)$keterampilan;

        if (
            $kkm < 0 || $kkm > 100 ||
            $pengetahuan < 0 || $pengetahuan > 100 ||
            $keterampilan < 0 || $keterampilan > 100
        ) {
            throw new Exception("Nilai harus berada antara 0 sampai 100.");
        }

        // Pastikan siswa benar-benar berada di kelas penugasan
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM siswa WHERE id = ? AND kelas = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "is", $sid, $kelas);
        mysqli_stmt_execute($stmt);
        $siswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$siswa) {
            throw new Exception("Siswa tidak ditemukan di kelas yang ditugaskan.");
        }

        // Cari nilai lama
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM nilai_rapor
             WHERE siswa_id = ? AND mapel_id = ?
             ORDER BY id DESC LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "ii", $sid, $mapel_id);
        mysqli_stmt_execute($stmt);
        $nilaiLama = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if ($nilaiLama) {
            // Perbarui nilai yang sudah ada
            $nilai_id = (int)$nilaiLama['id'];

            $stmt = mysqli_prepare(
                $koneksi,
                "UPDATE nilai_rapor
                 SET nilai_kkm = ?, nilai_pengetahuan = ?,
                     nilai_keterampilan = ?, keterangan = ?
                 WHERE id = ? AND siswa_id = ? AND mapel_id = ?"
            );
            mysqli_stmt_bind_param(
                $stmt,
                "dddsiii",
                $kkm,
                $pengetahuan,
                $keterampilan,
                $keterangan,
                $nilai_id,
                $sid,
                $mapel_id
            );
        } else {
            // Tambahkan nilai baru
            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO nilai_rapor
                 (siswa_id, mapel_id, nilai_kkm, nilai_pengetahuan,
                  nilai_keterampilan, keterangan)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param(
                $stmt,
                "iiddds",
                $sid,
                $mapel_id,
                $kkm,
                $pengetahuan,
                $keterampilan,
                $keterangan
            );
        }

        if (!mysqli_stmt_execute($stmt)) {
            $error = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            throw new Exception("Gagal menyimpan nilai: " . $error);
        }
        mysqli_stmt_close($stmt);
    }

    mysqli_commit($koneksi);

    echo "<script>
        alert('Semua nilai dan keterangan berhasil disimpan!');
        window.location.href = 'input_nilai.php?kelas=" .
            urlencode($kelas) . "&mapel_id=" . $mapel_id . "';
    </script>";
    exit;

} catch (Throwable $e) {
    mysqli_rollback($koneksi);
    die("Gagal menyimpan data: " . htmlspecialchars($e->getMessage()));
}