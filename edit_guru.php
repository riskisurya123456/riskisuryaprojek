<?php
include 'koneksi.php';

// Fungsi untuk mengamankan output
function e($nilai) {
    return htmlspecialchars(
        (string) $nilai,
        ENT_QUOTES,
        'UTF-8'
    );
}

// Ambil daftar nama guru dari database
$daftar_guru = mysqli_query($koneksi, "
    SELECT guru.user_id, guru.nama
    FROM guru
    INNER JOIN users ON users.id = guru.user_id
    WHERE users.role = 'guru'
    ORDER BY guru.nama ASC
");

if (!$daftar_guru) {
    die("Gagal mengambil daftar guru: " .
        mysqli_error($koneksi));
}

// Ambil ID guru yang dipilih
$user_id = (int) ($_GET['id'] ?? 0);
$data = null;

if ($user_id > 0) {
    $stmt = mysqli_prepare($koneksi, "
        SELECT
            guru.user_id,
            guru.nip,
            guru.nama,
            guru.jenis_kelamin,
            users.username
        FROM guru
        INNER JOIN users ON users.id = guru.user_id
        WHERE guru.user_id = ?
          AND users.role = 'guru'
        LIMIT 1
    ");

    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($hasil);

    mysqli_stmt_close($stmt);
}

// Format jenis kelamin dari database
$jk = strtoupper(trim($data['jenis_kelamin'] ?? ''));

include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Edit Data Guru</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
        }

        .content {
            padding: 30px;
        }

        .form-card {
            max-width: 650px;
            margin: 20px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,.08);
        }

        .form-label {
            font-weight: 600;
        }
    </style>
</head>

<body>

<div class="content">
    <div class="form-card">

        <h3 class="text-center mb-2">
            Edit Data Guru
        </h3>

        <p class="text-muted text-center mb-4">
            Pilih nama guru yang ingin diedit
        </p>

        <!-- PILIH NAMA GURU -->
        <form method="GET" action="edit_guru.php"
              class="mb-4">

            <label class="form-label">Nama Guru</label>

            <select name="id"
                    class="form-select"
                    onchange="this.form.submit()"
                    required>

                <option value="">
                    -- Pilih Nama Guru --
                </option>

                <?php while ($guru = mysqli_fetch_assoc($daftar_guru)) { ?>

                    <option
                        value="<?= (int) $guru['user_id'] ?>"
                        <?= $user_id === (int) $guru['user_id']
                            ? 'selected' : '' ?>>

                        <?= e($guru['nama']) ?>

                    </option>

                <?php } ?>

            </select>

            <noscript>
                <button type="submit"
                        class="btn btn-primary mt-2">
                    Pilih Guru
                </button>
            </noscript>

        </form>

        <?php if ($user_id > 0 && !$data) { ?>

            <div class="alert alert-warning">
                Data guru tidak ditemukan.
            </div>

        <?php } elseif ($data) { ?>

            <hr>

            <form action="aksi_edit_guru.php"
                  method="POST">

                <input type="hidden"
                       name="user_id"
                       value="<?= (int) $data['user_id'] ?>">

                <h5 class="mb-3">Data Profil Guru</h5>

                <!-- NIP -->
                <div class="mb-3">
                    <label class="form-label">NIP</label>

                    <input type="text"
                           name="nip"
                           class="form-control"
                           value="<?= e($data['nip']) ?>"
                           required>
                </div>

                <!-- NAMA -->
                <div class="mb-3">
                    <label class="form-label">Nama Guru</label>

                    <input type="text"
                           name="nama"
                           class="form-control"
                           value="<?= e($data['nama']) ?>"
                           required>
                </div>

                <!-- JENIS KELAMIN -->
                <div class="mb-3">
                    <label class="form-label">Jenis Kelamin</label>

                    <select name="jenis_kelamin"
                            class="form-select"
                            required>

                        <option value="">
                            -- Pilih Jenis Kelamin --
                        </option>

                        <option value="L"
                            <?= $jk === 'L' ? 'selected' : '' ?>>
                            Laki-laki
                        </option>

                        <option value="P"
                            <?= $jk === 'P' ? 'selected' : '' ?>>
                            Perempuan
                        </option>

                    </select>

                    <?php if ($jk !== 'L' && $jk !== 'P') { ?>
                        <small class="text-danger">
                            Jenis kelamin belum diisi.
                            Silakan pilih jenis kelamin.
                        </small>
                    <?php } ?>
                </div>

                <hr>

                <h5 class="mb-3">Data Akun Guru</h5>

                <!-- USERNAME -->
                <div class="mb-3">
                    <label class="form-label">Username</label>

                    <input type="text"
                           name="username"
                           class="form-control"
                           value="<?= e($data['username']) ?>"
                           required>
                </div>

                <!-- PASSWORD -->
                <div class="mb-4">
                    <label class="form-label">
                        Password Baru
                    </label>

                    <input type="password"
                           name="password"
                           class="form-control"
                           placeholder="Kosongkan jika tidak diganti">

                    <small class="text-muted">
                        Kosongkan jika ingin mempertahankan
                        password lama.
                    </small>
                </div>

                <div class="d-flex justify-content-between gap-2">

                    <a href="daftar_guru.php"
                       class="btn btn-secondary">
                        Kembali
                    </a>

                    <button type="submit"
                            class="btn btn-primary">
                        Simpan Perubahan
                    </button>

                </div>

            </form>

        <?php } ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>