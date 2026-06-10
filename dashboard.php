<?php
// dashboard.php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/gpa.php';

Auth::requireLogin();

$db        = Database::getInstance();
$studentId = $_SESSION['student_id'];

// Fetch student info
$student = $db->query('SELECT * FROM students WHERE id = ?', 'i', $studentId)[0] ?? [];

// Fetch all semesters + courses
$semesters = $db->query(
    'SELECT * FROM semesters WHERE student_id = ? ORDER BY year_of_study, semester_number',
    'i', $studentId
);
foreach ($semesters as &$sem) {
    $sem['courses'] = $db->query(
        'SELECT * FROM courses WHERE semester_id = ? ORDER BY id',
        'i', $sem['id']
    );
}
unset($sem);

// Calculate CGPA
$cgpa          = GPA::cgpa($semesters);
$classification = GPA::getClassification($cgpa);
$totalCredits  = 0;
foreach ($semesters as $s) {
    foreach ($s['courses'] as $c) {
        $totalCredits += $c['credit_hours'] ?? 0;
    }
}

$completedSems    = count($semesters);
$totalSems        = $student['total_semesters'] ?? 8;
$remainingSems    = max(0, $totalSems - $completedSems);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — <?= APP_NAME ?></title>
<link rel="stylesheet" href="css/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>

<!-- Sidebar -->
<div class="layout">
<aside class="sidebar">
    <div class="sidebar-logo">🎓 GPA Planner</div>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-link active">📊 Dashboard</a>
        <a href="pages/semesters.php" class="nav-link">📚 Semesters</a>
        <a href="pages/projections.php" class="nav-link">🎯 Projections</a>
        <a href="pages/profile.php" class="nav-link">👤 Profile</a>
    </nav>
    <div class="sidebar-footer">
        <span><?= htmlspecialchars($_SESSION['student_name']) ?></span>
        <a href="logout.php" class="btn-logout">Sign out</a>
    </div>
</aside>

<!-- Main -->
<main class="main-content">
    <div class="page-header">
        <h2>Welcome back, <?= htmlspecialchars(explode(' ', $student['full_name'])[0]) ?></h2>
        <span class="student-id-badge"><?= htmlspecialchars($student['student_id']) ?></span>
    </div>

    <!-- Metric Cards -->
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-label">Current CGPA</div>
            <div class="metric-value" style="color:<?= $classification['color'] ?>">
                <?= $totalCredits > 0 ? number_format($cgpa, 2) : '—' ?>
            </div>
            <div class="metric-sub">/ 4.00 maximum</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Classification</div>
            <div class="metric-value" style="font-size:1.1rem;color:<?= $classification['color'] ?>">
                <?= $totalCredits > 0 ? $classification['label'] : '—' ?>
            </div>
            <div class="metric-sub">Current standing</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Credit Hours</div>
            <div class="metric-value"><?= $totalCredits ?></div>
            <div class="metric-sub">Completed</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Semesters</div>
            <div class="metric-value"><?= $completedSems ?> / <?= $totalSems ?></div>
            <div class="metric-sub"><?= $remainingSems ?> remaining</div>
        </div>
    </div>

    <!-- Honors Progress Bars -->
    <div class="card">
        <h3 class="card-title">Honors Classification Progress</h3>
        <?php foreach (HONORS as $h): ?>
        <?php $achieved = $totalCredits > 0 && $cgpa >= $h['min']; ?>
        <div class="honors-row">
            <span class="honors-name"><?= $h['label'] ?></span>
            <div class="honors-bar-bg">
                <div class="honors-bar-fill" style="width:<?= $achieved ? min(100, ($cgpa/4.0)*100) : 0 ?>%;background:<?= $h['color'] ?>;"></div>
            </div>
            <span class="honors-threshold"><?= $h['min'] ?>+</span>
            <span class="honors-badge" style="background:<?= $achieved ? $h['color'].'22' : '#f1f1f1' ?>;color:<?= $achieved ? $h['color'] : '#888' ?>">
                <?= $achieved ? '✓ Achieved' : 'Pending' ?>
            </span>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Semester GPA Chart -->
    <?php if (count($semesters) > 0): ?>
    <div class="card">
        <h3 class="card-title">Semester GPA Trend</h3>
        <div style="position:relative;height:220px;">
            <canvas id="gpaChart" role="img" aria-label="Semester GPA trend">Semester GPA trend chart</canvas>
        </div>
    </div>
    <script>
    const semLabels = <?= json_encode(array_column($semesters, 'semester_name')) ?>;
    const semGPAs   = <?php
        $gpas = [];
        foreach ($semesters as $s) {
            $gpas[] = round(GPA::semesterGPA($s['courses']), 2);
        }
        echo json_encode($gpas);
    ?>;
    new Chart(document.getElementById('gpaChart'), {
        type: 'line',
        data: {
            labels: semLabels,
            datasets: [{
                label: 'GPA',
                data: semGPAs,
                borderColor: '#185FA5',
                backgroundColor: 'rgba(24,95,165,0.08)',
                borderWidth: 2,
                pointBackgroundColor: '#185FA5',
                pointRadius: 5,
                tension: 0.3,
                fill: true,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { min: 0, max: 4.0, ticks: { callback: v => v.toFixed(1) } },
                x: { grid: { display: false } }
            }
        }
    });
    </script>
    <?php endif; ?>

    <!-- Quick Action -->
    <div style="margin-top:1.5rem;display:flex;gap:12px;flex-wrap:wrap;">
        <a href="pages/semesters.php" class="btn btn-primary">+ Add Semester / Grades</a>
        <a href="pages/projections.php" class="btn btn-secondary">View Projections</a>
    </div>
</main>
</div>

</body>
</html>
