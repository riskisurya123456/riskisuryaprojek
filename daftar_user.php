<?php
// ==================================================
// 1. KONEKSI DATABASE DAN SESSION
// ==================================================

require_once 'koneksi.php';

// Memulai session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Memastikan halaman hanya dapat diakses admin
if (
    !isset($_SESSION['login']) ||
    ($_SESSION['role'] ?? '') !== 'admin'
) {
    header("Location: login.php");
    exit;
}


// ==================================================
// 2. MENGAMBIL DATA USER DAN NAMA PENGGUNA
// ==================================================

// Mengambil username dan role dari tabel users.
// Nama siswa diambil dari tabel siswa.
// Nama guru diambil dari tabel guru.
// Untuk admin, nama ditampilkan menggunakan username.

$sql = "
    SELECT
        u.id,
        u.username,
        u.role,

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

// Menampilkan pesan jika query gagal
if (!$query) {
    die("Query gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <!-- Pengaturan karakter halaman -->
    <meta charset="UTF-8">

    <!-- Membuat tampilan responsif -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Daftar User - SIACAD SMK</title>

    <!-- Bootstrap CSS -->
    <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

    <!-- CSS DataTables untuk pencarian dan pagination -->
    

    <style>
        /* Mengatur tampilan dasar halaman */
        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
            font-family: Arial, sans-serif;
        }

        /* Mengatur area konten tabel */
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

        /* Mengatur tampilan tabel */
        .table-users {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
        }

        /* Mengatur judul kolom */
        .table-users thead th {
            background: #4f46e5;
            color: white;
            padding: 13px 12px;
            text-align: left;
            border: 1px solid #dee2e6;
            white-space: nowrap;
        }

        /* Mengatur isi tabel */
        .table-users tbody td {
            padding: 12px;
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }

        /* Memberikan warna selang-seling pada baris */
        .table-users tbody tr:nth-child(odd) {
            background: #f1f1f1;
        }

        /* Efek saat kursor diarahkan ke baris */
        .table-users tbody tr:hover {
            background: #e9ecef;
        }

        /* Tampilan umum tombol aksi */
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
            color: white;
        }

        /* Efek hover tombol */
        .btn-edit:hover {
            background: #e0a800;
            color: #212529;
        }

        .btn-hapus:hover {
            background: #bb2d3b;
            color: white;
        }

        /* Menyesuaikan tampilan untuk layar kecil */
        @media (max-width: 768px) {
            .content {
                width: 96%;
            }

            .judul-halaman {
                font-size: 28px;
            }
        }
    </style>

</head>

<body>

    <!-- ==================================================
         3. NAVBAR
         ================================================== -->

    <!-- Memanggil navbar yang sudah digunakan di halaman lain.
         Tidak perlu membuat navbar baru di file ini. -->
    <?php include 'navbar.php'; ?>


    <!-- ==================================================
         4. TABEL DAFTAR USER
         ================================================== -->

    <div class="content">

        <!-- Judul halaman -->
        <h2 class="judul-halaman">Daftar User</h2>

        <!-- Membuat tabel responsif -->
        <div class="table-responsive">

            <!-- ID tabel dipakai untuk mengaktifkan DataTables -->
            <table id="tabelUsers" class="table-users">

                <!-- Nama kolom tabel -->
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Nama</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
<
                <!-- Data pengguna dari database -->
                <tbody>

                    <?php if (mysqli_num_rows($query) > 0): ?>

                        <?php
                        // Nomor urut dimulai dari 1
                        $no = 1;
                        ?>

                        <?php while ($row = mysqli_fetch_assoc($query)): ?>

                            <tr>

                                <!-- Nomor urut -->
                                <td>
                                    <strong><?= $no++; ?></strong>
                                </td>

                                <!-- Username pengguna -->
                                <td>
                                    <?= htmlspecialchars(
                                        $row['username'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                                <!-- Role pengguna -->
                                <td>
                                    <?= htmlspecialchars(
                                        $row['role'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                                <!-- Nama siswa, guru, atau admin -->
                                <td>
                                    <?= htmlspecialchars(
                                        $row['nama'] ?? '-',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                                <!-- Tombol Edit dan Hapus -->
                                <td>

                                    <!-- Menuju halaman edit user -->
                                    <a
                                        href="edit_user.php?id=<?= (int)$row['id']; ?>"
                                        class="btn-aksi btn-edit"
                                    >
                                        Edit
                                    </a>

                                    <!-- Menuju proses hapus user -->
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

                        <!-- Pesan jika tabel users masih kosong -->
                        <tr>
                            <td colspan="5" class="text-center">
                                Belum ada data pengguna.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>


    <!-- ==================================================
         5. JAVASCRIPT
         ================================================== -->

    <!-- Bootstrap JavaScript untuk dropdown navbar -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery untuk DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables JavaScript -->
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>

    <!-- Integrasi DataTables dengan Bootstrap -->
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // Mengaktifkan fitur pencarian, pengurutan, dan pagination
        $(document).ready(function () {

            $('#tabelUsers').DataTable({
                pageLength: 10,

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