<?php
require_once 'koneksi.php';
include 'navbar.php';

$query = mysqli_query($koneksi, "SELECT * FROM guru ORDER BY user_id DESC");

if (!$query) {
    die("Query gagal: " . mysqli_error($koneksi));
}

$fields = mysqli_fetch_fields($query);
?>

<style>
.content {
    padding: 30px;
}
.table-guru {
    width: 100%;
    border-collapse: collapse;
    background: white;
}
.table-guru th, .table-guru td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}
.table-guru th {
    background: #212529;
    color: white;
}
.table-guru tr:nth-child(even) {
    background: #f2f2f2;
}
.btn-edit, .btn-hapus {
    display: inline-block;
    padding: 7px 12px;
    color: white;
    text-decoration: none;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
.btn-edit {
    background: #0d6efd;
}
.btn-hapus {
    background: #dc3545;
}
</style>

<div class="content">
    <h2>Data Guru</h2>
    <p>Kelola data guru yang tersimpan di database.</p>

    <div style="overflow-x:auto;">
        <table class="table-guru">
            <thead>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
                <tr>
                    <?php foreach ($fields as $field): ?>
                        <?php if ($field->name !== 'user_id'): ?>
                            <th>
                                <?= htmlspecialchars(
                                    ucwords(str_replace('_', ' ', $field->name))
                                ) ?>
                            </th>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($query) > 0): ?>
                    <?php while ($guru = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <?php foreach ($guru as $kolom => $nilai): ?>
                                <?php if ($kolom !== 'user_id'): ?>
                                    <td>
                                        <?= htmlspecialchars((string)($nilai ?? '-')) ?>
                                    </td>
                                <?php endif; ?>
                            <?php endforeach; ?>

                            <td>
                                <a class="btn-edit"
                                   href="edit_guru.php?id=<?= (int)$guru['user_id'] ?>">
                                    Edit
                                </a>

                                <form action="aksi_hapus_guru.php"
                                      method="POST"
                                      style="display:inline;"
                                      onsubmit="return confirm('Yakin ingin menghapus data guru ini?');">
                                    <input type="hidden" name="user_id"
                                           value="<?= (int)$guru['user_id'] ?>">
                                    <button type="submit" class="btn-hapus">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= count($fields) ?>">
                            Belum ada data guru.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        </table>
    </div>
</div>