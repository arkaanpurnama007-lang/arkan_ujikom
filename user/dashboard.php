<?php
/**
 * FILE: user/dashboard.php
 * FUNGSI: Dashboard user dengan fitur melihat balasan admin
 */

session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

function tampilkanFile($file_name) {
    if (empty($file_name)) return;
    $ekstensi = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $bisa_preview = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    
    if (in_array($ekstensi, $bisa_preview)) {
        echo "<a class='file-link view' href='../uploads/$file_name' target='_blank'>👁️ Lihat File</a>";
    } else {
        echo "<a class='file-link download' href='../uploads/$file_name' download>📥 Download File</a>";
    }
}

$open = mysqli_query($conn, "
    SELECT tasks.*, COALESCE(admin_name, 'System') as pemberi 
    FROM tasks WHERE user_id = $user_id AND status = 'Open' ORDER BY deadline ASC");

$progress = mysqli_query($conn, "
    SELECT tasks.*, COALESCE(admin_name, 'System') as pemberi 
    FROM tasks WHERE user_id = $user_id AND status = 'In Progress' ORDER BY deadline ASC");

$done = mysqli_query($conn, "
    SELECT tasks.*, COALESCE(admin_name, 'System') as pemberi, admin_reply, reply_date
    FROM tasks WHERE user_id = $user_id AND status = 'Done' ORDER BY deadline ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css?v=<?= time(); ?>">
</head>
<body>

<div class="navbar">
    <h3>📋 <?= htmlspecialchars($_SESSION['user']['username']) ?></h3>
    <a href="../auth/logout.php">🚪 Logout</a>
</div>

<div class="stats">
    <div class="box open">Open: <?= mysqli_num_rows($open) ?></div>
    <div class="box progress">Progress: <?= mysqli_num_rows($progress) ?></div>
    <div class="box done">Done: <?= mysqli_num_rows($done) ?></div>
</div>

<div class="notification-container">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert success">✅ <?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert error">❌ <?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
</div>

<div class="board">
    <!-- KOLOM OPEN -->
    <div class="column">
        <h4>📋 Open</h4>
        <?php if (mysqli_num_rows($open) == 0): ?>
            <div class="empty-state">✨ Tidak ada tugas</div>
        <?php endif; ?>
        <?php while($t = mysqli_fetch_assoc($open)): ?>
        <div class="card">
            <div class="admin-badge">👑 dari: <?= htmlspecialchars($t['pemberi']) ?></div>
            <b><?= htmlspecialchars($t['title']) ?></b>
            <p><?= nl2br(htmlspecialchars($t['description'])) ?></p>
            <div class="task-meta">
                <span>📅 Deadline: <?= date('d/m/Y', strtotime($t['deadline'])) ?></span>
            </div>
            <div class="actions">
                <a href="update.php?id=<?= $t['id'] ?>&status=In Progress" class="move">🚀 Mulai Kerjakan</a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    
    <!-- KOLOM PROGRESS -->
    <div class="column">
        <h4>⏳ In Progress</h4>
        <?php if (mysqli_num_rows($progress) == 0): ?>
            <div class="empty-state">✨ Tidak ada tugas</div>
        <?php endif; ?>
        <?php while($t = mysqli_fetch_assoc($progress)): ?>
        <div class="card">
            <div class="admin-badge">👑 dari: <?= htmlspecialchars($t['pemberi']) ?></div>
            <b><?= htmlspecialchars($t['title']) ?></b>
            <p><?= nl2br(htmlspecialchars($t['description'])) ?></p>
            
            <div class="upload-box">
                <?php if(empty($t['file_user'])): ?>
                <form action="upload.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <input type="file" name="file" required>
                    <small>📄 Format: JPG, PNG, PDF, DOC, DOCX, XLS, XLSX, ZIP | Max 5MB</small>
                    <button type="submit">📤 Upload Tugas</button>
                </form>
                <?php else: ?>
                    <?php tampilkanFile($t['file_user']); ?>
                <?php endif; ?>
            </div>
            
            <div class="task-meta">
                <span>📅 Deadline: <?= date('d/m/Y', strtotime($t['deadline'])) ?></span>
            </div>
            
            <div class="actions">
                <a href="update.php?id=<?= $t['id'] ?>&status=Done" class="move">✅ Tandai Selesai</a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    
    <!-- KOLOM DONE (DENGAN BALASAN ADMIN) -->
    <div class="column">
        <h4>✅ Done</h4>
        <?php if (mysqli_num_rows($done) == 0): ?>
            <div class="empty-state">✨ Belum ada tugas selesai</div>
        <?php endif; ?>
        <?php while($t = mysqli_fetch_assoc($done)): ?>
        <div class="card">
            <div class="admin-badge">👑 dari: <?= htmlspecialchars($t['pemberi']) ?></div>
            <b><?= htmlspecialchars($t['title']) ?></b>
            <p><?= nl2br(htmlspecialchars($t['description'])) ?></p>
            
            <?php if(!empty($t['file_user'])): ?>
            <div class="file-area"><?php tampilkanFile($t['file_user']); ?></div>
            <?php endif; ?>
            
            <!-- TAMPILKAN BALASAN ADMIN JIKA ADA -->
            <?php if(!empty($t['admin_reply'])): ?>
            <div class="admin-reply-user">
                <strong>👑 Balasan dari Admin (<?= htmlspecialchars($t['pemberi']) ?>):</strong><br>
                <?= nl2br(htmlspecialchars($t['admin_reply'])) ?>
                <?php if(!empty($t['reply_date'])): ?>
                <br><small>📅 Dibalas: <?= date('d/m/Y H:i', strtotime($t['reply_date'])) ?></small>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <div class="comment-box">
                <form action="comment.php" method="POST">
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <textarea name="comment" placeholder="💬 Tulis komentar untuk admin..."></textarea>
                    <button type="submit">Kirim Komentar</button>
                </form>
                
                <?php if(!empty($t['comment'])): ?>
                <div class="user-comment-small">
                    <strong>💬 Komentar Anda:</strong><br>
                    <?= nl2br(htmlspecialchars($t['comment'])) ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="task-meta">
                <span>📅 Deadline: <?= date('d/m/Y', strtotime($t['deadline'])) ?></span>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<style>
    .admin-reply-user {
        background: #E8F0FE;
        padding: 10px;
        border-radius: 8px;
        margin-top: 10px;
        margin-bottom: 10px;
        border-left: 3px solid #0A174F;
        font-size: 13px;
    }
    .user-comment-small {
        margin-top: 10px;
        padding: 8px;
        background: #fff;
        border-radius: 6px;
        border-left: 2px solid #62B6CB;
    }
    .file-area {
        margin: 10px 0;
        padding: 8px;
        background: #F4FBFF;
        border-radius: 8px;
        text-align: center;
    }
</style>

</body>
</html>