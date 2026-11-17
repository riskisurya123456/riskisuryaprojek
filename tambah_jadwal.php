<?php
session_start();
require_once "koneksi.php";

// ========================================
// CEK LOGIN DAN ROLE ADMIN
// ========================================
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if (($_SESSION['role'] ?? '') !== 'admin') {
    echo "<script>
        alert('Halaman ini hanya dapat diakses oleh admin!');
        window.location.href = 'index.php';
    </script>";
    exit;
}

$pesan = "";
$tipe_pesan = "";

// ========================================
// PROSES SIMPAN JADWAL
// ========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $guru_mengajar_id = (int) ($_POST['guru_mengajar_id'] ?? 0);
    $hari             = $_POST['hari'] ?? '';
    $jam_mulai        = $_POST['jam_mulai'] ?? '';
    $jam_selesai      = $_POST['jam_selesai'] ?? '';
    $ruang            = trim($_POST['ruang'] ?? '');

    $daftar_hari = [
        'Senin', 'Selasa', 'Rabu',
        'Kamis', 'Jumat', 'Sabtu'
    ];

    // Validasi input
    if (
        $guru_mengajar_id <= 0 ||
        !in_array($hari, $daftar_hari, true) ||
        !preg_match('/^\d{2}:\d{2}$/', $jam_mulai) ||
        !preg_match('/^\d{2}:\d{2}$/', $jam_selesai) ||
        $jam_mulai >= $jam_selesai
    ) {
        $pesan = "Data tidak valid. Pastikan jam selesai lebih besar dari jam mulai.";
        $tipe_pesan = "danger";
    } else {

        // Pastikan penugasan guru memang tersedia
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM guru_mengajar WHERE id = ?"
        );

        mysqli_stmt_bind_param($cek, "i", $guru_mengajar_id);
        mysqli_stmt_execute($cek);
        $hasil_cek = mysqli_stmt_get_result($cek);
        $penugasan_ada = mysqli_num_rows($hasil_cek) > 0;
        mysqli_stmt_close($cek);

        if (!$penugasan_ada) {
            $pesan = "Data penugasan guru tidak ditemukan.";
            $tipe_pesan = "danger";
        } else {

            // Cek bentrok kelas pada hari dan waktu yang sama
            // sekaligus cek bentrok guru.
            $cek_bentrok = mysqli_prepare(
                $koneksi,
                "SELECT jp.id
                 FROM jadwal_pelajaran jp
                 JOIN guru_mengajar gm_lama
                    ON gm_lama.id = jp.guru_mengajar_id
                 JOIN guru_mengajar gm_baru
                    ON gm_baru.id = ?
                 WHERE jp.hari = ?
                   AND jp.jam_mulai < ?
                   AND jp.jam_selesai > ?
                   AND (
                        gm_lama.kelas = gm_baru.kelas
                        OR gm_lama.guru_id = gm_baru.guru_id
                   )
                 LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $cek_bentrok,
                "isss",
                $guru_mengajar_id,
                $hari,
                $jam_selesai,
                $jam_mulai
            );

            mysqli_stmt_execute($cek_bentrok);
            $hasil_bentrok = mysqli_stmt_get_result($cek_bentrok);
            $bentrok = mysqli_num_rows($hasil_bentrok) > 0;
            mysqli_stmt_close($cek_bentrok);

            if ($bentrok) {
                $pesan = "Jadwal bentrok! Kelas atau guru sudah memiliki jadwal pada waktu tersebut.";
                $tipe_pesan = "danger";
            } else {

                // Simpan jadwal
                $simpan = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO jadwal_pelajaran
                    (guru_mengajar_id, hari, jam_mulai, jam_selesai, ruang)
                    VALUES (?, ?, ?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $simpan,
                    "issss",
                    $guru_mengajar_id,
                    $hari,
                    $jam_mulai,
                    $jam_selesai,
                    $ruang
                );

                if (mysqli_stmt_execute($simpan)) {
                    mysqli_stmt_close($simpan);

                    echo "<script>
                        alert('Jadwal pelajaran berhasil ditambahkan!');
                        window.location.href = 'daftar_jadwal.php';
                    </script>";
                    exit;
                } else {
                    $pesan = "Gagal menyimpan jadwal: " . mysqli_stmt_error($simpan);
                    $tipe_pesan = "danger";
                    mysqli_stmt_close($simpan);
                }
            }
        }
    }
}

