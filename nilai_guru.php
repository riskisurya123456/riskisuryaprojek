<?php
require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (($_SESSION['role'] ?? '') !== 'guru') {
    die("Akses ditolak. Halaman ini khusus guru.");
}

$user_id = (int) ($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);

if ($user_id <= 0) {
    die("ID akun tidak ditemukan. Silakan login kembali.");
}

// ======================================
// 1. AMBIL DATA GURU YANG LOGIN
// ======================================

$stmt = mysqli_prepare($koneksi, "
    SELECT id, nama
    FROM guru
    WHERE user_id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Kesalahan query guru: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$resultGuru = mysqli_stmt_get_result($stmt);
$guru = mysqli_fetch_assoc($resultGuru);

mysqli_stmt_close($stmt);

if (!$guru) {
    die("Data guru tidak ditemukan untuk akun ID " . $user_id .
        ". Periksa kolom user_id pada tabel guru.");
}

$guru_id = (int) $guru['id'];

// ======================================
// 2. AMBIL KELAS DAN MAPEL DARI DATABASE
// ======================================

$sql = "
    SELECT
        gm.kelas,
        m.id AS mapel_id,
        m.kode_mapel,
        m.nama_mapel
    FROM guru_mengajar gm
    INNER JOIN mapel m
        ON m.id = gm.mapel_id
    WHERE gm.guru_id = ?
    ORDER BY gm.kelas ASC, m.nama_mapel ASC
";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    die("Kesalahan query penugasan: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "i", $guru_id);
mysqli_stmt_execute($stmt);

$resultMapel = mysqli_stmt_get_result($stmt);

$kelasList = [];

while ($row = mysqli_fetch_assoc($resultMapel)) {
    $namaKelas = $row['kelas'];

    if (!isset($kelasList[$namaKelas])) {
        $kelasList[$namaKelas] = [];
    }

    $kelasList[$namaKelas][] = $row;
}

mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Masukkan Nilai - SIACAD SMK</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f5f7fb;
        }

        .nilai-container {
            max-width: 1100px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .kelas-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s;
        }

        .kelas-card:hover {
            transform: translateY(-3px);
        }

        .kelas-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e8efff;
            font-size: 27px;
        }

        .kelas-title {
            font-weight: 700;
            margin-top: 15px;
        }
    </style>
</head>

<body>

<?php require 'navbar.php'; ?>

<div class="nilai-container">

    <h2 class="mb-2">Masukkan Nilai Siswa</h2>

    <p class="text-secondary mb-4">
        Selamat datang,
        <strong>
            <?= htmlspecialchars($guru['nama']) ?>
        </strong>.
        Pilih kelas dan mata pelajaran yang ingin diberi nilai.
    </p>

    <?php if (empty($kelasList)): ?>

        <div class="alert alert-warning">

            <h5>Belum Ada Penugasan Mengajar</h5>

            <p class="mb-0">
                Akun guru berhasil ditemukan, tetapi belum ada
                penugasan untuk guru ini pada tabel
                <code>guru_mengajar</code>.

                Silakan periksa data guru_id pada tabel tersebut.
            </p>

        </div>

    <?php else: ?>

        <div class="row g-4">

            <?php foreach ($kelasList as $namaKelas => $daftarMapel): ?>

                <div class="col-md-6 col-lg-4">

                    <div class="card kelas-card h-100">

                        <div class="card-body p-4">

                            <div class="kelas-icon">
                                🎓
                            </div>

                            <h5 class="kelas-title">
                                <?= htmlspecialchars($namaKelas) ?>
                            </h5>

                            <p class="text-secondary">
                                <?= count($daftarMapel) ?>
                                mata pelajaran ditugaskan
                            </p>

                            <form action="input_nilai.php" method="GET">

                                <input
                                    type="hidden"
                                    name="kelas"
                                    value="<?= htmlspecialchars($namaKelas) ?>"
                                >

                                <label class="form-label">
                                    Pilih Mata Pelajaran
                                </label>

                                <select
                                    name="mapel_id"
                                    class="form-select mb-3"
                                    required
                                >

                                    <option value="">
                                        -- Pilih Mata Pelajaran --
                                    </option>

                                    <?php foreach ($daftarMapel as $mapel): ?>

                                        <option
                                            value="<?= (int) $mapel['mapel_id'] ?>"
                                        >
                                            <?= htmlspecialchars(
                                                $mapel['nama_mapel']
                                            ) ?>
                                            (<?= htmlspecialchars(
                                                $mapel['kode_mapel']
                                            ) ?>)
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                                <button
                                    type="submit"
                                    class="btn btn-primary w-100"
                                >
                                    Buka Daftar Siswa
                                </button>

                            </form>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

</body>
</html>
