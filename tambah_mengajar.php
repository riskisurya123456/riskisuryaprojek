<?php
session_start();
require_once 'koneksi.php';

// =====================================
// 1. CEK AKSES ADMIN
// =====================================
if (($_SESSION['role'] ?? '') !== 'admin') {
    die("Akses ditolak. Halaman ini khusus admin.");
}

$pesan = '';
$jenis_pesan = '';

// =====================================
// 2. AMBIL DATA GURU DARI DATABASE
// =====================================
$queryGuru = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM guru ORDER BY nama ASC"
);

if (!$queryGuru) {
    die("Gagal mengambil data guru: " . mysqli_error($koneksi));
}

$guruList = [];

while ($row = mysqli_fetch_assoc($queryGuru)) {
    $guruList[] = $row;
}

// =====================================
// 3. AMBIL DATA MAPEL DARI DATABASE
// =====================================
$queryMapel = mysqli_query(
    $koneksi,
    "SELECT id, kode_mapel, nama_mapel
     FROM mapel
     ORDER BY nama_mapel ASC"
);

if (!$queryMapel) {
    die("Gagal mengambil data mapel: " . mysqli_error($koneksi));
}

$mapelList = [];

while ($row = mysqli_fetch_assoc($queryMapel)) {
    $mapelList[] = $row;
}

// =====================================
// 4. AMBIL KELAS DARI TABEL SISWA
// =====================================
$queryKelas = mysqli_query(
    $koneksi,
    "SELECT DISTINCT kelas
     FROM siswa
     WHERE kelas IS NOT NULL
       AND TRIM(kelas) <> ''
     ORDER BY kelas ASC"
);

if (!$queryKelas) {
    die("Gagal mengambil data kelas: " . mysqli_error($koneksi));
}

$kelasList = [];

while ($row = mysqli_fetch_assoc($queryKelas)) {
    $kelasList[] = $row['kelas'];
}

// =====================================
// 5. PROSES SIMPAN PENUGASAN
// =====================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $guru_id = filter_input(
        INPUT_POST,
        'guru_id',
        FILTER_VALIDATE_INT
    );

    $mapel_id = filter_input(
        INPUT_POST,
        'mapel_id',
        FILTER_VALIDATE_INT
    );

    $kelas = trim($_POST['kelas'] ?? '');

    if (!$guru_id || !$mapel_id || $kelas === '') {

        $pesan = "Semua pilihan wajib diisi.";
        $jenis_pesan = "danger";

    } else {

        // Pastikan guru benar-benar ada
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM guru WHERE id = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "i", $guru_id);
        mysqli_stmt_execute($stmt);

        $guruValid = mysqli_stmt_get_result($stmt)->num_rows > 0;

        mysqli_stmt_close($stmt);

        // Pastikan mapel benar-benar ada
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM mapel WHERE id = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "i", $mapel_id);
        mysqli_stmt_execute($stmt);

        $mapelValid = mysqli_stmt_get_result($stmt)->num_rows > 0;

        mysqli_stmt_close($stmt);

        // Pastikan kelas tersedia pada tabel siswa
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT 1
             FROM siswa
             WHERE kelas = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "s", $kelas);
        mysqli_stmt_execute($stmt);

        $kelasValid = mysqli_stmt_get_result($stmt)->num_rows > 0;

        mysqli_stmt_close($stmt);

        if (!$guruValid) {

            $pesan = "Guru yang dipilih tidak ditemukan.";
            $jenis_pesan = "danger";

        } elseif (!$mapelValid) {

            $pesan = "Mata pelajaran yang dipilih tidak ditemukan.";
            $jenis_pesan = "danger";

        } elseif (!$kelasValid) {

            $pesan = "Kelas tidak ditemukan dalam data siswa.";
            $jenis_pesan = "danger";

        } else {

            // Periksa apakah penugasan sudah ada
            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT id
                 FROM guru_mengajar
                 WHERE guru_id = ?
                   AND mapel_id = ?
                   AND kelas = ?
                 LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "iis",
                $guru_id,
                $mapel_id,
                $kelas
            );

            mysqli_stmt_execute($stmt);

            $sudahAda = mysqli_stmt_get_result($stmt)->num_rows > 0;

            mysqli_stmt_close($stmt);

            if ($sudahAda) {

                $pesan = "Penugasan guru, mata pelajaran, dan kelas tersebut sudah ada.";
                $jenis_pesan = "warning";

            } else {

                // Simpan penugasan ke database
                $stmt = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO guru_mengajar
                     (guru_id, mapel_id, kelas)
                     VALUES (?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "iis",
                    $guru_id,
                    $mapel_id,
                    $kelas
                );

                if (mysqli_stmt_execute($stmt)) {

                    mysqli_stmt_close($stmt);

                    // Redirect agar data tidak tersimpan dua kali
                    header("Location: tambah_mengajar.php?status=berhasil");
                    exit;

                } else {

                    $pesan = "Gagal menyimpan penugasan: " .
                             mysqli_stmt_error($stmt);

                    $jenis_pesan = "danger";

                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}

// =====================================
// 6. PESAN SETELAH BERHASIL MENYIMPAN
// =====================================
if (($_GET['status'] ?? '') === 'berhasil') {
    $pesan = "Penugasan guru berhasil ditambahkan!";
    $jenis_pesan = "success";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tambah Penugasan Mengajar - SIACAD SMK</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f4f7fb;
        }

        .container-utama {
            max-width: 850px;
            margin: 35px auto;
            padding: 0 15px;
        }

        .kartu {
            background: white;
            border: none;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.07);
        }

        .judul {
            color: #2563c7;
            font-weight: 700;
        }

        .form-label {
            font-weight: 600;
        }

        .form-select {
            padding: 12px;
            border-radius: 9px;
        }

        .btn {
            border-radius: 8px;
            padding: 10px 18px;
        }
    </style>
