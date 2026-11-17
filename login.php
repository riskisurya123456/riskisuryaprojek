<?php
// =============================================
// 1. SESSION DAN KONEKSI
// =============================================

ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/koneksi.php";

if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
    die("ERROR: Koneksi database tidak ditemukan. Periksa file koneksi.php");
}

$pesan_login = "";
$pesan_register = "";
$tampilkan_register = false;


// =============================================
// 2. PROSES LOGIN
// =============================================

if (isset($_POST['login'])) {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {

        $pesan_login = "Username dan password wajib diisi!";

    } else {

        $sql = "SELECT id, username, password, role
                FROM users
                WHERE username = ?";

        $stmt = mysqli_prepare($koneksi, $sql);

        if (!$stmt) {
            die("Query login gagal: " . mysqli_error($koneksi));
        }

        mysqli_stmt_bind_param($stmt, "s", $username);

        if (!mysqli_stmt_execute($stmt)) {
            die("Eksekusi login gagal: " . mysqli_stmt_error($stmt));
        }

        mysqli_stmt_bind_result(
            $stmt,
            $id,
            $db_username,
            $db_password,
            $db_role
        );

        $ada_user = mysqli_stmt_fetch($stmt);

        mysqli_stmt_close($stmt);

        if ($ada_user) {

            $benar = password_verify($password, $db_password);

            // Dukungan password lama plaintext
            if (
                !$benar &&
                hash_equals((string)$db_password, (string)$password)
            ) {

                $benar = true;

                $hash_baru = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $update = mysqli_prepare(
                    $koneksi,
                    "UPDATE users SET password = ? WHERE id = ?"
                );

                if (!$update) {
                    die("Update password gagal: " . mysqli_error($koneksi));
                }

                mysqli_stmt_bind_param(
                    $update,
                    "si",
                    $hash_baru,
                    $id
                );

                mysqli_stmt_execute($update);
                mysqli_stmt_close($update);
            }

            if ($benar) {

                $role = strtolower(trim($db_role));

                if (!in_array($role, ['admin', 'guru', 'siswa'], true)) {

                    $pesan_login = "Role akun tidak valid!";

                } else {

                    session_regenerate_id(true);

                    $_SESSION['login'] = true;
                    $_SESSION['id'] = $id;
                    $_SESSION['username'] = $db_username;
                    $_SESSION['role'] = $role;

                    if ($role === 'admin') {
                        header("Location: daftar_user.php");
                        exit;
                    }

                    if ($role === 'guru') {
                        header("Location: guru.php");
                        exit;
                    }

                    if ($role === 'siswa') {
                        header("Location: data_teman.php");
                        exit;
                    }
                }

            } else {

                $pesan_login = "Password salah!";

            }

        } else {

            $pesan_login = "Username tidak ditemukan!";

        }
    }
}


// =============================================
// 3. PROSES REGISTER SISWA
// =============================================

if (isset($_POST['register'])) {

    $tampilkan_register = true;

    $nis = trim($_POST['nis'] ?? '');
    $nama_siswa = trim($_POST['nama_siswa'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $role = 'siswa';

    if (
        $nis === '' ||
        $nama_siswa === '' ||
        $kelas === '' ||
        $username === '' ||
        $password === '' ||
        !in_array($jenis_kelamin, ['L', 'P'], true)
    ) {

        $pesan_register = "Semua data wajib diisi!";

    } elseif (strlen($password) < 6) {

        $pesan_register = "Password minimal 6 karakter!";

    } else {

        // CEK NIS
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT COUNT(*) FROM siswa WHERE nis = ?"
        );

        if (!$stmt) {
            die("Query NIS gagal: " . mysqli_error($koneksi));
        }

        mysqli_stmt_bind_param($stmt, "s", $nis);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $jumlah_nis);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        // CEK USERNAME
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT COUNT(*) FROM users WHERE username = ?"
        );

        if (!$stmt) {
            die("Query username gagal: " . mysqli_error($koneksi));
        }

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $jumlah_username);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($jumlah_nis > 0) {

            $pesan_register = "NIS sudah terdaftar!";

        } elseif ($jumlah_username > 0) {

            $pesan_register = "Username sudah digunakan!";

        } else {

            mysqli_begin_transaction($koneksi);

            $stmt = null;

            try {

                $password_hash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // SIMPAN USER
                $stmt = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO users
                    (username, password, role, created_at)
                    VALUES (?, ?, ?, NOW())"
                );

                if (!$stmt) {
                    throw new Exception(mysqli_error($koneksi));
                }

                mysqli_stmt_bind_param(
                    $stmt,
                    "sss",
                    $username,
                    $password_hash,
                    $role
                );

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception(mysqli_stmt_error($stmt));
                }

                $user_id = mysqli_insert_id($koneksi);

                mysqli_stmt_close($stmt);
                $stmt = null;

                // SIMPAN SISWA
                $stmt = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO siswa
                    (nis, nama_siswa, kelas, jenis_kelamin, user_id)
                    VALUES (?, ?, ?, ?, ?)"
                );

                if (!$stmt) {
                    throw new Exception(mysqli_error($koneksi));
                }

                mysqli_stmt_bind_param(
                    $stmt,
                    "ssssi",
                    $nis,
                    $nama_siswa,
                    $kelas,
                    $jenis_kelamin,
                    $user_id
                );

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception(mysqli_stmt_error($stmt));
                }

                mysqli_stmt_close($stmt);
                $stmt = null;

                mysqli_commit($koneksi);

                $pesan_register =
                    "Pendaftaran berhasil! Silakan login.";

                $tampilkan_register = false;

                $_POST = [];

            } catch (Throwable $e) {

                mysqli_rollback($koneksi);

                if ($stmt instanceof mysqli_stmt) {
                    mysqli_stmt_close($stmt);
                }

                error_log($e->getMessage());

                $pesan_register =
                    "Pendaftaran gagal: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login dan Register Siswa</title>

<style>

* {
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    margin: 0;
    min-height: 100vh;
    background: #f2f2f2;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 25px 12px;
}

.container {
    width: 100%;
    max-width: 440px;
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,.12);
}

