<?php
require_once 'koneksi.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    die("ID mapel tidak valid.");
}

// Ambil data mapel
$stmt = mysqli_prepare($koneksi, 
    "SELECT * FROM mapel WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Data mapel tidak ditemukan.");
}

// Ambil data guru
$query_guru = mysqli_query($koneksi,
    "SELECT id, nama FROM guru ORDER BY nama ASC"
);

if (!$query_guru) {
    die("Query guru gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Data Mapel</title>

    <link rel="stylesheet" href="style1.css">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>

<?php require 'navbar.php'; ?>

<div class="container mt-4 mb-5" style="max-width: 550px;">
    <div class="card shadow-sm">
        <div class="card-body p-4">

            <h3 class="text-center mb-4">Edit Data Mapel</h3>

            <form action="aksi_edit_mapel.php" method="POST">

                <input type="hidden" name="id"
                       value="<?= (int) $data['id'] ?>">

                <div class="mb-3">
                    <label class="form-label">Kode Mapel</label>
                    <input type="text"
                           name="kodemapel"
                           class="form-control"
                           maxlength="10"
                           value="<?= htmlspecialchars($data['kode_mapel']) ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Mapel</label>
                    <input type="text"
                           name="namamapel"
                           class="form-control"
                           maxlength="100"
                           value="<?= htmlspecialchars($data['nama_mapel']) ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Guru</label>

                    <select name="nama_guru" class="form-select" required>
                        <option value="">-- Pilih Nama Guru --</option>

                        <?php while ($guru = mysqli_fetch_assoc($query_guru)) { ?>
                            <option
                                value="<?= (int) $guru['id'] ?>"
                                <?= ((int) $guru['id'] === (int) $data['nama_guru']) ? 'selected' : '' ?>>

                                <?= htmlspecialchars($guru['nama']) ?>

                            </option>
                        <?php } ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    Simpan Perubahan
                </button>

                <a href="data_mapel.php" class="btn btn-secondary">
                    Kembali
                </a>

            </form>
        </div>
    </div>
</div>

</body>
</html>