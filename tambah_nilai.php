<?php
session_start();
require_once "koneksi.php";

// ========================================
// CEK LOGIN GURU
// ========================================
if (!isset($_SESSION['login']) || ($_SESSION['role'] ?? '') !== 'guru') {
    header("Location: login.php");
    exit;
}

$user_id = (int) ($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);

// ========================================
// AMBIL PARAMETER
// ========================================
$siswa_id = (int) ($_GET['siswa_id'] ?? $_POST['siswa_id'] ?? 0);
$kelas = trim($_GET['kelas'] ?? $_POST['kelas'] ?? '');
$mapel_id = (int) ($_GET['mapel_id'] ?? $_POST['mapel_id'] ?? 0);

if ($user_id <= 0 || $siswa_id <= 0 || $kelas === '' || $mapel_id <= 0) {
    die("Parameter tidak valid.");
}

$pesan_error = "";

// ========================================
// AMBIL DATA GURU
// ========================================
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, nama FROM guru WHERE user_id = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$guru) {
    die("Profil guru tidak ditemukan.");
}

$guru_id = (int) $guru['id'];

// ========================================
// CEK PENUGASAN GURU
// ========================================
$sql = "SELECT
            gm.kelas,
            m.id AS mapel_id,
            m.kode_mapel,
            m.nama_mapel
        FROM guru_mengajar gm
        JOIN mapel m ON m.id = gm.mapel_id
        WHERE gm.guru_id = ?
          AND gm.kelas = ?
          AND gm.mapel_id = ?
        LIMIT 1";

$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, "isi", $guru_id, $kelas, $mapel_id);
mysqli_stmt_execute($stmt);

$penugasan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$penugasan) {
    http_response_code(403);
    die("Akses ditolak. Guru tidak mengajar kelas atau mata pelajaran ini.");
}

// ========================================
// AMBIL DATA SISWA
// ========================================
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, nis, nama_siswa, kelas
     FROM siswa
     WHERE id = ? AND kelas = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "is", $siswa_id, $kelas);
mysqli_stmt_execute($stmt);

$siswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$siswa) {
    die("Data siswa tidak ditemukan atau bukan dari kelas tersebut.");
}

// ========================================
// CEK APAKAH NILAI SUDAH ADA
// ========================================
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id FROM nilai_rapor
     WHERE siswa_id = ? AND mapel_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "ii", $siswa_id, $mapel_id);
mysqli_stmt_execute($stmt);

$nilaiLama = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($nilaiLama) {
    header(
        "Location: input_nilai.php?kelas=" . urlencode($kelas) .
        "&mapel_id=" . $mapel_id
    );
    exit;
}

