<?php
/**
 * FILE: user/upload.php
 * FUNGSI: Memproses upload file tugas dari user
 * FITUR KEAMANAN:
 *   - Validasi tipe file (hanya ekstensi tertentu)
 *   - Batasan ukuran file (max 5MB)
 *   - Nama file diubah menjadi unik (timestamp + random)
 * 
 * JENIS FILE:
 *   - Preview (bisa Lihat): jpg, jpeg, png, gif, pdf
 *   - Download (tidak bisa preview): doc, docx, xls, xlsx, zip, rar
 */

session_start();
include '../config/koneksi.php';

// CEK APAKAH ADA FILE YANG DIUPLOAD
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = "Gagal upload file! Silakan coba lagi.";
    header("Location: dashboard.php");
    exit;
}

$task_id = (int)$_POST['id'];

// ===== VALIDASI 1: EKSTENSI FILE =====
$ekstensi_preview = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];      // Bisa dilihat langsung
$ekstensi_download = ['doc', 'docx', 'xls', 'xlsx', 'zip', 'rar', 'txt']; // Hanya download
$ekstensi_diperbolehkan = array_merge($ekstensi_preview, $ekstensi_download);

$ekstensi_file = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

if (!in_array($ekstensi_file, $ekstensi_diperbolehkan)) {
    $_SESSION['error'] = "Tipe file tidak diperbolehkan! Hanya: " . implode(', ', $ekstensi_diperbolehkan);
    header("Location: dashboard.php");
    exit;
}

// ===== VALIDASI 2: UKURAN FILE =====
$max_size = 5 * 1024 * 1024; // 5 Megabyte
if ($_FILES['file']['size'] > $max_size) {
    $_SESSION['error'] = "Ukuran file terlalu besar! Maksimal 5MB";
    header("Location: dashboard.php");
    exit;
}

// ===== BUAT NAMA FILE UNIK =====
$tmp_file = $_FILES['file']['tmp_name'];
$random_string = bin2hex(random_bytes(8));
$nama_unik = time() . '_' . $random_string . '.' . $ekstensi_file;

// ===== PINDAHKAN FILE KE FOLDER UPLOADS =====
if (move_uploaded_file($tmp_file, "../uploads/" . $nama_unik)) {
    
    // UPDATE NAMA FILE DI DATABASE
    $stmt = mysqli_prepare($conn, "UPDATE tasks SET file_user = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $nama_unik, $task_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "✅ File berhasil diupload!";
    } else {
        $_SESSION['error'] = "Gagal menyimpan data ke database!";
        unlink("../uploads/" . $nama_unik); // Hapus file jika gagal
    }
    
    mysqli_stmt_close($stmt);
    
} else {
    $_SESSION['error'] = "Gagal memindahkan file! Periksa permission folder uploads/";
}

header("Location: dashboard.php");
exit;
?>