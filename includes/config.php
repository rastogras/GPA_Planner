<?php
// includes/config.php
// ─── Database Configuration ───────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Change to your MySQL username
define('DB_PASS', '');           // Change to your MySQL password
define('DB_NAME', 'gpa_planner');

// ─── App Settings ─────────────────────────────────────────────────────────────
define('APP_NAME', 'JKUAT GPA Planner');
define('SESSION_TIMEOUT', 3600); // 1 hour

// ─── JKUAT Grade Scale ────────────────────────────────────────────────────────
define('GRADE_SCALE', [
    'A'  => 4.0,
    'A-' => 3.7,
    'B+' => 3.3,
    'B'  => 3.0,
    'B-' => 2.7,
    'C+' => 2.3,
    'C'  => 2.0,
    'C-' => 1.7,
    'D+' => 1.3,
    'D'  => 1.0,
    'E'  => 0.0,
]);

// ─── Honors Thresholds ────────────────────────────────────────────────────────
define('HONORS', [
    ['label' => 'First Class',         'min' => 3.8, 'color' => '#1D9E75'],
    ['label' => 'Second Class Upper',  'min' => 3.5, 'color' => '#378ADD'],
    ['label' => 'Second Class Lower',  'min' => 3.0, 'color' => '#BA7517'],
    ['label' => 'Pass',                'min' => 2.0, 'color' => '#888780'],
    ['label' => 'Fail',                'min' => 0.0, 'color' => '#E24B4A'],
]);

session_start();
