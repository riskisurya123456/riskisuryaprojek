<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) ($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);

if ($user_id <= 0) {
    die("ID pengguna tidak ditemukan. Silakan login kembali.");
}

$pesan = "";
$jenis_pesan = "";

// Ambil data akun saat ini
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, username, password, role
     FROM users
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    die("Data akun tidak ditemukan.");
}

// Proses perubahan pengaturan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username_baru = trim($_POST['username'] ?? '');
    $password_lama = $_POST['password_lama'] ?? '';
    $password_baru = $_POST['password_baru'] ?? '';
    $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';

    if ($username_baru === '') {

        $pesan = "Username tidak boleh kosong.";
        $jenis_pesan = "danger";

    } elseif (strlen($username_baru) > 50) {

        $pesan = "Username maksimal 50 karakter.";
        $jenis_pesan = "danger";

    } else {

        // Periksa apakah username digunakan akun lain
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM users
             WHERE username = ? AND id != ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param($cek, "si", $username_baru, $user_id);
        mysqli_stmt_execute($cek);

        $hasil_cek = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($hasil_cek) > 0) {

            $pesan = "Username sudah digunakan akun lain.";
            $jenis_pesan = "danger";

        } else {

            // Password hanya diganti jika kolom password diisi
            if (
                $password_lama !== '' ||
                $password_baru !== '' ||
                $konfirmasi_password !== ''
            ) {

                if (
                    $password_lama === '' ||
                    $password_baru === '' ||
                    $konfirmasi_password === ''
                ) {

                    $pesan = "Isi semua kolom password untuk mengganti password.";
                    $jenis_pesan = "danger";

                } else {

                    // Mendukung hash password dan akun lama yang masih plaintext
                    $password_valid = password_verify(
                        $password_lama,
                        $user['password']
                    ) || hash_equals(
                        (string) $user['password'],
                        (string) $password_lama
                    );

                    if (!$password_valid) {

                        $pesan = "Password lama yang kamu masukkan salah.";
                        $jenis_pesan = "danger";

                    } elseif (strlen($password_baru) < 8) {

                        $pesan = "Password baru minimal 8 karakter.";
                        $jenis_pesan = "danger";

                    } elseif ($password_baru !== $konfirmasi_password) {

                        $pesan = "Konfirmasi password baru tidak cocok.";
                        $jenis_pesan = "danger";

                    } else {

                        $password_hash = password_hash(
                            $password_baru,
                            PASSWORD_DEFAULT
                        );

                        $update = mysqli_prepare(
                            $koneksi,
                            "UPDATE users
                             SET username = ?, password = ?
                             WHERE id = ?"
                        );

                        mysqli_stmt_bind_param(
                            $update,
                            "ssi",
                            $username_baru,
                            $password_hash,
                            $user_id
                        );

                        if (mysqli_stmt_execute($update)) {

                            $_SESSION['username'] = $username_baru;

                            echo "<script>
                                alert('Username dan password berhasil diperbarui!');
                                window.location.href = 'pengaturan_profil.php';
                            </script>";
                            exit;

                        } else {

                            $pesan = "Gagal memperbarui akun.";
                            $jenis_pesan = "danger";
                        }
                    }
                }

            } else {

                // Hanya mengubah username
                $update = mysqli_prepare(
                    $koneksi,
                    "UPDATE users
                     SET username = ?
                     WHERE id = ?"
                );

                mysqli_stmt_bind_param(
                    $update,
                    "si",
                    $username_baru,
                    $user_id
                );

                if (mysqli_stmt_execute($update)) {

                    $_SESSION['username'] = $username_baru;

                    echo "<script>
                        alert('Username berhasil diperbarui!');
                        window.location.href = 'pengaturan_profil.php';
                    </script>";
                    exit;

                } else {

                    $pesan = "Gagal memperbarui username.";
                    $jenis_pesan = "danger";
                }
            }
        }
    }
}

