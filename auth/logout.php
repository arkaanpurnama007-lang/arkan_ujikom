<?php
/**
 * FILE: auth/logout.php
 * FUNGSI: Menghapus session user (logout)
 * PROSES:
 *   1. Memulai session
 *   2. Menghapus semua data session
 *   3. Redirect ke halaman login
 */

session_start(); // Memulai session yang sedang berjalan

// HAPUS SEMUA DATA SESSION
session_destroy(); // Menghancurkan seluruh session

// Redirect ke halaman login
header("Location: login.php");
exit; // Hentikan eksekusi script
?>