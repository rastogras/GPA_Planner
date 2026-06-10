<?php
// pages/profile.php
require_once '../includes/auth.php';
require_once '../includes/db.php';

Auth::requireLogin();
$db        = Database::getInstance();
$studentId = $_SESSION['student_id'];
$student   = $db->query('SELECT * FROM students WHERE id = ?', 'i', $studentId)[0] ?? [];

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['full_name']  ?? '');
    $course = trim($_POST['course']   ?? '');
    $dept  = trim($_POST['department'] ?? '');
    $sems  = intval($_POST['total_semesters'] ?? 8);

    $db->query(
        'UPDATE students SET full_name=?, course=?, department=?, total_semesters=? WHERE id=?',
        'sssii', $name, $course, $dept, $sems, $studentId
    );
    $_SESSION['student_name'] = $name;
    $success = 'Profile updated successfully.';
    $student = $db->query('SELECT * FROM students WHERE id = ?', 'i', $studentId)[0] ?? [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile — <?= APP_NAME ?></title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="layout">
<aside class="sidebar">
    <div class="sidebar-logo">🎓 GPA Planner</div>
    <nav class="sidebar-nav">
        <a href="../dashboard.php" class="nav-link">📊 Dashboard</a>
        <a href="semesters.php" class="nav-link">📚 Semesters</a>
        <a href="projections.php" class="nav-link">🎯 Projections</a>
        <a href="profile.php" class="nav-link active">👤 Profile</a>
    </nav>
    <div class="sidebar-footer">
        <span><?= htmlspecialchars($_SESSION['student_name']) ?></span>
        <a href="../logout.php" class="btn-logout">Sign out</a>
    </div>
</aside>

<main class="main-content">
    <div class="page-header"><h2>My Profile</h2></div>

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

    <div class="card" style="max-width:520px;">
        <form method="POST" action="profile.php">
            <div class="form-group">
                <label>Student ID</label>
                <input type="text" value="<?= htmlspecialchars($student['student_id']) ?>" disabled style="opacity:.6;">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="text" value="<?= htmlspecialchars($student['email']) ?>" disabled style="opacity:.6;">
            </div>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($student['full_name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Course / Programme</label>
                <input type="text" name="course" value="<?= htmlspecialchars($student['course'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Department</label>
                <input type="text" name="department" value="<?= htmlspecialchars($student['department'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Total Semesters in Programme</label>
                <select name="total_semesters">
                    <?php for ($i = 6; $i <= 12; $i++): ?>
                    <option value="<?= $i ?>" <?= ($student['total_semesters'] ?? 8) == $i ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</main>
</div>
</body>
</html>