// ========================================
// AMBIL DATA PENUGASAN GURU
// ========================================
$sql_penugasan = "
    SELECT
        gm.id,
        gm.kelas,
        g.nama AS nama_guru,
        m.nama_mapel,
        m.kode_mapel
    FROM guru_mengajar gm
    JOIN guru g ON g.id = gm.guru_id
    JOIN mapel m ON m.id = gm.mapel_id
    ORDER BY gm.kelas ASC, g.nama ASC, m.nama_mapel ASC
";

$data_penugasan = mysqli_query($koneksi, $sql_penugasan);

if (!$data_penugasan) {
    die("Gagal mengambil data penugasan: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Jadwal Pelajaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<?php require_once "navbar.php"; ?>

<div class="container py-4">

    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0">Tambah Jadwal Pelajaran</h4>
                </div>

                <div class="card-body p-4">

                    <?php if ($pesan !== ''): ?>
                        <div class="alert alert-<?= htmlspecialchars($tipe_pesan) ?>">
                            <?= htmlspecialchars($pesan) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (mysqli_num_rows($data_penugasan) === 0): ?>

                        <div class="alert alert-warning">
                            Belum ada penugasan guru. Tambahkan data mengajar
                            terlebih dahulu sebelum membuat jadwal.
                        </div>

                    <?php else: ?>

                        <form method="POST" action="">

                            <div class="mb-3">
                                <label class="form-label">Guru - Mata Pelajaran - Kelas</label>

                                <select name="guru_mengajar_id"
                                        class="form-select"
                                        required>

                                    <option value="">-- Pilih penugasan mengajar --</option>

                                    <?php while ($row = mysqli_fetch_assoc($data_penugasan)): ?>

                                        <option value="<?= (int) $row['id'] ?>"
                                            <?= (isset($_POST['guru_mengajar_id']) &&
                                                (int) $_POST['guru_mengajar_id'] === (int) $row['id'])
                                                ? 'selected' : '' ?>>

                                            <?= htmlspecialchars(
                                                $row['nama_guru'] . ' | ' .
                                                $row['nama_mapel'] . ' | ' .
                                                $row['kelas']
                                            ) ?>

                                        </option>

                                    <?php endwhile; ?>

                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Hari</label>

                                <select name="hari" class="form-select" required>
                                    <option value="">-- Pilih hari --</option>

                                    <?php
                                    foreach (
                                        ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
                                        as $h
                                    ):
                                    ?>
                                        <option value="<?= $h ?>"
                                            <?= (($_POST['hari'] ?? '') === $h) ? 'selected' : '' ?>>
                                            <?= $h ?>
                                        </option>
                                    <?php endforeach; ?>

                                </select>
                            </div>

                            <div class="row">

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jam Mulai</label>
                                    <input type="time"
                                           name="jam_mulai"
                                           class="form-control"
                                           value="<?= htmlspecialchars($_POST['jam_mulai'] ?? '') ?>"
                                           required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jam Selesai</label>
                                    <input type="time"
                                           name="jam_selesai"
                                           class="form-control"
                                           value="<?= htmlspecialchars($_POST['jam_selesai'] ?? '') ?>"
                                           required>
                                </div>

                            </div>

                            <div class="mb-4">
                                <label class="form-label">Ruang Kelas</label>
                                <input type="text"
                                       name="ruang"
                                       class="form-control"
                                       placeholder="Contoh: Lab RPL atau Ruang 10"
                                       maxlength="50"
                                       value="<?= htmlspecialchars($_POST['ruang'] ?? '') ?>">
                            </div>

                            <button type="submit" class="btn btn-primary">
                                Simpan Jadwal
                            </button>

                            <a href="daftar_jadwal.php" class="btn btn-secondary">
                                Lihat Daftar Jadwal
                            </a>

                        </form>

                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>