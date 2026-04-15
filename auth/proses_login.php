<?php
/**
 * FILE: auth/proses_login.php
 * FUNGSI: Memproses data login dari form login.php
 * PROSES: 
 *   1. Menerima username dan password dari form
 *   2. Mencari user di database
 *   3. Memverifikasi password
 *   4. Menyimpan data user ke session jika berhasil
 *   5. Redirect sesuai role
 */

session_start(); // Memulai session untuk menyimpan data user yang login
include '../config/koneksi.php'; // Menyertakan koneksi database

// AMBIL DATA DARI FORM
// mysqli_real_escape_string: membersihkan karakter berbahaya untuk SQL
$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password']; // Password belum di-escape karena akan diverifikasi

// GUNAKAN PREPARED STATEMENT UNTUK MENCEGAH SQL INJECTION
// Prepared statement adalah cara aman untuk menjalankan query SQL
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username); // "s" berarti string
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result); // Ambil data user sebagai array
mysqli_stmt_close($stmt); // Tutup statement

// VERIFIKASI USER DAN PASSWORD
// password_verify() mencocokkan password input dengan hash di database
if ($user && password_verify($password, $user['password'])) {
    
    // LOGIN BERHASIL
    // Simpan data user ke session untuk digunakan di halaman lain
    $_SESSION['user'] = $user;
    
    // Hapus password dari session untuk keamanan
    unset($_SESSION['user']['password']);
    
    // Redirect berdasarkan role
    if ($user['role'] == 'admin') {
        header("Location: ../admin/admin.php"); // Ke halaman admin
    } else {
        header("Location: ../user/dashboard.php"); // Ke halaman user
    }
    
} else {
    // LOGIN GAGAL
    // Simpan pesan error ke session dan redirect kembali ke login
    $_SESSION['error'] = "Username atau password salah!";
    header("Location: login.php");
}

exit; // Hentikan eksekusi script
?>