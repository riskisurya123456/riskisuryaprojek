<?php
session_start();
require_once 'koneksi.php';

// Ambil ID siswa dari URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    echo "<script>alert('ID siswa tidak valid!'); window.location='crud_edit_siswa.php';</script>";
    exit;
}

// Ambil data siswa dan akun
$query = mysqli_prepare($koneksi, "
    SELECT
        users.id,
        users.username,
        users.role,
        siswa.user_id,
        siswa.nis,
        siswa.nama_siswa,
        siswa.kelas,
        siswa.jenis_kelamin
    FROM users
    INNER JOIN siswa ON users.id = siswa.user_id
    WHERE users.id = ? AND users.role = 'siswa'
");

mysqli_stmt_bind_param($query, "i", $id);
mysqli_stmt_execute($query);

$result = mysqli_stmt_get_result($query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "<script>alert('Data siswa tidak ditemukan!'); window.location='crud_edit_siswa.php';</script>";
    exit;
}

// Escape data untuk ditampilkan dalam HTML
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Siswa</title>

    <link rel="stylesheet" href="style1.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body>

<?php include 'navbar.php'; ?>

<div class="container mt-4 mb-5" style="max-width: 600px;">
    <div class="card shadow-sm">
        <div class="card-body p-4">

            <h2 class="mb-4">Form Edit Siswa</h2>
            <h4 class="mb-4">Data Profil Siswa</h4>

            <form action="aksi_edit_siswa.php" method="POST">

                <input type="hidden" name="id"
                       value="<?= e($data['id']) ?>">

                <div class="mb-3">
                    <label class="form-label">NIS</label>
                    <input type="number" name="nis"
                           class="form-control"
                           value="<?= e($data['nis']) ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Lengkap Siswa</label>
                    <input type="text" name="nama_siswa"
                           class="form-control"
                           value="<?= e($data['nama_siswa']) ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kelas</label>
                    <input type="text" name="kelas"
                           class="form-control"
                           value="<?= e($data['kelas']) ?>"
                           required>
                </div>

                <div class="mb-4">
                    <label class="form-label d-block">Jenis Kelamin</label>

                    <div class="form-check">
                        <input type="radio" name="jenis_kelamin"
                               id="L" value="L"
                               class="form-check-input"
                               <?= $data['jenis_kelamin'] === 'L' ? 'checked' : '' ?>
                               required>
                        <label class="form-check-label" for="L">
                            Laki-laki
                        </label>
                    </div>

                    <div class="form-check">
                        <input type="radio" name="jenis_kelamin"
                               id="P" value="P"
                               class="form-check-input"
                               <?= $data['jenis_kelamin'] === 'P' ? 'checked' : '' ?>
                               required>
                        <label class="form-check-label" for="P">
                            Perempuan
                        </label>
                    </div>
                </div>

                <hr>

                <h4 class="mb-3">Data Kredensial Akun</h4>

                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username"
                           class="form-control"
                           value="<?= e($data['username']) ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="password"
                           class="form-control"
                           placeholder="Kosongkan jika tidak diganti">
                    <small class="text-muted">
                        Isi hanya jika ingin mengganti password.
                    </small>
                </div>

                <input type="hidden" name="role" value="siswa">

                <button type="submit" class="btn btn-primary">
                    Simpan Perubahan
                </button>

                <a href="crud_edit_siswa.php"
                   class="btn btn-secondary">
                    Kembali
                </a>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>