<?php
// Memulai session jika belum aktif.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Form Tambah Siswa</title>

    <!-- CSS desain asli -->
    <link rel="stylesheet" href="style1.css">

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- RESPONSIVE HP TANPA MENGUBAH DESAIN ASLI -->
    <style>

        * {
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        /* Tampilan desktop tetap asli */
        .form2 {
            width: 30%;
            max-width: 100%;
        }

        /* Tampilan HP dan tablet */
        @media screen and (max-width: 768px) {

            .form2 {
                width: 95% !important;
                max-width: 95%;
                margin-left: auto !important;
                margin-right: auto !important;
            }

            .container {
                width: 100%;
                max-width: 100%;
                padding-left: 10px;
                padding-right: 10px;
            }

            .card {
                width: 100%;
            }

            .form-control {
                max-width: 100%;
            }

            input, select, button {
                max-width: 100%;
            }

            .card-body {
                padding: 15px;
            }

        }

        /* Tampilan HP kecil */
        @media screen and (max-width: 400px) {

            .form2 {
                width: 98% !important;
                max-width: 98%;
            }

        }

    </style>

</head>

<body>

<?php
// Memanggil navbar.
require_once __DIR__ . '/navbar.php';
?>

<!-- =========================
     FORM TAMBAH DATA SISWA
========================= -->

<div class="form2" style="margin: 0 auto; width: 30%;">
    <div class="container text-center" style="margin: 0 auto;">
        <div class="form">
            <div class="card" style="margin-top: 20px;">

                <div class="card-form">
                    <h2>Form Tambah Siswa</h2>
                    <br>
                </div>

                <div class="card-body">

                    <!-- Data diproses oleh aksi_tambah_user.php -->
                    <form action="aksi_tambah_user.php" method="POST">

                        <h1>Data Profil Siswa</h1>
                        <br>

                        <!-- NIS -->
                        <div class="mb-3">
                            <input
                                type="number"
                                name="nis"
                                class="form-control"
                                placeholder="Nomor Induk Siswa | Contoh:12345678"
                                style="width: 90%; margin: 0 auto;"
                                required
                            >
                        </div>

                        <br>

                        <!-- Nama siswa -->
                        <div class="mb-3">
                            <input
                                type="text"
                                name="nama_siswa"
                                class="form-control"
                                placeholder="Nama Lengkap Siswa | Contoh:Ujang Asep"
                                style="width: 90%; margin: 0 auto;"
                                required
                            >
                        </div>

                        <br>

                        <!-- Kelas -->
                        <div class="mb-3">
                            <input
                                type="text"
                                name="kelas"
                                class="form-control"
                                placeholder="Kelas Siswa | Contoh:X-RPL-1"
                                style="width: 90%; margin: 0 auto;"
                                required
                            >
                        </div>

                        <br>

                        <!-- Jenis kelamin -->
                        <div class="radio">

                            Jenis Kelamin:<br>

                            <input
                                type="radio"
                                name="jenis_kelamin"
                                id="L"
                                value="L"
                                class="form-check-input"
                                required
                            >

                            <label for="L">L</label>
                            <br>

                            <input
                                type="radio"
                                name="jenis_kelamin"
                                id="P"
                                value="P"
                                class="form-check-input"
                                required
                            >

                            <label for="P">P</label>

                        </div>

                        <br>

                        <!-- DATA AKUN USERS -->

                        <div
                            class="form2"
                            style="border: 1px solid #d3d3d3; padding: 10px;"
                        >

                            <h3>Data Kredensial Akun (Tabel Users)</h3>
                            <br>

                            <!-- Username -->
                            <div class="mb-3">

                                <input
                                    type="text"
                                    name="username"
                                    class="form-control"
                                    placeholder="Username Akun | Contoh:ujang123"
                                    style="width: 90%; margin: 0 auto;"
                                    required
                                >

                            </div>

                            <br>

                            <!-- Password -->
                            <div class="mb-3">

                                <input
                                    type="password"
                                    name="password"
                                    class="form-control"
                                    placeholder="Password Akun"
                                    style="width: 90%; margin: 0 auto;"
                                    required
                                >

                            </div>

                            <!-- Role otomatis siswa -->
                            <input
                                type="hidden"
                                name="role"
                                value="siswa"
                            >

                            <br>

                            <!-- Tombol -->
                            <div class="button1">

                                <button
                                    type="submit"
                                    name="kirim"
                                    class="btn btn-primary"
                                    style="margin-bottom: 20px;"
                                >
                                    Simpan Data
                                </button>

                                <a
                                    href="crud_edit_siswa.php"
                                    class="btn btn-danger"
                                    style="margin-bottom: 20px; display: inline-block; text-decoration: none;"
                                >
                                    Kembali
                                </a>

                            </div>

                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<br>
<br>

<!-- =========================
     OUTPUT FORM
========================= -->

<div class="form2" style="margin: 0 auto; width: 30%;">
    <div class="container text-center">

        <div class="card" style="margin-top: 20px;">

            <div class="card-form">

                <h2>Output dari Form</h2>
                <br>

                <div class="card-get">

                    <?php

                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                        $nis = $_POST['nis'] ?? '-';
                        $nama_siswa = $_POST['nama_siswa'] ?? '-';
                        $kelas = $_POST['kelas'] ?? '-';
                        $jenis_kelamin = $_POST['jenis_kelamin'] ?? '-';
                        $username = $_POST['username'] ?? '-';

                        echo "NIS: " .
                            htmlspecialchars((string)$nis, ENT_QUOTES, 'UTF-8') . "<br>";

                        echo "Nama: " .
                            htmlspecialchars((string)$nama_siswa, ENT_QUOTES, 'UTF-8') . "<br>";

                        echo "Kelas: " .
                            htmlspecialchars((string)$kelas, ENT_QUOTES, 'UTF-8') . "<br>";

                        echo "Jenis Kelamin: " .
                            htmlspecialchars((string)$jenis_kelamin, ENT_QUOTES, 'UTF-8') . "<br>";

                        echo "Username: " .
                            htmlspecialchars((string)$username, ENT_QUOTES, 'UTF-8') . "<br>";

                    } else {

                        echo "<em>Belum ada data yang dikirim. Isi form di atas.</em>";

                    }

                    ?>

                </div>

            </div>

        </div>

    </div>
</div>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
