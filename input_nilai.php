<?php
session_start();
require_once "koneksi.php";

// ========================================
// CEK LOGIN DAN ROLE GURU
// ========================================
if (!isset($_SESSION['login']) || ($_SESSION['role'] ?? '') !== 'guru') {
    header("Location: login.php");
    exit;
}

$user_id = (int) ($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);
$kelas = trim($_GET['kelas'] ?? '');
$mapel_id = (int) ($_GET['mapel_id'] ?? 0);

if ($user_id <= 0 || $kelas === '' || $mapel_id <= 0) {
    die("Parameter tidak valid. Silakan kembali ke menu nilai.");
}

// ========================================
// AMBIL DATA GURU
// ========================================
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, nama FROM guru WHERE user_id = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$guru) {
    die("Profil guru tidak ditemukan.");
}

$guru_id = (int) $guru['id'];

// ========================================
// CEK PENUGASAN GURU
// ========================================
$sql = "SELECT
            gm.kelas,
            m.id AS mapel_id,
            m.kode_mapel,
            m.nama_mapel
        FROM guru_mengajar gm
        JOIN mapel m ON m.id = gm.mapel_id
        WHERE gm.guru_id = ?
          AND gm.kelas = ?
          AND gm.mapel_id = ?
        LIMIT 1";

$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, "isi", $guru_id, $kelas, $mapel_id);
mysqli_stmt_execute($stmt);

$penugasan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$penugasan) {
    http_response_code(403);
    die("Akses ditolak. Kelas atau mata pelajaran bukan penugasan lu.");
}

// ========================================
// AMBIL DATA SISWA BERDASARKAN KELAS
// ========================================
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, nis, nama_siswa, kelas
     FROM siswa
     WHERE kelas = ?
     ORDER BY nama_siswa ASC"
);

mysqli_stmt_bind_param($stmt, "s", $kelas);
mysqli_stmt_execute($stmt);

$resultSiswa = mysqli_stmt_get_result($stmt);

$siswaList = [];

while ($s = mysqli_fetch_assoc($resultSiswa)) {
    $siswaList[] = $s;
}

mysqli_stmt_close($stmt);

// ========================================
// AMBIL NILAI SISWA
// Ambil nilai terbaru berdasarkan ID
// ========================================
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT
        nr.id,
        nr.siswa_id,
        nr.nilai_kkm,
        nr.nilai_pengetahuan,
        nr.nilai_keterampilan,
        nr.keterangan
     FROM nilai_rapor nr
     INNER JOIN siswa s ON s.id = nr.siswa_id
     WHERE nr.mapel_id = ?
       AND s.kelas = ?
     ORDER BY nr.id DESC"
);

mysqli_stmt_bind_param($stmt, "is", $mapel_id, $kelas);
mysqli_stmt_execute($stmt);

$resultNilai = mysqli_stmt_get_result($stmt);

$nilaiList = [];

while ($n = mysqli_fetch_assoc($resultNilai)) {
    $sid = (int) $n['siswa_id'];

    // Ambil satu nilai terbaru untuk setiap siswa
    if (!isset($nilaiList[$sid])) {
        $nilaiList[$sid] = $n;
    }
}

mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Daftar Nilai Siswa - SIACAD SMK</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DATATABLES -->
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.css" rel="stylesheet">

    <style>
        body {
            background: #ffffff;
            font-family: Arial, sans-serif;
        }

        .nilai-container {
            max-width: 1140px;
            margin: 25px auto;
            padding: 0 15px;
        }

        .judul-halaman {
            font-size: 32px;
            font-weight: 600;
            color: #172943;
            margin-bottom: 5px;
        }

        .info-kelas {
            color: #64748b;
            margin-bottom: 25px;
        }

        .nilai-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,.08);
        }

        .nilai-card .card-body {
            padding: 16px;
        }

        .table {
            font-size: 14px;
            margin-bottom: 15px;
        }

        .table thead th {
            background: #f8f9fa;
            color: #111827;
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
            padding: 12px 8px;
        }

        .table tbody td {
            vertical-align: middle;
            padding: 9px 8px;
        }

        .nama-siswa {
            font-weight: bold;
            color: #111827;
        }

        .badge-nilai {
            display: inline-block;
            padding: 7px 10px;
            border-radius: 6px;
            color: white;
            font-weight: bold;
            font-size: 13px;
        }

        .badge-pengetahuan {
            background: #0d6efd;
        }

        .badge-keterampilan {
            background: #0d6efd;
        }

        .badge-akhir {
            background: #198754;
        }

        .btn-tambah {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 7px 12px;
            border-radius: 5px;
            font-size: 13px;
            text-decoration: none;
        }

        .btn-tambah:hover {
            background: #0b5ed7;
            color: white;
        }

        .btn-edit {
            background: #ffc107;
            color: #111;
            border: none;
            padding: 6px 10px;
            border-radius: 5px;
            font-size: 13px;
            text-decoration: none;
        }

        .btn-edit:hover {
            background: #e0a800;
            color: #111;
        }

        .btn-hapus {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 5px;
            font-size: 13px;
            text-decoration: none;
        }

        .btn-hapus:hover {
            background: #bb2d3b;
            color: white;
        }

        .dataTables_wrapper {
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .judul-halaman {
                font-size: 25px;
            }
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<?php require "navbar.php"; ?>

<div class="nilai-container">

    <!-- JUDUL -->
    <h2 class="judul-halaman">Daftar Nilai Siswa</h2>

    <div class="info-kelas">
        Kelas:
        <strong><?= htmlspecialchars($penugasan['kelas']) ?></strong>

        &nbsp; | &nbsp;

        Mata Pelajaran:
        <strong><?= htmlspecialchars($penugasan['nama_mapel']) ?></strong>
    </div>

    <div class="card nilai-card">
        <div class="card-body">

            <?php if (isset($_GET['berhasil'])): ?>
                <div class="alert alert-success">
                    Data nilai berhasil diperbarui.
                </div>
            <?php endif; ?>

            <?php if (empty($siswaList)): ?>

                <div class="alert alert-warning">
                    Belum ada siswa di kelas
                    <?= htmlspecialchars($kelas) ?>.
                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table id="tabelNilai"
                           class="table table-bordered table-hover align-middle">

                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>KKM</th>
                                <th>Pengetahuan</th>
                                <th>Keterampilan</th>
                                <th>Nilai Akhir</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($siswaList as $i => $s): ?>

                            <?php
                            $sid = (int) $s['id'];
                            $n = $nilaiList[$sid] ?? [];

                            $kkm = $n['nilai_kkm'] ?? null;
                            $pengetahuan = $n['nilai_pengetahuan'] ?? null;
                            $keterampilan = $n['nilai_keterampilan'] ?? null;
                            $keterangan = $n['keterangan'] ?? '-';

                            $sudahAdaNilai = isset($n['id']);

                            $akhir = (
                                $sudahAdaNilai &&
                                $pengetahuan !== null &&
                                $keterampilan !== null
                            )
                                ? round(((float)$pengetahuan + (float)$keterampilan) / 2)
                                : null;
                            ?>

                            <tr>

                                <!-- NOMOR -->
                                <td class="text-center">
                                    <?= $i + 1 ?>
                                </td>

                                <!-- NAMA SISWA -->
                                <td>
                                    <span class="nama-siswa">
                                        <?= htmlspecialchars($s['nama_siswa']) ?>
                                    </span>
                                </td>

                                <!-- KKM -->
                                <td class="text-center">
                                    <?= $sudahAdaNilai
                                        ? htmlspecialchars((string)$kkm)
                                        : '-' ?>
                                </td>

                                <!-- PENGETAHUAN -->
                                <td class="text-center">
                                    <?php if ($sudahAdaNilai): ?>
                                        <span class="badge-nilai badge-pengetahuan">
                                            <?= htmlspecialchars((string)$pengetahuan) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Belum ada</span>
                                    <?php endif; ?>
                                </td>

                                <!-- KETERAMPILAN -->
                                <td class="text-center">
                                    <?php if ($sudahAdaNilai): ?>
                                        <span class="badge-nilai badge-keterampilan">
                                            <?= htmlspecialchars((string)$keterampilan) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Belum ada</span>
                                    <?php endif; ?>
                                </td>

                                <!-- NILAI AKHIR -->
                                <td class="text-center">
                                    <?php if ($akhir !== null): ?>
                                        <span class="badge-nilai badge-akhir">
                                            <?= $akhir ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Belum ada</span>
                                    <?php endif; ?>
                                </td>

                                <!-- KETERANGAN -->
                                <td>
                                    <?= $sudahAdaNilai
                                        ? htmlspecialchars($keterangan)
                                        : '-' ?>
                                </td>

                                <!-- AKSI -->
                                <td class="text-center text-nowrap">

                                    <?php if ($sudahAdaNilai): ?>

                                        <a
                                            href="edit_nilai.php?id=<?= (int)$n['id'] ?>&kelas=<?= urlencode($kelas) ?>&mapel_id=<?= $mapel_id ?>"
                                            class="btn-edit"
                                        >
                                            Edit
                                        </a>

                                        <a
                                            href="hapus_nilai.php?id=<?= (int)$n['id'] ?>&kelas=<?= urlencode($kelas) ?>&mapel_id=<?= $mapel_id ?>"
                                            class="btn-hapus"
                                            onclick="return confirm('Yakin ingin menghapus nilai siswa ini?')"
                                        >
                                            Hapus
                                        </a>

                                    <?php else: ?>

                                        <a
                                            href="tambah_nilai.php?siswa_id=<?= $sid ?>&kelas=<?= urlencode($kelas) ?>&mapel_id=<?= $mapel_id ?>"
                                            class="btn-tambah"
                                        >
                                            Tambah
                                        </a>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>
                    </table>

                </div>

            <?php endif; ?>

            <a href="nilai_guru.php" class="btn btn-secondary btn-sm mt-2">
                Kembali
            </a>

        </div>
    </div>

</div>

<!-- JQUERY -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DATATABLES -->
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.js"></script>

<script>
$(document).ready(function () {
    $('#tabelNilai').DataTable({
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        ordering: true,
        searching: true,
        language: {
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            search: "Cari:",
            zeroRecords: "Data tidak ditemukan",
            info: "Menampilkan halaman _PAGE_ dari _PAGES_",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            paginate: {
                previous: "Sebelumnya",
                next: "Selanjutnya"
            }
        }
    });
});
</script>

<!-- BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
