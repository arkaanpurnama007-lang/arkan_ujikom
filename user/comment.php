<?php
/**
 * FILE: user/comment.php
 * FUNGSI: Menyimpan komentar dari user
 * UPDATE: Sekarang user bisa lihat siapa admin yang akan membaca komentarnya
 */

session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_POST['id'])) {
    $_SESSION['error'] = "ID tugas tidak ditemukan!";
    header("Location: dashboard.php");
    exit;
}

$task_id = (int)$_POST['id'];
$komentar = trim($_POST['comment']);

if (empty($komentar)) {
    $_SESSION['error'] = "Komentar tidak boleh kosong!";
    header("Location: dashboard.php");
    exit;
}

// Ambil info admin untuk ditampilkan di komentar (opsional)
$info_stmt = mysqli_prepare($conn, "SELECT admin_name FROM tasks WHERE id = ?");
mysqli_stmt_bind_param($info_stmt, "i", $task_id);
mysqli_stmt_execute($info_stmt);
$task_info = mysqli_fetch_assoc(mysqli_stmt_get_result($info_stmt));
mysqli_stmt_close($info_stmt);

$admin_name = $task_info['admin_name'] ?? 'Admin';

// Simpan komentar
$stmt = mysqli_prepare($conn, "UPDATE tasks SET comment = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, "si", $komentar, $task_id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success'] = "💬 Komentar untuk $admin_name berhasil disimpan!";
} else {
    $_SESSION['error'] = "Gagal menyimpan komentar!";
}

mysqli_stmt_close($stmt);
header("Location: dashboard.php");
exit;
?>