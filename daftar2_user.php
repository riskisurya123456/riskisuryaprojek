<?php
// ==================================================
// 1. KONEKSI DATABASE DAN SESSION
// ==================================================

// Menghubungkan halaman dengan database SIACAD SMK
require_once 'koneksi.php';

// Memulai session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Memastikan pengguna sudah login sebagai admin
if (
    !isset($_SESSION['login']) ||
    ($_SESSION['role'] ?? '') !== 'admin'
) {
    header("Location: login.php");
    exit;
}


// ==================================================
// 2. MENGAMBIL DATA USERS
// ==================================================

// Mengambil data akun dari tabel users.
// Password ditampilkan dalam bentuk hash sesuai isi database.
// Password hash bukan password asli yang bisa dibaca kembali.
//
// LEFT JOIN digunakan untuk mengambil nama siswa atau guru.
// Nama admin ditampilkan menggunakan username.

$sql = "
    SELECT
        u.id,
        u.username,
        u.password,
        u.role,
        u.created_at,

        CASE
            WHEN u.role = 'siswa' THEN s.nama_siswa
            WHEN u.role = 'guru' THEN g.nama
            ELSE u.username
        END AS nama

    FROM users u

    LEFT JOIN siswa s
        ON s.user_id = u.id

    LEFT JOIN guru g
        ON g.user_id = u.id

    ORDER BY u.id DESC
";

// Menjalankan query
$query = mysqli_query($koneksi, $sql);