h2 {
    text-align: center;
    margin: 0 0 25px;
}

h3 {
    text-align: center;
    font-size: 21px;
    margin: 25px 0 18px;
}

label {
    display: block;
    margin: 12px 0 7px;
    font-size: 14px;
}

input, select {
    width: 100%;
    padding: 12px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    margin-bottom: 10px;
    background: white;
}

input:focus, select:focus {
    outline: 2px solid #b6d4fe;
    border-color: #86b7fe;
}

button {
    width: 100%;
    padding: 12px;
    border: 0;
    border-radius: 6px;
    background: #0d6efd;
    color: white;
    font-size: 15px;
    cursor: pointer;
    margin-top: 10px;
}

button:hover {
    background: #0b5ed7;
}

.pesan {
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 6px;
    background: #fff0f0;
    color: #b02a37;
    font-size: 14px;
    text-align: center;
    overflow-wrap: anywhere;
}

.pesan.sukses {
    background: #d1e7dd;
    color: #0f5132;
}

.switch {
    text-align: center;
    margin-top: 20px;
    font-size: 14px;
}

.switch a {
    color: #0d6efd;
    text-decoration: none;
    cursor: pointer;
}

.switch a:hover {
    text-decoration: underline;
}

.pemisah {
    border: 0;
    border-top: 1px solid #ddd;
    margin: 22px 0;
}

.role {
    background: #e9ecef;
}

</style>

</head>

<body>

<div class="container">

    <!-- FORM LOGIN -->

    <div id="loginForm"
         style="<?= $tampilkan_register ? 'display:none' : 'display:block' ?>">

        <h2>Login</h2>

        <?php if ($pesan_login !== ''): ?>
            <div class="pesan">
                <?= htmlspecialchars($pesan_login, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($pesan_register !== '' && !$tampilkan_register): ?>
            <div class="pesan sukses">
                <?= htmlspecialchars($pesan_register, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <label>Username</label>

            <input type="text"
                   name="username"
                   placeholder="Masukkan username"
                   required>

            <label>Password</label>

            <input type="password"
                   name="password"
                   placeholder="Masukkan password"
                   required>

            <button type="submit" name="login">
                Login
            </button>

        </form>

        <div class="switch">
            Belum punya akun?
            <a href="#" onclick="showRegister(); return false;">
                Register Siswa
            </a>
        </div>

    </div>


    <!-- FORM REGISTER SISWA -->

    <div id="registerForm"
         style="<?= $tampilkan_register ? 'display:block' : 'display:none' ?>">

        <h2>Form Register Siswa</h2>

        <?php if ($pesan_register !== ''): ?>
            <div class="pesan <?= !$tampilkan_register ? 'sukses' : '' ?>">
                <?= htmlspecialchars($pesan_register, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <h3>Data Profil Siswa</h3>

            <label>Nomor Induk Siswa (NIS)</label>

            <input type="text"
                   name="nis"
                   placeholder="Contoh: 12345678"
                   value="<?= htmlspecialchars($_POST['nis'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   required>

            <label>Nama Lengkap Siswa</label>

            <input type="text"
                   name="nama_siswa"
                   placeholder="Masukkan nama lengkap"
                   value="<?= htmlspecialchars($_POST['nama_siswa'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   required>

            <label>Kelas Siswa</label>

            <input type="text"
                   name="kelas"
                   placeholder="Contoh: XI RPL 1"
                   value="<?= htmlspecialchars($_POST['kelas'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   required>

            <label>Jenis Kelamin</label>

            <select name="jenis_kelamin" required>
                <option value="">-- Pilih Jenis Kelamin --</option>

                <option value="L"
                    <?= ($_POST['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' ?>>
                    Laki-laki
                </option>

                <option value="P"
                    <?= ($_POST['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>
                    Perempuan
                </option>
            </select>

            <hr class="pemisah">

            <h3>Data Kredensial Akun</h3>

            <label>Username</label>

            <input type="text"
                   name="username"
                   placeholder="Buat username"
                   value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   required>

            <label>Password</label>

            <input type="password"
                   name="password"
                   placeholder="Minimal 6 karakter"
                   minlength="6"
                   required>

            <label>Role Akun</label>

            <input type="text"
                   class="role"
                   value="Siswa"
                   readonly>

            <button type="submit" name="register">
                Daftar sebagai Siswa
            </button>

        </form>

        <div class="switch">
            Sudah punya akun?
            <a href="#" onclick="showLogin(); return false;">
                Login
            </a>
        </div>

    </div>

</div>

<script>

function showRegister() {
    document.getElementById("loginForm").style.display = "none";
    document.getElementById("registerForm").style.display = "block";
}

function showLogin() {
    document.getElementById("registerForm").style.display = "none";
    document.getElementById("loginForm").style.display = "block";
}

</script>

</body>
</html>
