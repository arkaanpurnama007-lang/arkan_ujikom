<?php
/**
 * FILE: auth/login.php
 * FUNGSI: Menampilkan halaman form login untuk user
 * USER YANG MENGAKSES: Semua user yang belum login
 * PROSES: Form akan dikirim ke proses_login.php
 */

session_start(); // Memulai session untuk membaca pesan error/success
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Task Manager</title>
    <link rel="stylesheet" href="../assets/style.css?v=2">
</head>

<body class="auth-container">
    <div class="auth-box">
        <?php
        // TAMPILKAN PESAN ERROR JIKA ADA
        // Pesan error muncul saat login gagal
        if (isset($_SESSION['error'])) {
            echo "<div style='background:#ffebee; color:#c62828; padding:10px; border-radius:8px; margin-bottom:15px; border-left:4px solid #c62828; text-align:left;'>";
            echo "❌ " . htmlspecialchars($_SESSION['error']);
            echo "</div>";
            unset($_SESSION['error']); // Hapus pesan setelah ditampilkan
        }
        
        // TAMPILKAN PESAN SUKSES JIKA ADA
        // Pesan sukses muncul setelah registrasi berhasil
        if (isset($_SESSION['success'])) {
            echo "<div style='background:#e8f5e9; color:#2e7d32; padding:10px; border-radius:8px; margin-bottom:15px; border-left:4px solid #2e7d32; text-align:left;'>";
            echo "✅ " . htmlspecialchars($_SESSION['success']);
            echo "</div>";
            unset($_SESSION['success']);
        }
        ?>
        
        <!-- FORM LOGIN -->
        <!-- action: proses_login.php (file yang memproses login) -->
        <!-- method: POST (data dikirim secara tersembunyi) -->
        <form action="proses_login.php" method="POST">
            <h2>🔐 Login</h2>
            
            <!-- Input Username -->
            <input type="text" name="username" placeholder="Username" required autofocus>
            
            <!-- Input Password -->
            <input type="password" name="password" placeholder="Password" required>
            
            <!-- Tombol Submit -->
            <button type="submit">Masuk</button>
        </form>
        
        <!-- Link ke halaman registrasi -->
        <a href="register.php">✨ Belum punya akun? Daftar</a>
    </div>
</body>
</html>