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

if ($id <= 0 || $kelas === '' || $mapel_id <= 0 || $user_id <= 0) {
    die("Parameter tidak valid.");
}

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT nr.*, s.nama_siswa, s.nis, s.kelas, m.nama_mapel
     FROM nilai_rapor nr
     JOIN siswa s ON s.id = nr.siswa_id
     JOIN mapel m ON m.id = nr.mapel_id
     JOIN guru g ON g.user_id = ?
     JOIN guru_mengajar gm ON gm.guru_id = g.id
         AND gm.mapel_id = nr.mapel_id
         AND gm.kelas = s.kelas
     WHERE nr.id = ? AND nr.mapel_id = ? AND s.kelas = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "iiis", $user_id, $id, $mapel_id, $kelas);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$data) {
    http_response_code(403);
    die("Nilai tidak ditemukan atau lu tidak memiliki akses untuk mengeditnya.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kkm = $_POST['nilai_kkm'] ?? '';
    $pengetahuan = $_POST['nilai_pengetahuan'] ?? '';
    $keterampilan = $_POST['nilai_keterampilan'] ?? '';
    $keterangan = trim($_POST['keterangan'] ?? '');

    if (
        !is_numeric($kkm) || !is_numeric($pengetahuan) ||
        !is_numeric($keterampilan) ||
        $kkm < 0 || $kkm > 100 ||
        $pengetahuan < 0 || $pengetahuan > 100 ||
        $keterampilan < 0 || $keterampilan > 100
    ) {
        $error = "Semua nilai harus berupa angka antara 0 sampai 100.";
    } else {
        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE nilai_rapor
             SET nilai_kkm = ?, nilai_pengetahuan = ?,
                 nilai_keterampilan = ?, keterangan = ?
             WHERE id = ?"
        );

    mysqli_stmt_bind_param($stmt, "dddsi", $nilai_kkm, $nilai_pengetahuan, $nilai_keterampilan, $keterangan, $id);

        // Gunakan tipe parameter yang benar: d, d, d, s, i
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE nilai_rapor
             SET nilai_kkm = ?, nilai_pengetahuan = ?,
                 nilai_keterampilan = ?, keterangan = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "dddsi",
            $kkm,
            $pengetahuan,
            $keterampilan,
            $keterangan,
            $id
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            echo "<script>
                alert('Nilai berhasil diperbarui!');
                window.location.href = 'input_nilai.php?kelas=" .
                    urlencode($kelas) . "&mapel_id=" . $mapel_id . "';
            </script>";
            exit;
        }

        $error = "Gagal memperbarui nilai.";
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Nilai Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php require "navbar.php"; ?>

<div class="container py-5">
    <div class="card shadow-sm border-0 mx-auto" style="max-width:700px">
        <div class="card-header bg-warning">
            <h4 class="mb-0">✏️ Edit Nilai Siswa</h4>
        </div>

        <div class="card-body p-4">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label">Nama Siswa</label>
                <input class="form-control"
                       value="<?= htmlspecialchars($data['nama_siswa']) ?>" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">NIS</label>
                <input class="form-control"
                       value="<?= htmlspecialchars($data['nis']) ?>" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Mata Pelajaran</label>
                <input class="form-control"
                       value="<?= htmlspecialchars($data['nama_mapel']) ?>" readonly>
            </div>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">KKM</label>
                    <input type="number" name="nilai_kkm" class="form-control"
                           min="0" max="100" required
                           value="<?= htmlspecialchars((string)$data['nilai_kkm']) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Nilai Pengetahuan</label>
                    <input type="number" name="nilai_pengetahuan" class="form-control"
                           min="0" max="100" required
                           value="<?= htmlspecialchars((string)$data['nilai_pengetahuan']) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Nilai Keterampilan</label>
                    <input type="number" name="nilai_keterampilan" class="form-control"
                           min="0" max="100" required
                           value="<?= htmlspecialchars((string)$data['nilai_keterampilan']) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Keterangan Guru</label>
                    <textarea name="keterangan" class="form-control" rows="3"
                              placeholder="Contoh: Baik, rajin, dan aktif di kelas"><?= htmlspecialchars($data['keterangan'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>

                <a href="input_nilai.php?kelas=<?= urlencode($kelas) ?>&mapel_id=<?= $mapel_id ?>"
                   class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>