<?php

$koneksi = mysqli_connect(
    "127.0.0.1",
    "root",
    "root",
    "db_siacad_smk2",
    3306
);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// =============================================
// 2. PROSES DATA FORM
// =============================================

// LANJUTKAN KODE PROSES DATA KAMU DI SINI

// =============================================
// 2. PROSES DATA FORM
// =============================================

// PROSES HANYA JIKA TOMBOL SIMPAN DITEKAN
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $role     = $_POST['role'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($role === '' || $username === '' || $password === '') {
        die("Username, password, dan role wajib diisi.");
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    mysqli_begin_transaction($koneksi);

    try {

        // SIMPAN USER
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

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(mysqli_stmt_error($stmt));
        }

        $user_id = mysqli_insert_id($koneksi);
        mysqli_stmt_close($stmt);

        // SIMPAN SISWA
        if ($role === "siswa") {

            $nis = $_POST['nis'] ?? '';
            $nama_siswa = trim($_POST['nama_siswa'] ?? '');
            $kelas = trim($_POST['kelas'] ?? '');
            $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';

            if (
                $nis === '' ||
                $nama_siswa === '' ||
                $kelas === '' ||
                $jenis_kelamin === ''
            ) {
                throw new Exception("Data siswa belum lengkap.");
            }

            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO siswa
                (nis, nama_siswa, kelas, jenis_kelamin, user_id)
                VALUES (?, ?, ?, ?, ?)"
            );

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
        }

        // SIMPAN GURU
        elseif ($role === "guru") {

            $nama = trim($_POST['nama'] ?? '');
            $nip = trim($_POST['nip'] ?? '');
            $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';

            if (
                $nama === '' ||
                $nip === '' ||
                $jenis_kelamin === ''
            ) {
                throw new Exception("Data guru belum lengkap.");
            }

            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO guru
                (nip, nama, jenis_kelamin, user_id)
                VALUES (?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sssi",
                $nip,
                $nama,
                $jenis_kelamin,
                $user_id
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception(mysqli_stmt_error($stmt));
            }

            mysqli_stmt_close($stmt);
        }

        elseif ($role !== "admin") {
            throw new Exception("Role tidak valid.");
        }

        // SELESAIKAN TRANSAKSI
        mysqli_commit($koneksi);

        echo "<script>
            alert('Data berhasil disimpan!');
            window.location.href = 'daftar_user.php';
        </script>";

        exit;

    } catch (Throwable $e) {

        mysqli_rollback($koneksi);

        echo "Data gagal disimpan: " .
        htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
}
?>


<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<?php require_once 'navbar.php'; ?>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-md-8 col-lg-6">

            <div class="card shadow-sm border-0">

                <div class="card-body p-4">

                    <h3 class="fw-bold mb-1">
                        Tambah User
                    </h3>

                    <p class="text-muted mb-4">
                        Pilih role untuk mengisi data pengguna
                    </p>


                    <form action="aksi_tambah_user.php" method="POST">

                        <!-- ROLE -->
                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Role
                            </label>

                            <select
                                name="role"
                                id="role"
                                class="form-select"
                                onchange="ubahForm()"
                                required
                            >

                                <option value="">
                                    -- Pilih Role --
                                </option>

                                <option value="admin">
                                    Admin
                                </option>

                                <option value="siswa">
                                    Siswa
                                </option>

                                <option value="guru">
                                    Guru
                                </option>

                            </select>

                        </div>


                        <!-- DATA USER -->

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Username
                            </label>

                            <input
                                type="text"
                                name="username"
                                class="form-control"
                                placeholder="Masukkan username"
                                required
                            >

                        </div>

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                placeholder="Masukkan password"
                                required
                            >

                        </div>


                        <!-- ================= -->
                        <!-- FORM SISWA -->
                        <!-- ================= -->

                        <div id="formSiswa" style="display:none;">

                            <hr>

                            <h5 class="fw-bold mb-3">
                                Data Siswa
                            </h5>


                            <div class="mb-3">

                                <label class="form-label">
                                    NIS
                                </label>

                                <input
                                    type="text"
                                    name="nis"
                                    class="form-control"
                                    placeholder="Masukkan NIS"
                                >

                            </div>


                            <div class="mb-3">

                                <label class="form-label">
                                    Nama Siswa
                                </label>

                                <input
                                    type="text"
                                    name="nama_siswa"
                                    class="form-control"
                                    placeholder="Masukkan nama siswa"
                                >

                            </div>


                            <div class="mb-3">

                                <label class="form-label">
                                    Kelas
                                </label>

                                <input
                                    type="text"
                                    name="kelas"
                                    class="form-control"
                                    placeholder="Contoh: XI RPL 1"
                                >

                            </div>

                            <div class="mb-3">

                                <label class="form-label">
                                    Jenis Kelamin
                                </label>

                                <select name="jenis_kelamin" class="form-control">
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
 
                        </div>


                        <!-- ================= -->
                        <!-- FORM GURU -->
                        <!-- ================= -->

                        <div id="formGuru" style="display:none;">

                            <hr>

                            <h5 class="fw-bold mb-3">
                                Data Guru
                            </h5>

                            <div class="mb-3">

                                <label class="form-label">
                                    Nama Guru
                                </label>

                                <input
                                    type="text"
                                    name="nama"
                                    class="form-control"
                                    placeholder="Masukkan nama guru"
                                >

                            </div>

                            <div class="mb-3">

                                <label class="form-label">
                                    NIP
                                </label>

                                <input
                                    type="text"
                                    name="nip"
                                    class="form-control"
                                    placeholder="Masukkan NIP"
                                >

                            </div>

                            <div class="mb-3">

                                <label class="form-label">
                                    Jenis Kelamin
                                </label>

                                <select name="jenis_kelamin" class="form-control">
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>

                            </div>

                        </div>


                        <!-- ================= -->
                        <!-- FORM ADMIN -->
                        <!-- ================= -->

                        <div id="formAdmin" style="display:none;">

                            <hr>

                            <div class="alert alert-info">

                                <strong>Admin</strong> tidak membutuhkan
                                data tambahan.

                            </div>

                        </div>


                        <!-- BUTTON -->

                        <button
                            type="submit"
                            class="btn btn-primary w-100 mt-3"
                        >
                            Simpan Data
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>




<script>

function ubahForm() {

    let role = document.getElementById("role").value;

    // Sembunyikan semuanya
    document.getElementById("formSiswa").style.display = "none";
    document.getElementById("formGuru").style.display = "none";
    document.getElementById("formAdmin").style.display = "none";


    // Tampilkan sesuai role
    if (role === "siswa") {

        document.getElementById("formSiswa").style.display = "block";

    }

    else if (role === "guru") {

        document.getElementById("formGuru").style.display = "block";

    }

    else if (role === "admin") {

        document.getElementById("formAdmin").style.display = "block";

    }

}

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  </body>

  </html>