<?php
require_once 'koneksi.php';

// Ambil data siswa beserta username akun
$query = mysqli_query($koneksi, "
    SELECT
        u.username,
        u.role,
        s.nama_siswa AS nama,
        s.kelas
    FROM siswa s
    INNER JOIN users u ON s.user_id = u.id
    ORDER BY s.user_id DESC
");

if (!$query) {
    die("Query gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Siswa - SIACAD SMK</title>

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background-color: #f5f7fb;
            font-family: Arial, sans-serif;
        }

        .content {
            padding: 30px;
        }

        .table-siswa {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .table-siswa th,
        .table-siswa td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        .table-siswa th {
            background-color: #212529;
            color: white;
        }

        .table-siswa tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .table-siswa tbody tr:hover {
            background-color: #e9ecef;
        }

        .card-data {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        @media (max-width: 768px) {
            .content {
                padding: 15px;
            }
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<?php include "navbar.php"; ?>

<div class="content">

    <div class="card-data">

        <h2 class="mb-2">Data Siswa</h2>

        <p class="text-muted">
            Berikut adalah daftar siswa yang terdaftar di SIACAD SMK.
        </p>

        <div class="table-responsive">

            <table class="table-siswa">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (mysqli_num_rows($query) > 0): ?>

                        <?php $no = 1; ?>

                        <?php while ($siswa = mysqli_fetch_assoc($query)): ?>

                            <tr>
                                <td><?= $no++; ?></td>

                                <td>
                                    <?= htmlspecialchars(
                                        $siswa['username'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                                <td>
                                    <span class="badge bg-primary">
                                        <?= htmlspecialchars(
                                            $siswa['role'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $siswa['nama'] ?? '-',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $siswa['kelas'] ?? '-',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>
                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5">
                                Belum ada data siswa.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

        <div class="mt-3">
            <strong>Total siswa:</strong>
            <?= mysqli_num_rows($query); ?> data
        </div>

    </div>

</div>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
