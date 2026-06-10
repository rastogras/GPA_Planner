<?php
// pages/semesters.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/gpa.php';

Auth::requireLogin();
$db        = Database::getInstance();
$studentId = $_SESSION['student_id'];

// ── Handle form submissions ──────────────────────────────────────────────────

// Add semester
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_semester') {
        $name    = trim($_POST['semester_name'] ?? '');
        $year    = intval($_POST['year_of_study'] ?? 1);
        $semNum  = intval($_POST['semester_number'] ?? 1);
        if ($name) {
            $db->query(
                'INSERT INTO semesters (student_id, semester_name, year_of_study, semester_number) VALUES (?,?,?,?)',
                'isii', $studentId, $name, $year, $semNum
            );
        }
    }

    if ($action === 'delete_semester') {
        $semId = intval($_POST['semester_id'] ?? 0);
        // Verify ownership
        $own = $db->query('SELECT id FROM semesters WHERE id = ? AND student_id = ?', 'ii', $semId, $studentId);
        if (!empty($own)) {
            $db->query('DELETE FROM semesters WHERE id = ?', 'i', $semId);
        }
    }

    if ($action === 'save_courses') {
        $semId   = intval($_POST['semester_id'] ?? 0);
        $own     = $db->query('SELECT id FROM semesters WHERE id = ? AND student_id = ?', 'ii', $semId, $studentId);
        if (!empty($own)) {
            // Delete existing courses for this semester then re-insert
            $db->query('DELETE FROM courses WHERE semester_id = ?', 'i', $semId);
            $codes   = $_POST['course_code']   ?? [];
            $names   = $_POST['course_name']   ?? [];
            $credits = $_POST['credit_hours']  ?? [];
            $grades  = $_POST['grade_letter']  ?? [];
            foreach ($names as $i => $cname) {
                if (trim($cname) === '') continue;
                $letter = $grades[$i] ?? '';
                $points = GPA::letterToPoints($letter);
                $db->query(
                    'INSERT INTO courses (semester_id, course_code, course_name, credit_hours, grade_letter, grade_points) VALUES (?,?,?,?,?,?)',
                    'issids', $semId, trim($codes[$i] ?? ''), trim($cname),
                    intval($credits[$i] ?? 3), $letter, $points
                );
            }
        }
    }

    header('Location: semesters.php');
    exit;
}

// Fetch semesters + courses
$semesters = $db->query(
    'SELECT * FROM semesters WHERE student_id = ? ORDER BY year_of_study, semester_number',
    'i', $studentId
);
foreach ($semesters as &$sem) {
    $sem['courses'] = $db->query('SELECT * FROM courses WHERE semester_id = ? ORDER BY id', 'i', $sem['id']);
    $sem['gpa']     = GPA::semesterGPA($sem['courses']);
}
unset($sem);

$gradeScale = GRADE_SCALE;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Semesters — <?= APP_NAME ?></title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="layout">
<aside class="sidebar">
    <div class="sidebar-logo">🎓 GPA Planner</div>
    <nav class="sidebar-nav">
        <a href="../dashboard.php" class="nav-link">📊 Dashboard</a>
        <a href="semesters.php" class="nav-link active">📚 Semesters</a>
        <a href="projections.php" class="nav-link">🎯 Projections</a>
        <a href="profile.php" class="nav-link">👤 Profile</a>
    </nav>
    <div class="sidebar-footer">
        <span><?= htmlspecialchars($_SESSION['student_name']) ?></span>
        <a href="../logout.php" class="btn-logout">Sign out</a>
    </div>
</aside>

