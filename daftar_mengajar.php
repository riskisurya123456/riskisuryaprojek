<?php
session_start();
require_once 'koneksi.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    die("Akses ditolak. Halaman ini khusus admin.");
}

$sql = "
    SELECT
        gm.id,
        gm.guru_id,
        gm.mapel_id,
        gm.kelas,
        g.nama AS nama_guru,
        m.kode_mapel,
        m.nama_mapel
    FROM guru_mengajar gm
    INNER JOIN guru g ON gm.guru_id = g.id
    INNER JOIN mapel m ON gm.mapel_id = m.id
    ORDER BY g.nama ASC, gm.kelas ASC
";

$result = mysqli_query($koneksi, $sql);

if (!$result) {
    die("Gagal mengambil penugasan: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Daftar Mengajar - SIACAD SMK</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f4f7fb;
        }

        .container-utama {
            max-width: 1150px;
            margin: 35px auto;
            padding: 0 15px;
        }

        .kartu {
            border: none;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.07);
        }
    </style>
</head>

<body>

<?php require 'navbar.php'; ?>

<div class="container-utama">

    <div class="card kartu">
        <div class="card-body p-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

                <div>
                    <h2 class="mb-1">📚 Daftar Mengajar</h2>

                    <p class="text-secondary mb-0">
                        Daftar penugasan guru berdasarkan database.
                    </p>
                </div>

                <a
                    href="tambah_mengajar.php"
                    class="btn btn-primary"
                >
                    + Tambah Penugasan
                </a>

            </div>

            <div class="table-responsive">

                <table class="table table-bordered table-striped table-hover align-middle">

                    <thead class="table-primary">
                        <tr>
                            <th>No.</th>
                            <th>Nama Guru</th>
                            <th>Kode Mapel</th>
                            <th>Mata Pelajaran</th>
                            <th>Kelas</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (mysqli_num_rows($result) > 0): ?>

                        <?php $no = 1; ?>

                        <?php while ($row = mysqli_fetch_assoc($result)): ?>

                            <tr>
                                <td><?= $no++ ?></td>

                                <td>
                                    <?= htmlspecialchars($row['nama_guru']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['kode_mapel']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['nama_mapel']) ?>
                                </td>

                                <td>
                                    <span class="badge bg-primary">
                                        <?= htmlspecialchars($row['kelas']) ?>
                                    </span>
                                </td>
                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5" class="text-center py-4">
                                Belum ada penugasan mengajar.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>