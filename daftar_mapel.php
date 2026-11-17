<?php
include 'koneksi.php';
include 'navbar.php';


$query = mysqli_query($koneksi, "
    SELECT
        mapel.id,
        mapel.kode_mapel,
        mapel.nama_mapel,
        guru.nama AS nama_guru
    FROM mapel
    LEFT JOIN guru ON mapel.nama_guru = guru.id
    ORDER BY mapel.id DESC
");


if (!$query) {
    die("Query gagal: " . mysqli_error($koneksi));
}
?>

<style>
    .content {
        padding: 30px;
    }

    .content h2 {
        margin-bottom: 20px;
        color: #333;
    }

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

    .btn {
        display: inline-block;
        padding: 7px 12px;
        border-radius: 5px;
        text-decoration: none;
        color: white;
        font-size: 13px;
        margin-right: 5px;
    }

    .btn-edit {
        background: #f59e0b;
    }

    .btn-edit:hover {
        background: #d97706;
    }

    .btn-hapus {
        background: #ef4444;
    }

    .btn-hapus:hover {
        background: #dc2626;
    }
</style>

<div class="content">
    

    <h2>Data Mapel</h2>

    <table class="table-users">
        
        <thead>
            <tr>
                <th>ID</th>
                <th>Kode Mapel</th>
                <th>Nama Mapel</th>
                <th>Nama Guru</th>
               
                <th>Aksi</th>
            </tr>
        </thead>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <tbody>

            <?php if (mysqli_num_rows($query) > 0): ?>

                <?php while ($row = mysqli_fetch_assoc($query)) : ?>

                    <tr>
                        <td>
                            <?= htmlspecialchars($row['id']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['kode_mapel']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['nama_mapel']); ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($row['nama_guru']); ?>
                        </td>


                        

                        <td>

                            <!-- Tombol Edit -->
                            <a href="editmapel.php?id=<?= (int) $row['id'] ?>"
                                class="btn btn-edit">
                                 Edit
                           </a>

                            <!-- Tombol Hapus -->
                            <form action="aksi_hapus_mapel.php" method="POST"
      style="display:inline;"
      onsubmit="return confirm('Yakin ingin menghapus data mapel ini?');">

    <input type="hidden" name="id"
           value="<?= (int) $row['id'] ?>">

    <button type="submit" class="btn btn-danger btn-sm">
        Hapus
    </button>
</form>

                        </td>
                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="5" style="text-align: center;">
                        Belum ada data users.
                    </td>
                </tr>

            <?php endif; ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

        </tbody>

    </table>

</div>

<?php include 'footer.php'; ?>
</body>
</html>