</head>

<body>

<?php require 'navbar.php'; ?>

<div class="container-utama">

    <div class="card kartu">
        <div class="card-body p-4 p-md-5">

            <h2 class="judul mb-2">
                📚 Tambah Penugasan Mengajar
            </h2>

            <p class="text-secondary mb-4">
                Pilih guru, mata pelajaran, dan kelas berdasarkan
                data yang tersedia di database.
            </p>

            <?php if ($pesan !== ''): ?>
                <div class="alert alert-<?= htmlspecialchars($jenis_pesan) ?> alert-dismissible fade show">
                    <?= htmlspecialchars($pesan) ?>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Tutup"
                    ></button>
                </div>
            <?php endif; ?>

            <?php if (
                empty($guruList) ||
                empty($mapelList) ||
                empty($kelasList)
            ): ?>

                <div class="alert alert-warning">
                    <strong>Data belum lengkap!</strong>
                    <ul class="mb-0 mt-2">
                        <?php if (empty($guruList)): ?>
                            <li>Data guru belum tersedia.</li>
                        <?php endif; ?>

                        <?php if (empty($mapelList)): ?>
                            <li>Data mata pelajaran belum tersedia.</li>
                        <?php endif; ?>

                        <?php if (empty($kelasList)): ?>
                            <li>Data kelas belum tersedia pada tabel siswa.</li>
                        <?php endif; ?>
                    </ul>
                </div>

            <?php else: ?>

                <form action="tambah_mengajar.php" method="POST">

                    <!-- PILIH GURU -->
                    <div class="mb-4">
                        <label for="guru_id" class="form-label">
                            Nama Guru
                        </label>

                        <select
                            name="guru_id"
                            id="guru_id"
                            class="form-select"
                            required
                        >
                            <option value="">-- Pilih Guru --</option>

                            <?php foreach ($guruList as $guru): ?>
                                <option
                                    value="<?= (int) $guru['id'] ?>"
                                    <?= (
                                        isset($_POST['guru_id']) &&
                                        (int) $_POST['guru_id'] === (int) $guru['id']
                                    ) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($guru['nama']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>

                        <div class="form-text">
                            Data guru diambil dari tabel guru.
                        </div>
                    </div>

                    <!-- PILIH MAPEL -->
                    <div class="mb-4">
                        <label for="mapel_id" class="form-label">
                            Mata Pelajaran
                        </label>

                        <select
                            name="mapel_id"
                            id="mapel_id"
                            class="form-select"
                            required
                        >
                            <option value="">-- Pilih Mata Pelajaran --</option>

                            <?php foreach ($mapelList as $mapel): ?>
                                <option
                                    value="<?= (int) $mapel['id'] ?>"
                                    <?= (
                                        isset($_POST['mapel_id']) &&
                                        (int) $_POST['mapel_id'] === (int) $mapel['id']
                                    ) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($mapel['kode_mapel']) ?>
                                    -
                                    <?= htmlspecialchars($mapel['nama_mapel']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>

                        <div class="form-text">
                            Data mata pelajaran diambil dari tabel mapel.
                        </div>
                    </div>

                    <!-- PILIH KELAS -->
                    <div class="mb-4">
                        <label for="kelas" class="form-label">
                            Kelas yang Diajar
                        </label>

                        <select
                            name="kelas"
                            id="kelas"
                            class="form-select"
                            required
                        >
                            <option value="">-- Pilih Kelas --</option>

                            <?php foreach ($kelasList as $kelasItem): ?>
                                <option
                                    value="<?= htmlspecialchars($kelasItem) ?>"
                                    <?= (
                                        ($_POST['kelas'] ?? '') === $kelasItem
                                    ) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($kelasItem) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>

                        <div class="form-text">
                            Pilihan kelas diambil otomatis dari tabel siswa.
                        </div>
                    </div>

                    <!-- TOMBOL -->
                    <div class="d-flex flex-wrap gap-2 mt-4">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            💾 Simpan Penugasan
                        </button>

                        <a
                            href="daftar_mengajar.php"
                            class="btn btn-secondary"
                        >
                            Kembali
                        </a>

                    </div>

                </form>

            <?php endif; ?>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>