// ========================================
// PROSES SIMPAN NILAI
// ========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {

    $kkm = $_POST['nilai_kkm'] ?? '';
    $pengetahuan = $_POST['nilai_pengetahuan'] ?? '';
    $keterampilan = $_POST['nilai_keterampilan'] ?? '';
    $keterangan = trim($_POST['keterangan'] ?? '');

    // Validasi angka
    if (
        $kkm === '' ||
        $pengetahuan === '' ||
        $keterampilan === '' ||
        !is_numeric($kkm) ||
        !is_numeric($pengetahuan) ||
        !is_numeric($keterampilan)
    ) {
        $pesan_error = "Semua nilai wajib diisi dengan angka.";
    } elseif (
        $kkm < 0 || $kkm > 100 ||
        $pengetahuan < 0 || $pengetahuan > 100 ||
        $keterampilan < 0 || $keterampilan > 100
    ) {
        $pesan_error = "Nilai harus berada di antara 0 sampai 100.";
    } else {

        // Pastikan nilai masih belum tersimpan
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM nilai_rapor
             WHERE siswa_id = ? AND mapel_id = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "ii", $siswa_id, $mapel_id);
        mysqli_stmt_execute($stmt);

        $cekNilai = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if ($cekNilai) {
            $pesan_error = "Nilai siswa ini sudah tersedia. Silakan gunakan menu Edit.";
        } else {

            // Simpan ke database
            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO nilai_rapor
                (siswa_id, mapel_id, nilai_kkm, nilai_pengetahuan,
                 nilai_keterampilan, keterangan)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            $kkm = (float) $kkm;
            $pengetahuan = (float) $pengetahuan;
            $keterampilan = (float) $keterampilan;

            mysqli_stmt_bind_param(
                $stmt,
                "iiddds",
                $siswa_id,
                $mapel_id,
                $kkm,
                $pengetahuan,
                $keterampilan,
                $keterangan
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);

                header(
                    "Location: input_nilai.php?kelas=" . urlencode($kelas) .
                    "&mapel_id=" . $mapel_id . "&berhasil=1"
                );
                exit;
            } else {
                $pesan_error = "Gagal menyimpan nilai: " . mysqli_stmt_error($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tambah Nilai Siswa - SIACAD SMK</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background: #f4f7fb;
            font-family: Arial, sans-serif;
        }

        .form-container {
            max-width: 750px;
            margin: 35px auto;
            padding: 0 15px;
        }

        .form-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 18px rgba(0,0,0,.08);
        }

        .form-card .card-body {
            padding: 30px;
        }

        .judul {
            color: #172943;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .form-label {
            font-weight: 600;
            color: #334155;
        }

        .form-control {
            padding: 11px;
            border-radius: 8px;
        }

        .info-siswa {
            background: #eaf2ff;
            border-left: 5px solid #0d6efd;
            padding: 16px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .nilai-akhir {
            background: #e8f5e9;
            border: 1px solid #b7dfbd;
            border-radius: 8px;
            padding: 14px;
            font-weight: bold;
            color: #198754;
        }

        @media(max-width: 576px) {
            .form-card .card-body {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<?php require "navbar.php"; ?>

<div class="form-container">

    <div class="card form-card">
        <div class="card-body">

            <h3 class="judul">Tambah Nilai Siswa</h3>
            <p class="text-muted">
                Silakan masukkan nilai siswa pada formulir berikut.
            </p>

            <?php if ($pesan_error !== ''): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($pesan_error) ?>
                </div>
            <?php endif; ?>

            <!-- INFORMASI SISWA -->
            <div class="info-siswa">

                <div class="mb-2">
                    <strong>Nama Guru:</strong>
                    <?= htmlspecialchars($guru['nama']) ?>
                </div>

                <div class="mb-2">
                    <strong>Nama Siswa:</strong>
                    <?= htmlspecialchars($siswa['nama_siswa']) ?>
                </div>

                <div class="mb-2">
                    <strong>NIS:</strong>
                    <?= htmlspecialchars($siswa['nis']) ?>
                </div>

                <div class="mb-2">
                    <strong>Kelas:</strong>
                    <?= htmlspecialchars($siswa['kelas']) ?>
                </div>

                <div>
                    <strong>Mata Pelajaran:</strong>
                    <?= htmlspecialchars($penugasan['nama_mapel']) ?>
                </div>

            </div>

            <!-- FORM INPUT NILAI -->
            <form method="POST">

                <input type="hidden" name="siswa_id"
                       value="<?= $siswa_id ?>">

                <input type="hidden" name="kelas"
                       value="<?= htmlspecialchars($kelas) ?>">

                <input type="hidden" name="mapel_id"
                       value="<?= $mapel_id ?>">

                <!-- KKM -->
                <div class="mb-3">
                    <label class="form-label">KKM</label>

                    <input
                        type="number"
                        name="nilai_kkm"
                        class="form-control"
                        min="0"
                        max="100"
                        value="<?= htmlspecialchars($_POST['nilai_kkm'] ?? '75') ?>"
                        required
                    >
                </div>

                <!-- PENGETAHUAN -->
                <div class="mb-3">
                    <label class="form-label">Nilai Pengetahuan</label>

                    <input
                        type="number"
                        name="nilai_pengetahuan"
                        id="pengetahuan"
                        class="form-control"
                        min="0"
                        max="100"
                        value="<?= htmlspecialchars($_POST['nilai_pengetahuan'] ?? '') ?>"
                        placeholder="Masukkan nilai pengetahuan"
                        required
                    >
                </div>

                <!-- KETERAMPILAN -->
                <div class="mb-3">
                    <label class="form-label">Nilai Keterampilan</label>

                    <input
                        type="number"
                        name="nilai_keterampilan"
                        id="keterampilan"
                        class="form-control"
                        min="0"
                        max="100"
                        value="<?= htmlspecialchars($_POST['nilai_keterampilan'] ?? '') ?>"
                        placeholder="Masukkan nilai keterampilan"
                        required
                    >
                </div>

                <!-- NILAI AKHIR OTOMATIS -->
                <div class="mb-3">
                    <label class="form-label">Nilai Akhir</label>

                    <div class="nilai-akhir">
                        <span id="nilaiAkhir">-</span>
                    </div>

                    <small class="text-muted">
                        Nilai akhir dihitung dari rata-rata pengetahuan
                        dan keterampilan.
                    </small>
                </div>

                <!-- KETERANGAN -->
                <div class="mb-4">
                    <label class="form-label">Keterangan Guru</label>

                    <textarea
                        name="keterangan"
                        class="form-control"
                        rows="3"
                        placeholder="Contoh: Baik, rajin, aktif, perlu ditingkatkan..."
                    ><?= htmlspecialchars($_POST['keterangan'] ?? '') ?></textarea>
                </div>

                <!-- TOMBOL -->
                <div class="d-flex gap-2 flex-wrap">

                    <button type="submit" name="simpan"
                            class="btn btn-primary px-4">
                        💾 Simpan Nilai
                    </button>

                    <a
                        href="input_nilai.php?kelas=<?= urlencode($kelas) ?>&mapel_id=<?= $mapel_id ?>"
                        class="btn btn-secondary px-4"
                    >
                        Kembali
                    </a>

                </div>

            </form>

        </div>
    </div>

</div>

<script>
function hitungNilai() {
    const pengetahuan = document.getElementById('pengetahuan').value;
    const keterampilan = document.getElementById('keterampilan').value;
    const hasil = document.getElementById('nilaiAkhir');

    if (pengetahuan !== '' && keterampilan !== '') {
        const akhir = Math.round(
            (parseFloat(pengetahuan) + parseFloat(keterampilan)) / 2
        );

        hasil.textContent = akhir;
    } else {
        hasil.textContent = '-';
    }
}

document.getElementById('pengetahuan').addEventListener('input', hitungNilai);
document.getElementById('keterampilan').addEventListener('input', hitungNilai);

hitungNilai();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
