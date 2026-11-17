<?php
session_start();
require_once "koneksi.php";

// =====================================
// 1. CEK LOGIN SISWA
// =====================================
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'siswa') {
    header("Location: login.php");
    exit;
}

// Ambil ID user dari session
$user_id = (int) ($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);

if ($user_id <= 0) {
    die("ID akun tidak ditemukan. Silakan login kembali.");
}

// =====================================
// 2. AMBIL DATA SISWA
// =====================================
$sql_siswa = "
    SELECT id, nis, nama_siswa, kelas
    FROM siswa
    WHERE user_id = ?
    LIMIT 1
";

$stmt_siswa = mysqli_prepare($koneksi, $sql_siswa);

if (!$stmt_siswa) {
    die("Gagal menyiapkan query siswa.");
}

mysqli_stmt_bind_param($stmt_siswa, "i", $user_id);
mysqli_stmt_execute($stmt_siswa);

$result_siswa = mysqli_stmt_get_result($stmt_siswa);
$siswa = mysqli_fetch_assoc($result_siswa);

mysqli_stmt_close($stmt_siswa);

if (!$siswa) {
    die("Data siswa tidak ditemukan pada akun ini.");
}

$siswa_id = (int) $siswa['id'];

// =====================================
// 3. AMBIL NILAI SISWA
// =====================================
$sql_nilai = "
    SELECT
        nr.id,
        m.nama_mapel,
        nr.nilai_kkm,
        nr.nilai_pengetahuan,
        nr.nilai_keterampilan,
        nr.Keterangan,
        (
            COALESCE(nr.nilai_pengetahuan, 0) +
            COALESCE(nr.nilai_keterampilan, 0)
        ) / 2 AS rata_rata
    FROM nilai_rapor nr
    INNER JOIN mapel m ON nr.mapel_id = m.id
    WHERE nr.siswa_id = ?
    ORDER BY m.nama_mapel ASC
";

$stmt_nilai = mysqli_prepare($koneksi, $sql_nilai);

if (!$stmt_nilai) {
    die("Gagal menyiapkan query nilai.");
}

mysqli_stmt_bind_param($stmt_nilai, "i", $siswa_id);
mysqli_stmt_execute($stmt_nilai);

$result_nilai = mysqli_stmt_get_result($stmt_nilai);

$daftar_nilai = [];
$total_nilai = 0;
$jumlah_nilai = 0;

while ($row = mysqli_fetch_assoc($result_nilai)) {
    $daftar_nilai[] = $row;

    // Hitung rata-rata keseluruhan.
    // Nilai yang belum lengkap tidak dihitung.
    if (
        $row['nilai_pengetahuan'] !== null &&
        $row['nilai_keterampilan'] !== null
    ) {
        $total_nilai += (float) $row['rata_rata'];
        $jumlah_nilai++;
    }
}

mysqli_stmt_close($stmt_nilai);

$rata_rata_keseluruhan = $jumlah_nilai > 0
    ? $total_nilai / $jumlah_nilai
    : 0;

