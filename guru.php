<?php
// ========================================
// SESSION DAN KONEKSI DATABASE
// ========================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'koneksi.php';

// ========================================
// CEK HAK AKSES ADMIN DAN GURU
// ========================================
$role = $_SESSION['role'] ?? '';

if (!in_array($role, ['admin', 'guru'], true)) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

// ========================================
// AMBIL DATA GURU DAN MATA PELAJARAN
// ========================================
$sql = "
    SELECT
        u.username,
        u.role,
        g.nama,
        GROUP_CONCAT(
            DISTINCT m.nama_mapel
            ORDER BY m.nama_mapel ASC
            SEPARATOR ', '
        ) AS mapel

    FROM guru g

    INNER JOIN users u
        ON u.id = g.user_id

    LEFT JOIN guru_mengajar gm
        ON gm.guru_id = g.id

    LEFT JOIN mapel m
        ON m.id = gm.mapel_id

    GROUP BY
        u.id,
        u.username,
        u.role,
        g.id,
        g.nama

    ORDER BY g.nama ASC
";

$query = mysqli_query($koneksi, $sql);

if (!$query) {
    die("Query gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Daftar Guru - SIACAD SMK</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.css"
          rel="stylesheet">

    <style>
        body {
            background: #f5f7fb;
        }

        .content {
            padding: 30px;
        }

        .judul {
            text-align: center;
            margin-bottom: 28px;
            font-weight: 600;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(0,0,0,.06);
        }

        #tabelGuru thead th {
            background: #eaf0f7;
            color: #222;
            white-space: nowrap;
        }

        #tabelGuru tbody td {
            vertical-align: middle;
        }

        .badge-role {
            background: #dbeafe;
            color: #1d4ed8;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
        }

        .mapel-text {
            color: #333;
            line-height: 1.7;
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<?php require 'navbar.php'; ?>

<div class="content">

    <h2 class="judul">👨‍🏫 Daftar Guru</h2>

    <div class="card">

        <div class="card-body">

            <div class="table-responsive">

                <table id="tabelGuru"
                       class="table table-striped table-bordered align-middle w-100">

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Nama Guru</th>
                            <th>Mata Pelajaran</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php $no = 1; ?>

                    <?php while ($guru = mysqli_fetch_assoc($query)): ?>

                        <tr>

                            <td><?= $no++ ?></td>

                            <td>
                                <?= htmlspecialchars($guru['username'] ?? '-') ?>
                            </td>

                            <td>
                                <span class="badge-role">
                                    <?= htmlspecialchars($guru['role'] ?? '-') ?>
                                </span>
                            </td>

                            <td>
                                <?= htmlspecialchars($guru['nama'] ?? '-') ?>
                            </td>

                            <td>
                                <span class="mapel-text">
                                    <?= htmlspecialchars(
                                        !empty($guru['mapel'])
                                        ? $guru['mapel']
                                        : 'Belum ada mata pelajaran'
                                    ) ?>
                                </span>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>

<script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.js"></script>

<!-- Pengaturan DataTables -->
<script>
$(document).ready(function () {

    $('#tabelGuru').DataTable({

        pageLength: 10,

        lengthMenu: [5, 10, 25, 50, 100],

        language: {

            search: "Cari:",

            lengthMenu: "Tampilkan _MENU_ data per halaman",

            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ guru",

            infoEmpty: "Belum ada data guru",

            zeroRecords: "Data guru tidak ditemukan",

            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        }

    });

});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
