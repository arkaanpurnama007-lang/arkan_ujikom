<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID tugas tidak ditemukan!";
    header("Location: admin.php");
    exit;
}

$task_id = (int)$_GET['id'];

$stmt = mysqli_prepare($conn, "SELECT * FROM tasks WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $task_id);
mysqli_stmt_execute($stmt);
$task = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$task) {
    $_SESSION['error'] = "Tugas tidak ditemukan!";
    header("Location: admin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reply = trim($_POST['reply']);
    
    if (empty($reply)) {
        $_SESSION['error'] = "Balasan tidak boleh kosong!";
        header("Location: reply.php?id=" . $task_id);
        exit;
    }
    
    $stmt = mysqli_prepare($conn, "UPDATE tasks SET admin_reply = ?, reply_date = NOW() WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $reply, $task_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Balasan berhasil dikirim!";
    } else {
        $_SESSION['error'] = "Gagal mengirim balasan!";
    }
    
    mysqli_stmt_close($stmt);
    header("Location: admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Balas Komentar</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="form-wrapper">
    <div class="form-card">
        <h2>Balas Komentar</h2>
        
        <div style="background:#E8F0FE; padding:15px; border-radius:10px; margin-bottom:20px;">
            <h3>Informasi Tugas</h3>
            <p><strong>Judul:</strong> <?= htmlspecialchars($task['title']) ?></p>
            <p><strong>Deadline:</strong> <?= $task['deadline'] ?></p>
        </div>
        
        <div style="background:#FFF3E0; padding:15px; border-radius:10px; margin-bottom:20px; border-left:4px solid #FF9800;">
            <h4>Komentar User:</h4>
            <p><?= nl2br(htmlspecialchars($task['comment'])) ?></p>
        </div>
        
        <form method="POST">
            <textarea name="reply" rows="5" placeholder="Tulis balasan..." style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;" required></textarea>
            <button type="submit" style="width:100%; padding:10px; background:#0A174F; color:white; border:none; border-radius:8px; margin-top:10px;">Kirim Balasan</button>
        </form>
        
        <a href="admin.php" style="display:block; text-align:center; margin-top:15px;">Kembali</a>
    </div>
</div>
</body>
</html>