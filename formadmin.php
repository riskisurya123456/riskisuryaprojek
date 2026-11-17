<?php
session_start();
require_once "koneksi.php";

$pesan = "";
$jenis_pesan = "";

// Proses simpan data
if (isset($_POST['simpan'])) {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Role default untuk form ini adalah admin
    $role = "admin";

    if ($username === '' || $password === '') {

        $pesan = "Username dan password wajib diisi!";
        $jenis_pesan = "danger";

    } else {

        // Periksa apakah username sudah digunakan
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM users WHERE username = ?"
        );

        mysqli_stmt_bind_param($cek, "s", $username);
        mysqli_stmt_execute($cek);

        $hasil_cek = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($hasil_cek) > 0) {

            $pesan = "Username sudah digunakan! Silakan gunakan username lain.";
            $jenis_pesan = "danger";

        } else {

            // Hash password sebelum disimpan
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Simpan username, password, dan role
            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO users (username, password, role)
                 VALUES (?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sss",
                $username,
                $password_hash,
                $role
            );

            if (mysqli_stmt_execute($stmt)) {

                echo "<script>
                    alert('Data user berhasil ditambahkan!');
                    window.location.href = 'daftar_user.php';
                </script>";
                exit;

            } else {

                $pesan = "Gagal menyimpan data: " . mysqli_stmt_error($stmt);
                $jenis_pesan = "danger";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tambah User - SIACAD SMK</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body>

<?php require_once "navbar.php"; ?>

<div class="container mt-5 mb-5">

    <h3 class="text-center mb-4">
        Form Tambah User (Admin)
    </h3>

    <div class="card shadow-sm">
        <div class="card-body p-4">

            <h4 class="mb-4">Data Kredensial Akun</h4>

            <?php if ($pesan !== ''): ?>
                <div class="alert alert-<?= htmlspecialchars($jenis_pesan); ?>">
                    <?= htmlspecialchars($pesan); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">

                <div class="mb-3">
                    <label for="username" class="form-label">
                        Username Akun
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-control"
                        placeholder="Contoh: ujian123"
                        maxlength="50"
                        value="<?= htmlspecialchars($_POST['username'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">
                        Password Akun
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Masukkan password"
                        required
                    >
                </div>

                <div class="alert alert-info">
                    <strong>Role akun:</strong> Admin
                    <br>
                    Form ini membuat akun admin. Form ini tidak otomatis
                    membuat data guru atau siswa.
                </div>

                <button
                    type="submit"
                    name="simpan"
                    value="1"
                    class="btn btn-primary"
                >
                    Simpan Data
                </button>

                <a href="daftar_user.php" class="btn btn-secondary">
                    Kembali
                </a>

            </form>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