// Ambil username terbaru untuk ditampilkan
$username_tampil = $_SESSION['username'] ?? $user['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Pengaturan Profil - SIACAD SMK</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f4f6f9;
            font-family: Arial, sans-serif;
        }

        .container-pengaturan {
            max-width: 800px;
            margin: 35px auto;
            padding: 0 15px;
        }

        .kartu-pengaturan {
            background: white;
            border: none;
            border-radius: 16px;
            box-shadow: 0 5px 22px rgba(0, 0, 0, .07);
        }

        .header-pengaturan {
            background: linear-gradient(135deg, #0d6efd, #084298);
            color: white;
            padding: 25px;
            border-radius: 16px 16px 0 0;
        }

        .isi-pengaturan {
            padding: 28px;
        }

        .form-control {
            padding: 11px 13px;
            border-radius: 9px;
        }

        .btn {
            border-radius: 9px;
            padding: 10px 18px;
        }

        .ikon-judul {
            font-size: 22px;
            margin-right: 8px;
        }
    </style>
</head>

<body>

<?php require "navbar.php"; ?>

<div class="container-pengaturan">

    <div class="mb-4">
        <h2 class="fw-bold text-dark">
            <i class="bi bi-gear-fill text-primary me-2"></i>
            Pengaturan Profil
        </h2>

        <p class="text-secondary">
            Kelola username dan password akun SIACAD SMK kamu.
        </p>
    </div>

    <div class="kartu-pengaturan">

        <div class="header-pengaturan">
            <h4 class="fw-bold mb-1">
                <i class="bi bi-person-lock me-2"></i>
                Pengaturan Akun
            </h4>

            <p class="mb-0">
                Role akun: <?= htmlspecialchars(ucfirst($user['role'])); ?>
            </p>
        </div>

        <div class="isi-pengaturan">

            <?php if ($pesan !== ''): ?>
                <div class="alert alert-<?= htmlspecialchars($jenis_pesan); ?>">
                    <?= htmlspecialchars($pesan); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">

                <h5 class="fw-bold mb-3">
                    <i class="bi bi-person-circle ikon-judul text-primary"></i>
                    Ubah Username
                </h5>

                <div class="mb-4">
                    <label for="username" class="form-label">
                        Username Baru
                    </label>

                    <input
                        type="text"
                        name="username"
                        id="username"
                        class="form-control"
                        maxlength="50"
                        value="<?= htmlspecialchars(
                            $_POST['username'] ?? $username_tampil,
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>"
                        required
                    >
                </div>

                <hr class="my-4">

                <h5 class="fw-bold mb-2">
                    <i class="bi bi-shield-lock ikon-judul text-primary"></i>
                    Ubah Password
                </h5>

                <p class="text-secondary small">
                    Kosongkan semua kolom password jika kamu hanya ingin
                    mengganti username.
                </p>

                <div class="mb-3">
                    <label for="password_lama" class="form-label">
                        Password Lama
                    </label>

                    <input
                        type="password"
                        name="password_lama"
                        id="password_lama"
                        class="form-control"
                        autocomplete="current-password"
                    >
                </div>

                <div class="mb-3">
                    <label for="password_baru" class="form-label">
                        Password Baru
                    </label>

                    <input
                        type="password"
                        name="password_baru"
                        id="password_baru"
                        class="form-control"
                        minlength="8"
                        autocomplete="new-password"
                    >

                    <div class="form-text">
                        Minimal 8 karakter.
                    </div>
                </div>

                <div class="mb-4">
                    <label for="konfirmasi_password" class="form-label">
                        Konfirmasi Password Baru
                    </label>

                    <input
                        type="password"
                        name="konfirmasi_password"
                        id="konfirmasi_password"
                        class="form-control"
                        minlength="8"
                        autocomplete="new-password"
                    >
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>
                    Simpan Perubahan
                </button>

                <a href="profil.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Kembali ke Profil
                </a>

            </form>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
