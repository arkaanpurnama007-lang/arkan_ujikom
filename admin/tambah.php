<?php
/**
 * FILE: admin/tambah.php
 * FUNGSI: Form untuk admin menambahkan tugas baru
 * DIPERBAIKI: Dengan pengecekan kolom database
 */

session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$admin_id = $_SESSION['user']['id'];
$admin_name = $_SESSION['user']['username'];

// Cek apakah kolom admin_name ada
$check = mysqli_query($conn, "SHOW COLUMNS FROM tasks LIKE 'admin_name'");
$has_admin_column = mysqli_num_rows($check) > 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Tugas - Admin</title>
    <link rel="stylesheet" href="../assets/style.css?v=<?= time(); ?>">
    <style>
        .info-admin {
            background: #E8F0FE;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffecb5;
            color: #856404;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 13px;
        }
    </style>
</head>
<body>

<div class="form-wrapper">
    <div class="form-card">
        <h2>📝 Tambah Tugas Baru</h2>
        
        <?php if (!$has_admin_column): ?>
        <div class="warning-box">
            ⚠️ Fitur multi-admin memerlukan kolom tambahan. Silakan jalankan SQL:<br>
            <code>ALTER TABLE tasks ADD COLUMN admin_name VARCHAR(100) NULL, ADD COLUMN admin_id INT NULL;</code>
        </div>
        <?php endif; ?>
        
        <div class="info-admin">
            <span class="badge">👑 Admin</span>
            <strong><?= htmlspecialchars($admin_name) ?></strong>
            <small style="color:#666;">(Tugas akan tercatat atas nama Anda)</small>
        </div>
        
        <form method="POST">
            <label>👤 Pilih User</label>
            <select name="user_id" required>
                <option value="">-- Pilih User --</option>
                <?php
                $users = mysqli_query($conn, "SELECT * FROM users WHERE role='user' ORDER BY username");
                while($user = mysqli_fetch_array($users)) {
                ?>
                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                <?php } ?>
            </select>
            
            <label>📌 Judul Tugas</label>
            <input type="text" name="title" placeholder="Contoh: Membuat Laporan Keuangan" required>
            
            <label>📄 Deskripsi</label>
            <textarea name="desc" placeholder="Jelaskan detail tugas..." rows="4"></textarea>
            
            <label>⏰ Deadline</label>
            <input type="date" name="deadline" min="<?= date('Y-m-d') ?>" required>
            
            <button type="submit">💾 Simpan Tugas</button>
        </form>
        
        <a href="admin.php" class="btn-back">← Kembali ke Dashboard</a>
    </div>
</div>

<?php
if ($_POST) {
    $user_id = (int)$_POST['user_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['desc']);
    $deadline = $_POST['deadline'];
    $today = date('Y-m-d');
    
    if ($deadline < $today) {
        $_SESSION['error'] = "Deadline tidak boleh tanggal yang sudah lewat!";
        header("Location: tambah.php");
        exit;
    }
    
    if ($user_id <= 0) {
        $_SESSION['error'] = "Silakan pilih user yang valid!";
        header("Location: tambah.php");
        exit;
    }
    
    // Cek apakah kolom admin_name ada
    $check = mysqli_query($conn, "SHOW COLUMNS FROM tasks LIKE 'admin_name'");
    $has_admin = mysqli_num_rows($check) > 0;
    
    if ($has_admin) {
        // Dengan kolom admin
        $stmt = mysqli_prepare($conn, "INSERT INTO tasks (user_id, admin_id, admin_name, title, description, deadline, file, file_user, status) VALUES (?, ?, ?, ?, ?, ?, '', '', 'Open')");
        mysqli_stmt_bind_param($stmt, "iissss", $user_id, $admin_id, $admin_name, $title, $description, $deadline);
    } else {
        // Tanpa kolom admin (fallback)
        $stmt = mysqli_prepare($conn, "INSERT INTO tasks (user_id, title, description, deadline, file, file_user, status) VALUES (?, ?, ?, ?, '', '', 'Open')");
        mysqli_stmt_bind_param($stmt, "isss", $user_id, $title, $description, $deadline);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "✅ Tugas berhasil ditambahkan! (oleh: $admin_name)";
        mysqli_stmt_close($stmt);
        header("Location: admin.php");
    } else {
        $_SESSION['error'] = "❌ Gagal menambahkan tugas: " . mysqli_error($conn);
        mysqli_stmt_close($stmt);
        header("Location: tambah.php");
    }
    exit;
}
?>

</body>
</html>