<main class="main-content">
    <div class="page-header">
        <h2>Semester Grades</h2>
    </div>

    <!-- Add Semester Form -->
    <div class="card" style="margin-bottom:1.5rem;">
        <h3 class="card-title">Add New Semester</h3>
        <form method="POST" action="semesters.php" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <input type="hidden" name="action" value="add_semester">
            <div class="form-group" style="margin:0;flex:1;min-width:150px;">
                <label>Semester Name</label>
                <input type="text" name="semester_name" placeholder="e.g. Year 1 Semester 1" required>
            </div>
            <div class="form-group" style="margin:0;width:100px;">
                <label>Year</label>
                <select name="year_of_study">
                    <?php for ($y = 1; $y <= 5; $y++): ?>
                    <option value="<?= $y ?>">Year <?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group" style="margin:0;width:120px;">
                <label>Semester</label>
                <select name="semester_number">
                    <option value="1">Semester 1</option>
                    <option value="2">Semester 2</option>
                    <option value="3">Semester 3</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">+ Add Semester</button>
        </form>
    </div>

    <!-- Semester Blocks -->
    <?php if (empty($semesters)): ?>
        <div class="empty-state">
            <div style="font-size:3rem;margin-bottom:1rem;">📚</div>
            <p>No semesters yet. Add your first semester above.</p>
        </div>
    <?php endif; ?>

    <?php foreach ($semesters as $sem): ?>
    <div class="card semester-block" style="margin-bottom:1.5rem;">
        <div class="sem-header">
            <div>
                <h3 style="margin:0;"><?= htmlspecialchars($sem['semester_name']) ?></h3>
                <span class="sem-gpa-badge">Semester GPA: <strong><?= $sem['gpa'] > 0 ? number_format($sem['gpa'], 2) : '—' ?></strong></span>
            </div>
            <form method="POST" action="semesters.php" onsubmit="return confirm('Delete this semester and all its courses?')">
                <input type="hidden" name="action" value="delete_semester">
                <input type="hidden" name="semester_id" value="<?= $sem['id'] ?>">
                <button type="submit" class="btn btn-danger-outline">Delete Semester</button>
            </form>
        </div>

        <!-- Course Table -->
        <form method="POST" action="semesters.php">
            <input type="hidden" name="action" value="save_courses">
            <input type="hidden" name="semester_id" value="<?= $sem['id'] ?>">

            <table class="course-table" id="table-<?= $sem['id'] ?>">
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Name</th>
                        <th>Credit Hours</th>
                        <th>Grade</th>
                        <th>Points</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tbody-<?= $sem['id'] ?>">
                <?php if (!empty($sem['courses'])): ?>
                    <?php foreach ($sem['courses'] as $course): ?>
                    <tr>
                        <td><input type="text" name="course_code[]" value="<?= htmlspecialchars($course['course_code']) ?>" placeholder="e.g. SMA2101"></td>
                        <td><input type="text" name="course_name[]" value="<?= htmlspecialchars($course['course_name']) ?>" placeholder="Course name" required></td>
                        <td>
                            <select name="credit_hours[]">
                                <?php for ($c = 1; $c <= 6; $c++): ?>
                                <option value="<?= $c ?>" <?= $course['credit_hours'] == $c ? 'selected' : '' ?>><?= $c ?></option>
                                <?php endfor; ?>
                            </select>
                        </td>
                        <td>
                            <select name="grade_letter[]">
                                <option value="">—</option>
                                <?php foreach ($gradeScale as $letter => $pts): ?>
                                <option value="<?= $letter ?>" <?= $course['grade_letter'] === $letter ? 'selected' : '' ?>><?= $letter ?> (<?= $pts ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td class="grade-pts"><?= $course['grade_points'] !== null ? number_format($course['grade_points'],1) : '—' ?></td>
                        <td><button type="button" class="btn-row-del" onclick="removeRow(this)">✕</button></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td><input type="text" name="course_code[]" placeholder="e.g. SMA2101"></td>
                        <td><input type="text" name="course_name[]" placeholder="Course name" required></td>
                        <td><select name="credit_hours[]"><?php for ($c=1;$c<=6;$c++) echo "<option value='$c'" . ($c==3?' selected':'') . ">$c</option>"; ?></select></td>
                        <td><select name="grade_letter[]"><option value="">—</option><?php foreach ($gradeScale as $l => $p) echo "<option value='$l'>$l ($p)</option>"; ?></select></td>
                        <td class="grade-pts">—</td>
                        <td><button type="button" class="btn-row-del" onclick="removeRow(this)">✕</button></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>

            <div style="display:flex;gap:10px;margin-top:10px;flex-wrap:wrap;">
                <button type="button" class="btn btn-secondary" onclick="addRow(<?= $sem['id'] ?>)">+ Add Course</button>
                <button type="submit" class="btn btn-primary">💾 Save Grades</button>
            </div>
        </form>
    </div>
    <?php endforeach; ?>
</main>
</div>

<script src="../js/semesters.js"></script>
</body>
</html>
