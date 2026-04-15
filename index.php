<?php
/**
 * FILE: index.php
 * FUNGSI: File utama yang pertama kali dijalankan, berfungsi untuk mengarahkan user 
 *         ke halaman yang sesuai berdasarkan status login dan role
 */

session_start(); // Memulai session untuk membaca data user yang sudah login

// Cek apakah user sudah login (ada session user)
if (isset($_SESSION['user'])) {
    // Jika sudah login, cek role user
    if ($_SESSION['user']['role'] == 'admin') {
        // Jika role admin, arahkan ke dashboard admin
        header("Location: admin/admin.php");
    } else {
        // Jika role user biasa, arahkan ke dashboard user
        header("Location: user/dashboard.php");
    }
} else {
    // Jika belum login, arahkan ke halaman login
    header("Location: auth/login.php");
}
?>