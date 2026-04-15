<?php
/**
 * FILE: auth/proses_register.php
 * FUNGSI: Memproses data registrasi dari form register.php
 * PROSES:
 *   1. Validasi input (username unik, password minimal 6 karakter)
 *   2. Hash password untuk keamanan
 *   3. Simpan user baru ke database
 *   4. Redirect ke login dengan pesan sukses/error
 */

session_start(); // Memulai session untuk menyimpan pesan
include '../config/koneksi.php'; // Koneksi database

// AMBIL DATA DARI FORM
$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password'];
$role = $_POST['role']; // 'admin' atau 'user'

// VALIDASI 1: Cek apakah username sudah terdaftar
$stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt); // Simpan hasil query

if (mysqli_stmt_num_rows($stmt) > 0) {
    // Username sudah digunakan
    $_SESSION['error'] = "Username '$username' sudah digunakan! Silakan pilih username lain.";
    mysqli_stmt_close($stmt);
    header("Location: register.php");
    exit;
}
mysqli_stmt_close($stmt);

// VALIDASI 2: Minimal password 6 karakter
if (strlen($password) < 6) {
    $_SESSION['error'] = "Password minimal 6 karakter untuk keamanan!";
    header("Location: register.php");
    exit;
}

// HASH PASSWORD
// password_hash() mengenkripsi password agar aman disimpan di database
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// SIMPAN USER BARU KE DATABASE
// Gunakan prepared statement untuk keamanan
$stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($stmt, "sss", $username, $hashed_password, $role);

if (mysqli_stmt_execute($stmt)) {
    // Registrasi berhasil
    $_SESSION['success'] = "Registrasi berhasil! Silakan login dengan akun Anda.";
    mysqli_stmt_close($stmt);
    header("Location: login.php");
} else {
    // Registrasi gagal karena error database
    $_SESSION['error'] = "Registrasi gagal: " . mysqli_error($conn);
    mysqli_stmt_close($stmt);
    header("Location: register.php");
}

exit;
?>