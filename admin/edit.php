<?php
/**
 * FILE: admin/edit.php
 * FUNGSI: Form untuk admin mengedit tugas yang sudah ada
 * PROSES:
 *   1. Menampilkan data tugas yang akan diedit
 *   2. Menyimpan perubahan ke database
 */

session_start(); // Memulai session
include '../config/koneksi.php'; // Koneksi database

// CEK APAKAH ADMIN YANG LOGIN
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// AMBIL ID TUGAS DARI URL
$task_id = (int)$_GET['id'];

// AMBIL DATA TUGAS YANG AKAN DIEDIT
$stmt = mysqli_prepare($conn, "SELECT * FROM tasks WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $task_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$task = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Jika tugas tidak ditemukan
if (!$task) {
    $_SESSION['error'] = "Tugas tidak ditemukan!";
    header("Location: admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Tugas - Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<div class="form-wrapper">
    <div class="form-card">
        <h2>✏️ Edit Tugas</h2>
        
        <!-- FORM EDIT TUGAS -->
        <form method="POST">
            <!-- Judul Tugas -->
            <label>📌 Judul Tugas</label>
            <input type="text" name="title" value="<?= htmlspecialchars($task['title']) ?>" required>
            
            <!-- Deskripsi Tugas -->
            <label>📄 Deskripsi</label>
            <textarea name="desc" rows="4"><?= htmlspecialchars($task['description']) ?></textarea>
            
            <!-- Deadline -->
            <label>⏰ Deadline</label>
            <input type="date" name="deadline" value="<?= $task['deadline'] ?>" min="<?= date('Y-m-d') ?>" required>
            
            <!-- Tombol Update -->
            <button type="submit">💾 Update Tugas</button>
        </form>
        
        <!-- Tombol Kembali -->
        <a href="admin.php" class="btn-back">← Kembali ke Dashboard</a>
    </div>
</div>

<?php
// PROSES UPDATE DATA (dijalankan saat form disubmit)
if ($_POST) {
    
    // Ambil data dari form
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['desc']);
    $deadline = $_POST['deadline'];
    $today = date('Y-m-d');
    
    // VALIDASI: Deadline tidak boleh kurang dari hari ini
    if ($deadline < $today) {
        $_SESSION['error'] = "Deadline tidak boleh tanggal yang sudah lewat!";
        header("Location: edit.php?id=" . $task_id);
        exit;
    }
    
    // UPDATE KE DATABASE
    $stmt = mysqli_prepare($conn, "UPDATE tasks SET title = ?, description = ?, deadline = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "sssi", $title, $description, $deadline, $task_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "✅ Tugas berhasil diupdate!";
    } else {
        $_SESSION['error'] = "❌ Gagal mengupdate tugas!";
    }
    
    mysqli_stmt_close($stmt);
    header("Location: admin.php");
    exit;
}
?>

</body>
</html>