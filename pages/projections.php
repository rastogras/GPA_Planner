<?php
// pages/projections.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/gpa.php';

Auth::requireLogin();
$db        = Database::getInstance();
$studentId = $_SESSION['student_id'];

$student   = $db->query('SELECT * FROM students WHERE id = ?', 'i', $studentId)[0] ?? [];
$semesters = $db->query(
    'SELECT * FROM semesters WHERE student_id = ? ORDER BY year_of_study, semester_number',
    'i', $studentId
);
foreach ($semesters as &$sem) {
    $sem['courses'] = $db->query('SELECT * FROM courses WHERE semester_id = ?', 'i', $sem['id']);
}
unset($sem);

$cgpa           = GPA::cgpa($semesters);
$classification = GPA::getClassification($cgpa);
$totalCredits   = 0;
foreach ($semesters as $s) {
    foreach ($s['courses'] as $c) { $totalCredits += $c['credit_hours']; }
}
$completedSems = count($semesters);
$totalSems     = $student['total_semesters'] ?? 8;
$defaultRemSems = max(0, $totalSems - $completedSems);

// Projection for all honor levels
$honorsData = [];
foreach (HONORS as $h) {
    if ($h['min'] < 2.0) continue; // skip Fail
    $proj = GPA::projectRequired($cgpa, $totalCredits, $h['min'], $defaultRemSems);
    $proj['tips']  = GPA::getRecommendations($cgpa, $h['min'], $proj['feasibility']);
    $proj['label'] = $h['label'];
    $proj['color'] = $h['color'];
    $honorsData[]  = $proj;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Projections — <?= APP_NAME ?></title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="layout">
<aside class="sidebar">
    <div class="sidebar-logo">🎓 GPA Planner</div>
    <nav class="sidebar-nav">
        <a href="../dashboard.php" class="nav-link">📊 Dashboard</a>
        <a href="semesters.php" class="nav-link">📚 Semesters</a>
        <a href="projections.php" class="nav-link active">🎯 Projections</a>
        <a href="profile.php" class="nav-link">👤 Profile</a>
    </nav>
    <div class="sidebar-footer">
        <span><?= htmlspecialchars($_SESSION['student_name']) ?></span>
        <a href="../logout.php" class="btn-logout">Sign out</a>
    </div>
</aside>

<main class="main-content">
    <div class="page-header">
        <h2>Graduation Projections</h2>
    </div>

    <!-- Current Standing -->
    <div class="metrics-grid" style="margin-bottom:1.5rem;">
        <div class="metric-card">
            <div class="metric-label">Current CGPA</div>
            <div class="metric-value" style="color:<?= $classification['color'] ?>"><?= $totalCredits > 0 ? number_format($cgpa,2) : '—' ?></div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Classification</div>
            <div class="metric-value" style="font-size:1rem;color:<?= $classification['color'] ?>"><?= $totalCredits > 0 ? $classification['label'] : '—' ?></div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Credits Done</div>
            <div class="metric-value"><?= $totalCredits ?></div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Remaining Sems</div>
            <div class="metric-value"><?= $defaultRemSems ?></div>
        </div>
    </div>

    <!-- Remaining semesters adjuster -->
    <div class="card" style="margin-bottom:1.5rem;">
        <label style="font-size:14px;color:#555;">Adjust remaining semesters: <strong id="rem-val"><?= $defaultRemSems ?></strong></label>
        <input type="range" id="rem-slider" min="1" max="<?= $totalSems ?>" value="<?= max(1,$defaultRemSems) ?>"
               style="width:100%;margin-top:8px;" oninput="document.getElementById('rem-val').textContent=this.value">
        <p style="font-size:12px;color:#888;margin-top:6px;">Reload the page after changing to recalculate. Use the form below to set a custom value.</p>
        <form method="GET" action="projections.php" style="display:flex;gap:10px;margin-top:10px;align-items:center;">
            <input type="number" name="rem" min="1" max="<?= $totalSems ?>" value="<?= $defaultRemSems ?>" style="width:80px;">
            <button type="submit" class="btn btn-secondary">Recalculate</button>
        </form>
    </div>

    <?php if ($totalCredits === 0): ?>
        <div class="empty-state">
            <div style="font-size:3rem;">🎯</div>
            <p>No grades entered yet. <a href="semesters.php">Add your semester grades</a> to see projections.</p>
        </div>
    <?php else: ?>

    <?php foreach ($honorsData as $h): ?>
    <?php
        $feasLabel = [
            'achievable'   => ['Achievable',   '#1D9E75'],
            'challenging'  => ['Challenging',   '#BA7517'],
            'very_hard'    => ['Very Hard',     '#E24B4A'],
            'not_possible' => ['Not Possible',  '#E24B4A'],
            'already_met'  => ['Already Met ✓', '#1D9E75'],
        ][$h['feasibility']] ?? ['Unknown', '#888'];
    ?>
    <div class="card projection-card" style="border-left:4px solid <?= $h['color'] ?>;margin-bottom:1.5rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
            <h3 style="margin:0;color:<?= $h['color'] ?>"><?= $h['label'] ?> <small style="color:#999;font-size:0.75em;">(≥ <?= $h['target_cgpa'] ?>)</small></h3>
            <span style="background:<?= $feasLabel[1] ?>22;color:<?= $feasLabel[1] ?>;padding:4px 12px;border-radius:99px;font-size:12px;font-weight:600;"><?= $feasLabel[0] ?></span>
        </div>

        <div class="proj-detail">
            <?php if ($h['feasibility'] === 'already_met'): ?>
                <p>🎉 You have already reached this classification threshold.</p>
            <?php elseif ($h['feasibility'] === 'not_possible'): ?>
                <p>This classification cannot be reached in <?= $defaultRemSems ?> remaining semester(s) given your current credits.</p>
            <?php else: ?>
                <p>GPA required per remaining semester:
                    <span style="font-size:1.4rem;font-weight:700;color:<?= $h['color'] ?>"><?= number_format($h['gpa_needed_per_sem'],2) ?></span>
                    <small style="color:#888;">(over <?= $h['future_credits'] ?> future credit hours)</small>
                </p>
            <?php endif; ?>
        </div>

        <?php foreach ($h['tips'] as $tip): ?>
        <div class="tip-block">
            <div class="tip-label">💡 Recommendation</div>
            <div class="tip-text"><?= htmlspecialchars($tip) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <?php endif; ?>
</main>
</div>
</body>
</html>
