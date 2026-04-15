<?php
/**
 * FILE: admin/hapus.php
 * FUNGSI: Menghapus tugas dari database
 */

session_start();
include '../config/koneksi.php';

// CEK LOGIN ADMIN
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// CEK APAKAH ID ADA
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID tugas tidak ditemukan!";
    header("Location: admin.php");
    exit;
}

$task_id = (int)$_GET['id'];

// HAPUS TUGAS DARI DATABASE
$stmt = mysqli_prepare($conn, "DELETE FROM tasks WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $task_id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success'] = "✅ Tugas berhasil dihapus!";
} else {
    $_SESSION['error'] = "❌ Gagal menghapus tugas: " . mysqli_error($conn);
}

mysqli_stmt_close($stmt);
header("Location: admin.php");
exit;
?>