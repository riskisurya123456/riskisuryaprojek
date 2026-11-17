<?php
include 'koneksi.php';

$query = mysqli_query($koneksi, "
    SELECT
        users.id,
        users.username,
        users.role,
        siswa.nama_siswa,
        siswa.kelas
    FROM users
    INNER JOIN siswa ON users.id = siswa.user_id
    WHERE users.role = 'siswa'
    ORDER BY users.id DESC
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

    <title>Data Users - SIACAD SMK</title>

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            margin: 0;
            background: #f8f9fc;
            font-family: Arial, sans-serif;
        }

        /* KONTEN */
        .content {
            padding: 30px;
        }

        .content h2 {
            margin-bottom: 20px;
            color: #333;
        }

        /* TABEL */
        .table-users {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .table-users thead {
            background: #4f46e5;
            color: white;
        }

        .table-users th {
            padding: 15px;
            text-align: left;
            font-size: 15px;
        }

        .table-users td {
            padding: 13px 15px;
            border-bottom: 1px solid #eee;
        }

        .table-users tbody tr:hover {
            background: #f5f5ff;
        }

        /* TOMBOL AKSI */
        .table-users .btn-aksi {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-size: 13px;
            margin-right: 5px;
        }

        .table-users .btn-edit {
            background: #f59e0b;
        }

        .table-users .btn-edit:hover {
            background: #d97706;
            color: white;
        }

        .table-users .btn-hapus {
            background: #ef4444;
        }

        .table-users .btn-hapus:hover {
            background: #dc2626;
            color: white;
        }

        /* RESPONSIVE */
        .table-responsive-custom {
            width: 100%;
            overflow-x: auto;
        }

        @media (max-width: 768px) {
            .content {
                padding: 15px;
            }

            .content h2 {
                font-size: 23px;
            }
        }
    </style>
</head>

<body>

    <!-- NAVBAR: DI LUAR TABEL -->
    <?php include 'navbar.php'; ?>

    <!-- KONTEN DATA SISWA -->
    <div class="content">

        <h2>Data Users</h2>

        <div class="table-responsive-custom">

            <table class="table-users">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (mysqli_num_rows($query) > 0): ?>

                        <?php while ($row = mysqli_fetch_assoc($query)): ?>

                            <tr>
                                <td>
                                    <?= htmlspecialchars((string)$row['id'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['role'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['nama_siswa'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['kelas'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <a
                                        href="edit_siswa.php?id=<?= (int)$row['id']; ?>"
                                        class="btn-aksi btn-edit"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        href="hapus_siswa.php?id=<?= (int)$row['id']; ?>"
                                        class="btn-aksi btn-hapus"
                                        onclick="return confirm('Apakah kamu yakin ingin menghapus user ini?');"
                                    >
                                        Hapus
                                    </a>
                                </td>
                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6" style="text-align: center; padding: 25px;">
                                Belum ada data users.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>
    </div>

    <?php include 'footer.php'; ?>

    <!-- Bootstrap JavaScript -->
    <!-- Hapus script ini jika footer.php sudah memuat Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
