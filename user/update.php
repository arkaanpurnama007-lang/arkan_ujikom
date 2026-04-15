<?php
/**
 * FILE: user/update.php
 * FUNGSI: Mengubah status tugas (Open → Progress → Done)
 * CARA KERJA:
 *   1. Menerima parameter id dan status dari URL (GET)
 *   2. Memvalidasi status yang diperbolehkan
 *   3. Update status di database
 *   4. Redirect kembali ke dashboard
 */

session_start(); // Memulai session untuk cek login
include '../config/koneksi.php'; // Koneksi database

// CEK APAKAH USER SUDAH LOGIN
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

// CEK APAKAH PARAMETER id DAN status ADA
if (isset($_GET['id']) && isset($_GET['status'])) {
    
    // Ambil dan bersihkan parameter
    $task_id = (int)$_GET['id']; // ID tugas, konversi ke integer
    $status_baru = $_GET['status']; // Status baru yang diinginkan
    
    // Daftar status yang diperbolehkan (whitelist)
    $status_valid = ['Open', 'In Progress', 'Done'];
    
    // Validasi: apakah status yang diminta valid?
    if (in_array($status_baru, $status_valid)) {
        
        // UPDATE STATUS TUGAS DI DATABASE
        // Gunakan prepared statement untuk keamanan
        $stmt = mysqli_prepare($conn, "UPDATE tasks SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $status_baru, $task_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Status tugas berhasil diubah menjadi '$status_baru'";
        } else {
            $_SESSION['error'] = "Gagal mengubah status tugas";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Status tidak valid!";
    }
}

// Redirect kembali ke dashboard user
header("Location: dashboard.php");
exit;
?>