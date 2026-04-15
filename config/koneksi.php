<?php
/**
 * FILE: config/koneksi.php
 * FUNGSI: Menghubungkan aplikasi ke database MySQL
 * FILE INI DI-INCLUDE OLEH: Hampir semua file PHP yang membutuhkan akses database
 */

// Membuat koneksi ke database
// Parameter: (host, username, password, nama_database)
$conn = mysqli_connect("localhost", "root", "", "ujikom1");

// Memeriksa apakah koneksi berhasil
if (!$conn) {
    // Jika gagal, hentikan program dan tampilkan pesan error
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Mengatur charset ke UTF-8 agar bisa menyimpan karakter khusus (seperti emoji, aksen, dll)
mysqli_set_charset($conn, "utf8");

// CATATAN: Untuk keamanan di production, ganti username dan password default
?>