// Fungsi untuk mengamankan tampilan teks
function e($value)
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nilai Siswa - SIACAD SMK</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- DataTables -->
    <link
        href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background-color: #f5f7fb;
            font-family: Arial, sans-serif;
            color: #212529;
        }

        .halaman-nilai {
            padding: 35px 20px;
        }

        .judul-halaman {
            text-align: center;
            margin-bottom: 30px;
        }

        .judul-halaman h1 {
            font-size: 38px;
            font-weight: 600;
        }

        .judul-halaman h3 {
            font-size: 23px;
            margin-top: 8px;
        }

        .kartu-rata {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 22px;
            width: 100%;
            max-width: 360px;
            margin: 25px auto;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
        }

        .kartu-rata h5 {
            margin-bottom: 12px;
        }

        .angka-rata {
            font-size: 52px;
            font-weight: 300;
        }

        .kartu-daftar {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 18px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
        }

        .kartu-daftar h4 {
            margin-bottom: 18px;
        }

        #tabelNilai th {
            background-color: #f0f2f5;
            white-space: nowrap;
        }

        #tabelNilai td {
            vertical-align: middle;
        }

        .nilai-tebal {
            font-weight: bold;
        }

        @media (max-width: 576px) {
            .judul-halaman h1 {
                font-size: 30px;
            }

            .angka-rata {
                font-size: 42px;
            }

            .halaman-nilai {
                padding: 20px 10px;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid halaman-nilai">

    <!-- IDENTITAS SISWA -->
    <div class="judul-halaman">

        <h1>Nilai Siswa</h1>

        <h3><?= e($siswa['nama_siswa']); ?></h3>

        <p class="mb-1">
            NIS: <strong><?= e($siswa['nis']); ?></strong>
        </p>

        <p>
            Kelas: <strong><?= e($siswa['kelas']); ?></strong>
        </p>

    </div>

    <!-- RATA-RATA NILAI -->
    <div class="kartu-rata">

        <h5>Rata-rata Nilai</h5>

        <div class="angka-rata">
            <?= number_format($rata_rata_keseluruhan, 2, '.', ''); ?>
        </div>

    </div>

    <!-- DAFTAR NILAI -->
    <div class="container-fluid px-lg-5">

        <div class="kartu-daftar">

            <h4>Daftar Nilai</h4>

            <div class="table-responsive">

                <table
                    id="tabelNilai"
                    class="table table-bordered table-striped"
                    style="width:100%"
                >

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Mata Pelajaran</th>
                            <th>KKM</th>
                            <th>Pengetahuan</th>
                            <th>Keterampilan</th>
                            <th>Rata-rata</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (count($daftar_nilai) > 0): ?>

                        <?php foreach ($daftar_nilai as $index => $nilai): ?>

                            <tr>
                                <td><?= $index + 1; ?></td>

                                <td>
                                    <?= e($nilai['nama_mapel']); ?>
                                </td>

                                <td>
                                    <?= $nilai['nilai_kkm'] === null
                                        ? '-'
                                        : e($nilai['nilai_kkm']); ?>
                                </td>

                                <td>
                                    <?= $nilai['nilai_pengetahuan'] === null
                                        ? '-'
                                        : e($nilai['nilai_pengetahuan']); ?>
                                </td>

                                <td>
                                    <?= $nilai['nilai_keterampilan'] === null
                                        ? '-'
                                        : e($nilai['nilai_keterampilan']); ?>
                                </td>

                                <td class="nilai-tebal">
                                    <?php
                                    if (
                                        $nilai['nilai_pengetahuan'] !== null &&
                                        $nilai['nilai_keterampilan'] !== null
                                    ) {
                                        echo number_format(
                                            (float) $nilai['rata_rata'],
                                            2,
                                            '.',
                                            ''
                                        );
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>

                                <td>
                                    <?= e($nilai['Keterangan'] ?: '-'); ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <?php if (count($daftar_nilai) === 0): ?>

                <div class="alert alert-info mt-3 mb-0">
                    Belum ada nilai yang diberikan oleh guru.
                </div>

            <?php endif; ?>

            <div class="mt-3">
                <a href="data_teman.php" class="btn btn-secondary">
                    Kembali ke Dashboard
                </a>
            </div>

        </div>

    </div>

</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#tabelNilai').DataTable({
        pageLength: 50,
        lengthMenu: [10, 25, 50, 100],
        ordering: true,
        searching: true,
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            zeroRecords: "Data nilai tidak ditemukan",
            info: "Menampilkan halaman _PAGE_ dari _PAGES_",
            infoEmpty: "Belum ada data nilai",
            infoFiltered: "(difilter dari _MAX_ total data)",
            paginate: {
                previous: "Sebelumnya",
                next: "Selanjutnya"
            }
        }
    });
});
</script>

</body>
</html>