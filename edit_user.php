<?php
session_start();

// Hanya admin yang boleh mengedit user (sesuaikan dengan sistem login Anda)
if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

// Token CSRF (wajib dicek juga di aksi_edit_user.php)
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$koneksi = mysqli_connect("localhost", "root", "", "db_siacad_smk");
mysqli_set_charset($koneksi, "utf8mb4");

// Helper: escape output agar aman dari XSS
function e($nilai): string
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}

// Helper: ambil satu baris dengan prepared statement (aman dari SQL injection)
function ambilSatu(mysqli $koneksi, string $sql, int $id): ?array
{
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $hasil = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($hasil) ?: null;
}

// Validasi ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    exit('ID tidak valid.');
}

$user = ambilSatu($koneksi, "SELECT id, username, role FROM users WHERE id = ?", $id);
if (!$user) {
    http_response_code(404);
    exit('User tidak ditemukan.');
}

$role = $user['role'];
$data = [];

if ($role === 'siswa') {
    $data = ambilSatu($koneksi, "SELECT * FROM siswa WHERE user_id = ?", $id) ?? [];
} elseif ($role === 'guru') {
    $data = ambilSatu($koneksi, "SELECT * FROM guru WHERE user_id = ?", $id) ?? [];
}

$jk = $data['jenis_kelamin'] ?? 'L';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data <?= e(ucfirst($role)) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow mx-auto" style="max-width: 600px;">
        <div class="card-body p-4">

            <h3 class="mb-4">Edit Data <?= e(ucfirst($role)) ?></h3>

            <form action="aksi_edit_user.php" method="POST">
                <input type="hidden" name="id" value="<?= (int) $id ?>">
                <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">

                <!-- Field umum: semua role -->
                <div class="mb-3">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control"
                           value="<?= e($user['username']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">Password Baru</label>
                    <input type="password" id="password" name="password" class="form-control"
                           autocomplete="new-password">
                    <small class="text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                </div>

                <?php if ($role === 'siswa'): ?>

                    <div class="mb-3">
                        <label class="form-label" for="nis">NIS</label>
                        <input type="text" id="nis" name="nis" class="form-control"
                               value="<?= e($data['nis'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="nama_siswa">Nama</label>
                        <input type="text" id="nama_siswa" name="nama_siswa" class="form-control"
                               value="<?= e($data['nama_siswa'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="kelas">Kelas</label>
                        <input type="text" id="kelas" name="kelas" class="form-control"
                               value="<?= e($data['kelas'] ?? '') ?>" required>
                    </div>

                <?php elseif ($role === 'guru'): ?>

                    <div class="mb-3">
                        <label class="form-label" for="nip">NIP</label>
                        <input type="text" id="nip" name="nip" class="form-control"
                               value="<?= e($data['nip'] ?? '') ?>" placeholder="NIP" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="nama">Nama</label>
                        <input type="text" id="nama" name="nama" class="form-control"
                               value="<?= e($data['nama'] ?? '') ?>" placeholder="Nama Lengkap" required>
                    </div>

                <?php endif; ?>

                <?php if ($role === 'siswa' || $role === 'guru'): ?>
                    <div class="mb-3">
                        <label class="form-label" for="jenis_kelamin">Jenis Kelamin</label>
                        <select id="jenis_kelamin" name="jenis_kelamin" class="form-select" required>
                            <option value="L" <?= $jk === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="P" <?= $jk === 'P' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="form.php" class="btn btn-secondary">Batal</a>
            </form>

        </div>
    </div>
</div>

</body>
</html>