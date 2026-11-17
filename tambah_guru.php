<?php
include 'koneksi.php';
include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Guru</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f9;
        }

        .content {
            padding: 30px;
        }

        .form-card {
            max-width: 650px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .form-title {
            font-weight: bold;
            color: #212529;
        }

        .form-label {
            font-weight: 600;
        }

        .btn-simpan {
            background-color: #0d6efd;
            color: white;
        }

        .btn-simpan:hover {
            background-color: #0b5ed7;
            color: white;
        }
    </style>
</head>

<body>

<div class="content">

    <div class="form-card">

        <h3 class="form-title text-center mb-2">
            Form Tambah Guru
        </h3>

        <p class="text-muted text-center mb-4">
            Masukkan data akun dan profil guru
        </p>

        <form action="aksi_tambah_user.php" method="POST">

            <!-- ROLE OTOMATIS GURU -->
            <input type="hidden" name="role" value="guru">

            <h5 class="mb-3">Data Akun</h5>

            <!-- USERNAME -->
            <div class="mb-3">
                <label class="form-label">Username</label>

                <input
                    type="text"
                    name="username"
                    class="form-control"
                    placeholder="Masukkan username"
                    required>
            </div>

            <!-- PASSWORD -->
            <div class="mb-4">
                <label class="form-label">Password</label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Masukkan password"
                    required>
            </div>

            <hr>

            <h5 class="mb-3">Data Profil Guru</h5>

            <!-- NIP -->
            <div class="mb-3">
                <label class="form-label">NIP</label>

                <input
                    type="text"
                    name="nip"
                    class="form-control"
                    placeholder="Masukkan NIP guru"
                    required>
            </div>

            <!-- NAMA GURU -->
            <div class="mb-3">
                <label class="form-label">Nama Guru</label>

                <input
                    type="text"
                    name="nama"
                    class="form-control"
                    placeholder="Masukkan nama lengkap guru"
                    required>
            </div>

            <!-- JENIS KELAMIN -->
            <div class="mb-4">
                <label class="form-label">Jenis Kelamin</label>

                <select
                    name="jenis_kelamin"
                    class="form-select"
                    required>

                    <option value="">-- Pilih Jenis Kelamin --</option>

                    <option value="L">Laki-laki</option>

                    <option value="P">Perempuan</option>

                </select>
            </div>

            <!-- TOMBOL -->
            <div class="d-flex justify-content-between gap-2">

                <a href="daftar_guru.php"
                   class="btn btn-secondary">
                    Kembali
                </a>

                <button type="submit" class="btn btn-simpan">
                    Simpan Guru
                </button>

            </div>

        </form>

    </div>

</div>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
