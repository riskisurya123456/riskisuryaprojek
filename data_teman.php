<?php
session_start();
require_once "koneksi.php";

// Pastikan siswa sudah login
if (!isset($_SESSION['login']) || ($_SESSION['role'] ?? '') !== 'siswa') {
    header("Location: login.php");
    exit;
}

// Ambil ID akun siswa dari session
$user_id = (int) ($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);

if ($user_id <= 0) {
    die("ID akun tidak ditemukan. Silakan login kembali.");
}

// Ambil data siswa yang sedang login
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT s.*, u.username, u.role
     FROM siswa s
     INNER JOIN users u ON u.id = s.user_id
     WHERE s.user_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);
$siswa_login = mysqli_fetch_assoc($hasil);

if (!$siswa_login) {
    die("Data siswa tidak ditemukan atau belum terhubung dengan akun pengguna.");
}

// Cari kolom kelas yang tersedia di tabel siswa
$kolom_kelas = null;

foreach (['kelas', 'nama_kelas', 'rombel'] as $kolom) {
    if (array_key_exists($kolom, $siswa_login)) {
        $kolom_kelas = $kolom;
        break;
    }
}

if ($kolom_kelas === null) {
    die("Kolom kelas tidak ditemukan pada tabel siswa. Periksa struktur tabel siswa.");
}

$kelas_saya = trim((string) ($siswa_login[$kolom_kelas] ?? ''));

if ($kelas_saya === '') {
    die("Kelas akun kamu belum diisi. Silakan hubungi admin.");
}

// Ambil hanya siswa dari kelas yang sama
$sql = "SELECT u.username, u.role, s.*
        FROM siswa s
        INNER JOIN users u ON u.id = s.user_id
        WHERE s.`$kolom_kelas` = ?
        ORDER BY u.username ASC";

$stmt_teman = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt_teman, "s", $kelas_saya);
mysqli_stmt_execute($stmt_teman);

$result_teman = mysqli_stmt_get_result($stmt_teman);

function e($nilai) {
    return htmlspecialchars((string) ($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Data Teman Sekelas - SIACAD SMK</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
          rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
            font-family: Arial, sans-serif;
        }

        .container-halaman {
            max-width: 1100px;
            margin: 35px auto;
            padding: 0 15px;
        }

        .judul {
            color: #253858;
            font-weight: 700;
        }

        .kartu {
            background: white;
            border: none;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .06);
        }

        .info-kelas {
            background: #e7f1ff;
            color: #084298;
            border-radius: 12px;
            padding: 18px 20px;
        }

        .tabel-data thead th {
            background: #0d6efd;
            color: white;
            padding: 14px;
            white-space: nowrap;
        }

        .tabel-data tbody td {
            padding: 14px;
            vertical-align: middle;
        }

        .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #e7f1ff;
            color: #0d6efd;
            font-weight: bold;
            margin-right: 8px;
        }

        .badge-role {
            background: #e7f1ff;
            color: #084298;
            padding: 7px 12px;
            border-radius: 20px;
            font-weight: 600;
        }
    </style>
</head>

<body>

<?php include "navbar.php"; ?>

<div class="container-halaman">

    <div class="mb-4">
        <h2 class="judul">
            <i class="bi bi-people-fill me-2"></i>Data Teman Sekelas
        </h2>

        <p class="text-secondary">
            Lihat data akun siswa yang berada di kelas yang sama dengan kamu.
        </p>
    </div>

    <div class="info-kelas mb-4">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-mortarboard-fill fs-2"></i>

            <div>
                <div class="small">Kelas kamu</div>
                <h4 class="fw-bold mb-0"><?= e($kelas_saya); ?></h4>
            </div>
        </div>
    </div>

    <div class="kartu p-3 p-md-4">

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h5 class="fw-bold mb-0">Daftar Siswa</h5>

            <span class="badge text-bg-primary rounded-pill px-3 py-2">
                <?= mysqli_num_rows($result_teman); ?> siswa
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover tabel-data align-middle mb-0">

                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Kelas</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (mysqli_num_rows($result_teman) > 0): ?>

                        <?php $no = 1; ?>

                        <?php while ($teman = mysqli_fetch_assoc($result_teman)): ?>

                            <?php
                            // Cari nama berdasarkan kolom yang tersedia
                            $nama_teman = '';

                            foreach (['nama', 'nama_siswa', 'nama_lengkap'] as $kolom_nama) {
                                if (!empty($teman[$kolom_nama])) {
                                    $nama_teman = $teman[$kolom_nama];
                                    break;
                                }
                            }

                            if ($nama_teman === '') {
                                $nama_teman = $teman['username'];
                            }
                            ?>

                            <tr>
                                <td><?= $no++; ?></td>

                                <td>
                                    <span class="avatar">
                                        <?= e(strtoupper(substr($nama_teman, 0, 1))); ?>
                                    </span>
                                    <?= e($nama_teman); ?>
                                </td>

                                <td><?= e($teman['username']); ?></td>

                                <td>
                                    <span class="badge-role">
                                        <?= e($teman['role']); ?>
                                    </span>
                                </td>

                                <td><?= e($teman[$kolom_kelas]); ?></td>
                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">
                                Belum ada siswa di kelas ini.
                            </td>
                        </tr>

                    <?php endif; ?>
                </tbody>

            </table>
        </div>

    </div>

    <div class="text-secondary small mt-3">
        <i class="bi bi-shield-lock me-1"></i>
        Halaman ini hanya menampilkan siswa yang terdaftar di kelas kamu.
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
