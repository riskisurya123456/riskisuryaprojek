<?php
$role = strtolower(trim((string)($_SESSION['role'] ?? '')));
$username = $_SESSION['username'] ?? 'Pengguna';

$dashboard = [
    'admin' => 'dashboard.php',
    'guru'  => 'guru.php',
    'siswa' => 'dashboard_siswa.php'
];

$home = $dashboard[$role] ?? 'login.php';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">

        <!-- BRAND -->
        <a class="navbar-brand fw-bold"
           href="<?= htmlspecialchars($home, ENT_QUOTES, 'UTF-8') ?>">
            SIACAD SMK
        </a>

        <!-- TOMBOL RESPONSIVE -->
        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#menuSIACAD"
                aria-controls="menuSIACAD"
                aria-expanded="false"
                aria-label="Buka menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuSIACAD">

            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <?php if ($role === 'admin'): ?>

                    <!-- HOME ADMIN -->
                    <li class="nav-item">
                        <a class="nav-link" href="daftar_user.php">
                            Home
                        </a>
                    </li>

                    <!-- DROPDOWN USERS -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle"
                           href="#"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            Users
                        </a>

                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="daftar_user.php">
                                    Daftar Users 1
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="daftar2_user.php">
                                    Daftar Users 2
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="form.php">
                                    Form Siswa
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="formpilihan.php">
                                    Form Pilihan
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="formadmin.php">
                                    Form Admin
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- DROPDOWN SISWA -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle"
                           href="#"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            Siswa
                        </a>

                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="crud_edit_siswa.php">
                                    Data Siswa
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="form.php">
                                    Tambah Siswa
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- DROPDOWN GURU -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle"
                           href="#"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            Guru
                        </a>

                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="daftar_guru.php">
                                    Daftar Guru
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="tambah_guru.php">
                                    Tambah Guru
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- DROPDOWN MAPEL -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle"
                           href="#"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            Mapel
                        </a>

                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="daftar_mapel.php">
                                    Daftar Mapel
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="tambah_mapel.php">
                                    Tambah Mapel
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- DROPDOWN MENGAJAR -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle"
                           href="#"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            Mengajar
                        </a>

                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="daftar_mengajar.php">
                                    Daftar Mengajar
                                </a>
                            </li>
                        </ul>
                    </li>

                <?php elseif ($role === 'guru'): ?>

                    <!-- HOME GURU -->
                    <li class="nav-item">
                        <a class="nav-link" href="guru.php">
                            Home
                        </a>
                    </li>

                    <!-- DROPDOWN GURU -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle"
                           href="#"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            Menu Guru
                        </a>

                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="daftar_mengajar.php">
                                    Daftar Mengajar Saya
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="nilai_guru.php">
                                    Input Nilai
                                </a>
                            </li>
                        </ul>
                    </li>

                <?php elseif ($role === 'siswa'): ?>

                    <!-- HOME SISWA -->
                    <li class="nav-item">
                        <a class="nav-link" href="data_teman.php">
                            Home
                        </a>
                    </li>

                    <!-- DROPDOWN SISWA -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle"
                           href="#"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            Akademik Saya
                        </a>

                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="nilai_siswa.php">
                                    Nilai Saya
                                </a>
                            </li>
                        </ul>
                    </li>

                <?php endif; ?>

            </ul>

            
<!-- PROFIL DAN LOGOUT -->
<div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">

    <!-- DROPDOWN PROFIL -->
    <div class="nav-item dropdown">

        <a class="nav-link dropdown-toggle text-white"
           href="#"
           role="button"
           data-bs-toggle="dropdown"
           aria-expanded="false">

            <span class="badge rounded-circle bg-light text-primary me-1">
                <?= strtoupper(substr($username, 0, 1)); ?>
            </span>

            <?= htmlspecialchars(ucfirst($role), ENT_QUOTES, 'UTF-8'); ?>:
            <strong>
                <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>
            </strong>

        </a>

        <ul class="dropdown-menu dropdown-menu-end">

            <li>
                <h6 class="dropdown-header">
                    Akun Saya
                </h6>
            </li>

            <li>
                <a class="dropdown-item" href="profil.php">
                    <i class="bi bi-person-circle me-2"></i>
                    Profil Saya
                </a>
            </li>

            <li>
                <hr class="dropdown-divider">
            </li>
            <li>
<a class="dropdown-item" href="pengaturan_profil.php">
    <i class="bi bi-gear me-2"></i>
    Pengaturan Akun
</a>
                </li>
            <li>
                <a class="dropdown-item text-danger"
                   href="logout.php"
                   onclick="return confirm('Apakah kamu yakin ingin logout?');">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Logout
                </a>
            </li>

        </ul>

    </div>

</div>

</nav>

<style>
    .navbar .dropdown-menu {
        border: none;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        padding: 8px;
        min-width: 210px;
    }

    .navbar .dropdown-item {
        padding: 10px 14px;
        border-radius: 5px;
        transition: background-color 0.2s ease;
    }

    .navbar .dropdown-item:hover {
        background-color: #0d6efd;
        color: white;
    }

    @media (min-width: 992px) {
        .navbar .dropdown:hover > .dropdown-menu {
            display: block;
        }
    }
</style>
