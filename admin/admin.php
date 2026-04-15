<?php
/**
 * FILE: admin/admin.php
 * FUNGSI: Dashboard admin dengan fitur balas komentar
 */

session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$admin_id = $_SESSION['user']['id'];
$admin_name = $_SESSION['user']['username'];
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$filter_own = isset($_GET['filter_own']) ? $_GET['filter_own'] : 'all';

function tampilkanFileAdmin($file_name) {
    if (empty($file_name)) {
        echo "<small style='color:#888;'>📭 Belum ada file</small>";
        return;
    }
    $ekstensi = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $bisa_preview = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    
    if (in_array($ekstensi, $bisa_preview)) {
        echo "<a href='../uploads/$file_name' target='_blank' style='background:#E8F0FE; padding:6px 12px; border-radius:6px; text-decoration:none; display:inline-block;'>👁️ Lihat File</a>";
    } else {
        echo "<a href='../uploads/$file_name' download style='background:#E8F0FE; padding:6px 12px; border-radius:6px; text-decoration:none; display:inline-block;'>📥 Download File</a>";
    }
}

function getTasks($conn, $status, $search, $filter_own, $admin_id) {
    if ($filter_own == 'own') {
        $stmt = mysqli_prepare($conn, "
            SELECT tasks.*, users.username as user_name 
            FROM tasks JOIN users ON tasks.user_id = users.id 
            WHERE tasks.status = ? AND tasks.title LIKE CONCAT('%', ?, '%') 
            AND tasks.admin_id = ? ORDER BY tasks.deadline ASC
        ");
        mysqli_stmt_bind_param($stmt, "ssi", $status, $search, $admin_id);
    } else {
        $stmt = mysqli_prepare($conn, "
            SELECT tasks.*, users.username as user_name, 
                   COALESCE(tasks.admin_name, 'System') as admin_name
            FROM tasks JOIN users ON tasks.user_id = users.id 
            WHERE tasks.status = ? AND tasks.title LIKE CONCAT('%', ?, '%')
            ORDER BY tasks.deadline ASC
        ");
        mysqli_stmt_bind_param($stmt, "ss", $status, $search);
    }
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function showNotification() {
    if (isset($_SESSION['success'])) {
        echo '<div class="alert success">✅ ' . htmlspecialchars($_SESSION['success']) . '</div>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo '<div class="alert error">❌ ' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
}

$open = getTasks($conn, 'Open', $search, $filter_own, $admin_id);
$progress = getTasks($conn, 'In Progress', $search, $filter_own, $admin_id);
$done = getTasks($conn, 'Done', $search, $filter_own, $admin_id);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Arkan Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css?v=<?= time(); ?>">
    <script>
        function confirmLogout() {
            if (confirm("Apakah kamu yakin ingin logout?")) {
                window.location.href = "../auth/logout.php";
            }
        }
        
        function confirmDelete(id) {
            if (confirm("Yakin hapus tugas ini?")) {
                window.location.href = 'hapus.php?id=' + id;
            }
        }
    </script>
</head>
<body>

<?php showNotification(); ?>

<div class="navbar">
    <h3> Admin - <?= htmlspecialchars($admin_name) ?></h3>
    <a href="logout.php" class="logout" onclick="return confirm('Apakah Anda yakin ingin logout?')">Logout</a>
</div>

<div class="stats">
    <div class="box open">📋 Open: <?= mysqli_num_rows($open) ?></div>
    <div class="box progress">⏳ Progress: <?= mysqli_num_rows($progress) ?></div>
    <div class="box done">✅ Done: <?= mysqli_num_rows($done) ?></div>
</div>

<div class="filter-bar">
    <a href="?filter_own=all&search=<?= urlencode($search) ?>" class="filter-btn <?= $filter_own == 'all' ? 'active' : '' ?>">📋 Semua Tugas</a>
    <a href="?filter_own=own&search=<?= urlencode($search) ?>" class="filter-btn <?= $filter_own == 'own' ? 'active' : '' ?>">👑 Tugas Saya</a>
</div>

<div class="top-bar">
    <form method="GET" class="search-box">
        <input type="hidden" name="filter_own" value="<?= $filter_own ?>">
        <input type="text" name="search" placeholder="🔍 Cari tugas..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Cari</button>
    </form>
    <a href="tambah.php" class="btn-add">+ Tambah Tugas</a>
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
            <div class="admin-badge">👑 <?= htmlspecialchars($t['admin_name'] ?? 'System') ?></div>
            <b><?= htmlspecialchars($t['title']) ?></b>
            <p><?= nl2br(htmlspecialchars($t['description'])) ?></p>
            <div class="task-meta">
                <span>👤 <?= htmlspecialchars($t['user_name']) ?></span>
                <span>📅 <?= date('d/m/Y', strtotime($t['deadline'])) ?></span>
            </div>
            <div class="actions">
                <a href="edit.php?id=<?= $t['id'] ?>" class="btn-edit">✏️ Edit</a>
                <a href="hapus.php?id=<?= $t['id'] ?>" class="btn-delete" onclick="return confirm('Yakin hapus?')">🗑️ Hapus</a>
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
            <div class="admin-badge">👑 <?= htmlspecialchars($t['admin_name'] ?? 'System') ?></div>
            <b><?= htmlspecialchars($t['title']) ?></b>
            <p><?= nl2br(htmlspecialchars($t['description'])) ?></p>
            <div class="task-meta">
                <span>👤 <?= htmlspecialchars($t['user_name']) ?></span>
                <span>📅 <?= date('d/m/Y', strtotime($t['deadline'])) ?></span>
            </div>
            <?php if(!empty($t['file_user'])): ?>
            <div class="file-area"><?php tampilkanFileAdmin($t['file_user']); ?></div>
            <?php endif; ?>
            <div class="actions">
                <a href="edit.php?id=<?= $t['id'] ?>" class="btn-edit">✏️ Edit</a>
                <a href="hapus.php?id=<?= $t['id'] ?>" class="btn-delete" onclick="return confirm('Yakin hapus?')">🗑️ Hapus</a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    
    <!-- KOLOM DONE (DENGAN TOMBOL BALAS KOMENTAR) -->
    <div class="column">
        <h4>✅ Done</h4>
        <?php if (mysqli_num_rows($done) == 0): ?>
            <div class="empty-state">✨ Belum ada tugas selesai</div>
        <?php endif; ?>
        <?php while($t = mysqli_fetch_assoc($done)): ?>
        <div class="card">
            <div class="admin-badge">👑 <?= htmlspecialchars($t['admin_name'] ?? 'System') ?></div>
            <b><?= htmlspecialchars($t['title']) ?></b>
            <p><?= nl2br(htmlspecialchars($t['description'])) ?></p>
            <div class="task-meta">
                <span>👤 <?= htmlspecialchars($t['user_name']) ?></span>
                <span>📅 <?= date('d/m/Y', strtotime($t['deadline'])) ?></span>
            </div>
            
            <?php if(!empty($t['file_user'])): ?>
            <div class="file-area"><?php tampilkanFileAdmin($t['file_user']); ?></div>
            <?php endif; ?>
            
            <!-- KOMENTAR USER -->
            <?php if(!empty($t['comment'])): ?>
            <div class="user-comment">
                <strong>💬 Komentar User:</strong><br>
                <?= nl2br(htmlspecialchars($t['comment'])) ?>
            </div>
            <?php endif; ?>
            
            <!-- BALASAN ADMIN (JIKA SUDAH ADA) -->
            <?php if(!empty($t['admin_reply'])): ?>
            <div class="admin-reply">
                <strong>👑 Balasan Admin (<?= htmlspecialchars($t['admin_name'] ?? 'System') ?>):</strong><br>
                <?= nl2br(htmlspecialchars($t['admin_reply'])) ?>
                <?php if(!empty($t['reply_date'])): ?>
                <br><small>📅 Dibalas: <?= date('d/m/Y H:i', strtotime($t['reply_date'])) ?></small>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- TOMBOL BALAS (HANYA JIKA ADA KOMENTAR DAN BELUM DIBALAS) -->
            <div class="actions" style="margin-top: 12px;">
                <?php if(!empty($t['comment']) && empty($t['admin_reply'])): ?>
                <!-- Cari bagian ini di admin/admin.php -->
                <a href="reply.php?id=<?= $t['id'] ?>" class="btn-reply">💬 Balas Komentar</a>
                <?php endif; ?>
                <a href="edit.php?id=<?= $t['id'] ?>" class="btn-edit">✏️ Edit</a>
                <a href="hapus.php?id=<?= $t['id'] ?>" class="btn-delete" onclick="return confirm('Yakin hapus?')">🗑️ Hapus</a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<style>
    .btn-reply {
        background: #FF9800;
        color: white;
        padding: 6px 14px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 12px;
        font-weight: bold;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s;
    }
    .btn-reply:hover {
        background: #E65100;
        transform: translateY(-1px);
    }
    .admin-reply {
        background: #E8F0FE;
        padding: 10px;
        border-radius: 8px;
        margin-top: 10px;
        border-left: 3px solid #0A174F;
        font-size: 13px;
    }
</style>

</body>
</html>