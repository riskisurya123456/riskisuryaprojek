<?php
session_start();
require_once "koneksi.php";

// Pastikan pengguna sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Ambil ID user dari session
$user_id = (int) ($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);

if ($user_id <= 0) {
    die("ID pengguna tidak ditemukan. Silakan logout dan login kembali.");
}

// Ambil data akun pengguna
$query = mysqli_prepare(
    $koneksi,
    "SELECT id, username, role, created_at
     FROM users
     WHERE id = ?"
);

mysqli_stmt_bind_param($query, "i", $user_id);
mysqli_stmt_execute($query);

$result = mysqli_stmt_get_result($query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    die("Data pengguna tidak ditemukan.");
}

// Siapkan data profil tambahan
$nama = $user['username'];
$detail = [];
$role = strtolower($user['role'] ?? '');

// Ambil data sesuai role
if ($role === 'siswa') {

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT *
         FROM siswa
         WHERE user_id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);
    $data_siswa = mysqli_fetch_assoc($hasil);

    if ($data_siswa) {
        $detail = $data_siswa;

        // Coba ambil nama siswa dari kolom yang tersedia
        foreach (['nama', 'nama_siswa', 'nama_lengkap'] as $kolom) {
            if (!empty($data_siswa[$kolom])) {
                $nama = $data_siswa[$kolom];
                break;
            }
        }
    }

} elseif ($role === 'guru') {

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT *
         FROM guru
         WHERE user_id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);
    $data_guru = mysqli_fetch_assoc($hasil);

    if ($data_guru) {
        $detail = $data_guru;

        foreach (['nama', 'nama_guru'] as $kolom) {
            if (!empty($data_guru[$kolom])) {
                $nama = $data_guru[$kolom];
                break;
            }
        }
    }
}

// Fungsi untuk menampilkan teks dengan aman
function aman($nilai) {
    return htmlspecialchars((string) ($nilai ?? '-'), ENT_QUOTES, 'UTF-8');
}

// Inisial nama untuk avatar
$inisial = strtoupper(substr($nama, 0, 1));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Profil Saya - SIACAD SMK</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
          rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
            font-family: Arial, sans-serif;
        }

        .profil-container {
            max-width: 1000px;
            margin: 35px auto;
            padding: 0 15px;
        }

        .judul-halaman {
            font-weight: 700;
            color: #253858;
        }

        .kartu-profil {
            background: #fff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 5px 22px rgba(0, 0, 0, 0.07);
            border: none;
        }

        .profil-header {
            background: linear-gradient(135deg, #0d6efd, #084298);
            padding: 35px 25px;
            color: white;
        }

        .avatar {
            width: 95px;
            height: 95px;
            background: white;
            color: #0d6efd;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 42px;
            font-weight: bold;
            margin: 0 auto 15px;
            border: 4px solid rgba(255,255,255,.5);
        }

        .badge-role {
            background: rgba(255,255,255,.2);
            color: white;
            padding: 8px 16px;
            border-radius: 30px;
            display: inline-block;
            font-size: 13px;
        }

        .bagian-data {
            padding: 28px;
        }

        .judul-bagian {
            font-weight: 700;
            color: #253858;
            margin-bottom: 20px;
        }

        .item-data {
            padding: 15px;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            margin-bottom: 12px;
            height: 100%;
        }

        .label-data {
            color: #6c757d;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .isi-data {
            color: #212529;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .ikon-data {
            color: #0d6efd;
            margin-right: 7px;
        }

        .tombol-kembali {
            border-radius: 9px;
            padding: 10px 18px;
        }

        @media (max-width: 576px) {
            .bagian-data {
                padding: 20px;
            }

            .profil-header {
                padding: 28px 15px;
            }
        }
    </style>
</head>

<body>

<?php
// Tampilkan navbar yang sudah kamu gunakan
include "navbar.php";
?>

<div class="profil-container">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="judul-halaman mb-1">
                <i class="bi bi-person-circle me-2"></i>Profil Saya
            </h2>
            <p class="text-secondary mb-0">
                Informasi akun SIACAD SMK kamu.
            </p>
        </div>

        <a href="javascript:history.back()" class="btn btn-outline-primary tombol-kembali">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="kartu-profil">

        <div class="profil-header text-center">
            <div class="avatar">
                <?= aman($inisial); ?>
            </div>

            <h3 class="fw-bold mb-2"><?= aman($nama); ?></h3>

            <span class="badge-role">
                <i class="bi bi-shield-check me-1"></i>
                <?= aman(ucfirst($role)); ?>
            </span>

            <p class="mb-0 mt-3">
                <i class="bi bi-person me-1"></i>
                @<?= aman($user['username']); ?>
            </p>
        </div>

        <div class="bagian-data">

            <h5 class="judul-bagian">
                <i class="bi bi-card-list ikon-data"></i>
                Informasi Akun
            </h5>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <div class="item-data">
                        <div class="label-data">
                            <i class="bi bi-person-badge ikon-data"></i>Username
                        </div>
                        <div class="isi-data">
                            <?= aman($user['username']); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="item-data">
                        <div class="label-data">
                            <i class="bi bi-shield-check ikon-data"></i>Role Pengguna
                        </div>
                        <div class="isi-data">
                            <?= aman(ucfirst($role)); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="item-data">
                        <div class="label-data">
                            <i class="bi bi-hash ikon-data"></i>ID Pengguna
                        </div>
                        <div class="isi-data">
                            <?= aman($user['id']); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="item-data">
                        <div class="label-data">
                            <i class="bi bi-calendar-check ikon-data"></i>Tanggal Akun Dibuat
                        </div>
                        <div class="isi-data">
                            <?= aman($user['created_at'] ?? '-'); ?>
                        </div>
                    </div>
                </div>

            </div>

            <?php if (!empty($detail)): ?>

                <hr class="my-4">

                <h5 class="judul-bagian">
                    <i class="bi bi-person-vcard ikon-data"></i>
                    Data <?= aman(ucfirst($role)); ?>
                </h5>

                <div class="row">

                    <?php foreach ($detail as $kolom => $nilai): ?>

                        <?php
                        // Hindari menampilkan ID internal dan relasi akun
                        if (in_array(strtolower($kolom), [
                            'id',
                            'user_id',
                            'users_id',
                            'password'
                        ], true)) {
                            continue;
                        }

                        // Ubah nama kolom menjadi label yang lebih mudah dibaca
                        $label = ucwords(str_replace('_', ' ', $kolom));
                        ?>

                        <div class="col-md-6 mb-3">
                            <div class="item-data">
                                <div class="label-data">
                                    <i class="bi bi-info-circle ikon-data"></i>
                                    <?= aman($label); ?>
                                </div>

                                <div class="isi-data">
                                    <?= aman($nilai); ?>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <?php if ($role === 'siswa' || $role === 'guru'): ?>
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Data <?= aman($role); ?> belum ditemukan atau belum terhubung
                        dengan akun ini. Silakan hubungi admin.
                    </div>
                <?php endif; ?>

            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="logout.php"
                   class="btn btn-danger tombol-kembali"
                   onclick="return confirm('Yakin ingin keluar dari akun?')">
                    <i class="bi bi-box-arrow-right me-1"></i>
                    Logout
                </a>
            </div>

        </div>
    </div>

    <p class="text-center text-secondary mt-4 small">
        SIACAD SMK &copy; <?= date('Y'); ?>
    </p>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