// Memeriksa apakah query berhasil
if (!$query) {
    die("Query gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <!-- Pengaturan karakter halaman -->
    <meta charset="UTF-8">

    <!-- Membuat halaman responsif -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Daftar User 2 - SIACAD SMK</title>

    <!-- Bootstrap CSS untuk tampilan -->
    <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

    <!-- CSS DataTables untuk tabel pencarian -->
    <link
        href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css"
        rel="stylesheet"
    >

    <style>

        /* Mengatur tampilan dasar halaman */
        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
            font-family: Arial, sans-serif;
        }

        /* Mengatur area utama tabel */
        .content {
            width: 90%;
            max-width: 1200px;
            margin: 25px auto;
        }

        /* Mengatur judul halaman */
        .judul-halaman {
            text-align: center;
            font-size: 36px;
            margin-bottom: 25px;
            color: #212529;
        }

        /* Membuat tabel memenuhi area konten */
        .table-users {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
        }

        /* Mengatur judul kolom */
        .table-users thead th {
            background: #4f46e5;
            color: #ffffff;
            padding: 13px 12px;
            text-align: left;
            border: 1px solid #dee2e6;
            white-space: nowrap;
        }

        /* Mengatur isi setiap sel tabel */
        .table-users tbody td {
            padding: 12px;
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }

        /* Memberikan warna selang-seling pada baris */
        .table-users tbody tr:nth-child(odd) {
            background: #f1f1f1;
        }

        /* Memberikan efek ketika kursor diarahkan ke baris */
        .table-users tbody tr:hover {
            background: #e9ecef;
        }

        /* Mengatur tampilan tombol Edit dan Hapus */
        .btn-aksi {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 13px;
            margin: 2px;
            white-space: nowrap;
        }

        /* Tombol Edit berwarna kuning */
        .btn-edit {
            background: #ffc107;
            color: #212529;
        }

        /* Tombol Hapus berwarna merah */
        .btn-hapus {
            background: #dc3545;
            color: #ffffff;
        }

        /* Efek hover tombol Edit */
        .btn-edit:hover {
            background: #e0a800;
            color: #212529;
        }

        /* Efek hover tombol Hapus */
        .btn-hapus:hover {
            background: #bb2d3b;
            color: #ffffff;
        }

        /* Mengatur tampilan password hash agar tidak memaksa kolom melebar */
        .password-hash {
            display: inline-block;
            max-width: 280px;
            overflow-wrap: anywhere;
            font-size: 12px;
            color: #333333;
        }

        /* Penyesuaian tampilan untuk layar HP */
        @media (max-width: 768px) {

            .content {
                width: 96%;
            }

            .judul-halaman {
                font-size: 28px;
            }

            .table-users th,
            .table-users td {
                padding: 8px;
                font-size: 13px;
            }

            .password-hash {
                max-width: 180px;
            }
        }

    </style>

</head>

<body>

    <!-- ==================================================
         3. MEMANGGIL NAVBAR YANG SUDAH ADA
         ================================================== -->

    <!-- Navbar tetap menggunakan file navbar.php milik lu -->
    <?php include 'navbar.php'; ?>


    <!-- ==================================================
         4. MENAMPILKAN TABEL DAFTAR USERS
         ================================================== -->

    <div class="content">

        <!-- Judul halaman -->
        <h2 class="judul-halaman">Daftar User</h2>

        <!-- Membuat tabel bisa digeser secara horizontal -->
        <div class="table-responsive">

            <!-- ID tabel digunakan untuk mengaktifkan DataTables -->
            <table id="tabelUsers" class="table-users">

                <!-- Judul kolom -->
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Password</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <!-- Data pengguna ditampilkan di bagian tbody -->
                 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
                <tbody>

                    <?php if (mysqli_num_rows($query) > 0): ?>

                        <?php
                        // Nomor urut dimulai dari 1
                        $no = 1;
                        ?>

                        <!-- Mengambil setiap baris data dari database -->
                        <?php while ($row = mysqli_fetch_assoc($query)): ?>

                            <tr>

                                <!-- Nomor urut tabel -->
                                <td>
                                    <strong><?= $no++; ?></strong>
                                </td>

                                <!-- Username akun -->
                                <td>
                                    <?= htmlspecialchars(
                                        $row['username'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                                <!-- Menampilkan hash password dari database -->
                                <td>
                                    <span class="password-hash">
                                        <?= htmlspecialchars(
                                            $row['password'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </span>
                                </td>

                                <!-- Role pengguna: admin, guru, atau siswa -->
                                <td>
                                    <?= htmlspecialchars(
                                        $row['role'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                                <!-- Waktu pembuatan akun -->
                                <td>
                                    <?= htmlspecialchars(
                                        $row['created_at'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                                <!-- Tombol untuk mengedit dan menghapus akun -->
                                <td>

                                    <!-- Membuka halaman edit berdasarkan ID user -->
                                    <a
                                        href="edit_user.php?id=<?= (int)$row['id']; ?>"
                                        class="btn-aksi btn-edit"
                                    >
                                        Edit
                                    </a>

                                    <!-- Menghapus akun setelah konfirmasi -->
                                    <a
                                        href="hapus.php?id=<?= (int)$row['id']; ?>"
                                        class="btn-aksi btn-hapus"
                                        onclick="return confirm('Yakin ingin menghapus user ini?');"
                                    >
                                        Hapus
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <!-- Ditampilkan jika belum ada data -->
                        <tr>
                            <td colspan="6" class="text-center">
                                Belum ada data users.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

            <!-- Menampilkan jumlah akun -->
            <p class="mt-3">
                <strong>Total pengguna:</strong>
                <?= mysqli_num_rows($query); ?> akun
            </p>

        </div>

    </div>


    <!-- ==================================================
         5. JAVASCRIPT UNTUK TABEL
         ================================================== -->

    <!-- Bootstrap JavaScript -->
   
    <!-- jQuery diperlukan oleh DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables JavaScript -->
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>

    <!-- Integrasi DataTables dengan Bootstrap -->
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // Mengaktifkan pencarian, pengurutan, dan pagination tabel
        $(document).ready(function () {

            $('#tabelUsers').DataTable({

                // Menampilkan 10 data pada setiap halaman
                pageLength: 10,

                // Mengatur teks navigasi tabel ke bahasa Indonesia
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Tidak ada data",
                    zeroRecords: "Data tidak ditemukan",

                    paginate: {
                        previous: "Sebelumnya",
                        next: "Selanjutnya"
                    }
                }

            });

        });
    </script>

</body>
</html>
```