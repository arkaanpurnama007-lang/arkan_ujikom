<?php
/**
 * FILE: auth/register.php
 * FUNGSI: Menampilkan halaman form registrasi untuk user baru
 * USER YANG MENGAKSES: User baru yang belum punya akun
 * PROSES: Form akan dikirim ke proses_register.php
 */

session_start(); // Memulai session untuk membaca pesan error
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Task Manager</title>
    <link rel="stylesheet" href="../assets/style.css?v=2">
</head>

<body class="auth-container">
    <div class="auth-box">
        <?php
        // TAMPILKAN PESAN ERROR JIKA ADA
        // Error bisa berupa: username sudah dipakai, password terlalu pendek, dll
        if (isset($_SESSION['error'])) {
            echo "<div style='background:#ffebee; color:#c62828; padding:10px; border-radius:8px; margin-bottom:15px; border-left:4px solid #c62828; text-align:left;'>";
            echo "❌ " . htmlspecialchars($_SESSION['error']);
            echo "</div>";
            unset($_SESSION['error']);
        }
        ?>
        
        <!-- FORM REGISTRASI -->
        <!-- action: proses_register.php (file yang memproses pendaftaran) -->
        <form action="proses_register.php" method="POST">
            <h2>📝 Register</h2>
            
            <!-- Input Username -->
            <input type="text" name="username" placeholder="Username" required autofocus>
            
            <!-- Input Password dengan minimal 6 karakter -->
            <input type="password" name="password" placeholder="Password (min. 6 karakter)" required>
            
            <!-- Pilihan Role (Admin atau User) -->
            <select name="role">
                <option value="user">👤 User</option>
                <option value="admin">👑 Admin</option>
            </select>
            
            <!-- Tombol Daftar -->
            <button type="submit">Daftar</button>
        </form>
        
        <!-- Link kembali ke halaman login -->
        <a href="login.php">🔙 Sudah punya akun? Login</a>
    </div>
</body>
</html>