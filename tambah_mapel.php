<?php
include 'koneksi.php';

// Mengambil data guru yang sudah tersimpan di database
$queryGuru = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM guru ORDER BY nama ASC"
);

if (!$queryGuru) {
    die("Gagal mengambil data guru: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Mapel</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<?php include 'navbar.php'; ?>

<div class="container py-5">

    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow border-0">

                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Form Tambah Mata Pelajaran</h4>
                </div>

                <div class="card-body">

                    <form action="aksi_tambah_mapel.php" method="POST">

                        <!-- Kode Mapel -->
                        <div class="mb-3">
                            <label for="kode_mapel" class="form-label">
                                Kode Mapel
                            </label>

                            <input
                                type="text"
                                name="kode_mapel"
                                id="kode_mapel"
                                class="form-control"
                                placeholder="Contoh: MTK01"
                                required>
                        </div>

                        <!-- Nama Mapel -->
                        <div class="mb-3">
                            <label for="nama_mapel" class="form-label">
                                Nama Mapel
                            </label>

                            <input
                                type="text"
                                name="nama_mapel"
                                id="nama_mapel"
                                class="form-control"
                                placeholder="Contoh: Matematika"
                                required>
                        </div>

                        <!-- Nama Guru dari Database -->
                        <div class="mb-3">
                            <label for="nama_guru" class="form-label">
                                Nama Guru
                            </label>

                            <select
                                name="nama_guru"
                                id="nama_guru"
                                class="form-select"
                                required>

                                <option value="">
                                    -- Pilih Nama Guru --
                                </option>

                                <?php while ($guru = mysqli_fetch_assoc($queryGuru)) { ?>

                                    <option value="<?= (int) $guru['id']; ?>">
                                        <?= htmlspecialchars($guru['nama']); ?>
                                    </option>

                                <?php } ?>

                            </select>
                        </div>

                        <!-- Tombol -->
                        <div class="d-flex justify-content-between">

                            <a href="data_mapel.php"
                               class="btn btn-secondary">
                                Kembali
                            </a>

                            <button type="submit"
                                    class="btn btn-primary">
                                Simpan Mapel
                            </button>

                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>