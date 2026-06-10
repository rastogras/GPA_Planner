<?php
// register.php
require_once 'includes/auth.php';

if (Auth::isLoggedIn()) { header('Location: dashboard.php'); exit; }

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid      = trim($_POST['student_id']       ?? '');
    $name     = trim($_POST['full_name']        ?? '');
    $email    = trim($_POST['email']            ?? '');
    $password = trim($_POST['password']         ?? '');
    $confirm  = trim($_POST['confirm_password'] ?? '');
    $course   = trim($_POST['course']           ?? '');
    $dept     = trim($_POST['department']       ?? '');
    $sems     = intval($_POST['total_semesters'] ?? 8);

    if (!$sid || !$name || !$email || !$password) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $result = Auth::register($sid, $name, $email, $password, $course, $dept, $sems);
        if ($result['success']) {
            $success = 'Account created! <a href="login.php">Sign in now</a>.';
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register — JKUAT GPA Planner</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">

<div class="auth-card" style="max-width:480px;">
    <div class="auth-logo">
        <span class="logo-icon">🎓</span>
        <h1><?= APP_NAME ?></h1>
        <p>Create your account</p>
    </div>

    <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

    <form method="POST" action="register.php" novalidate>
        <div class="form-row">
            <div class="form-group">
                <label>Student ID *</label>
                <input type="text" name="student_id" placeholder="SCT221-0001/2022"
                       value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" placeholder="John Doe"
                       value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label>Email address *</label>
            <input type="email" name="email" placeholder="you@students.jkuat.ac.ke"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" placeholder="Min 6 characters" required>
            </div>
            <div class="form-group">
                <label>Confirm Password *</label>
                <input type="password" name="confirm_password" placeholder="Repeat password" required>
            </div>
        </div>
        <div class="form-group">
            <label>Course / Programme</label>
            <input type="text" name="course" placeholder="e.g. BSc Computer Technology"
                   value="<?= htmlspecialchars($_POST['course'] ?? '') ?>">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Department</label>
                <input type="text" name="department" placeholder="e.g. Computing"
                       value="<?= htmlspecialchars($_POST['department'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Total Semesters</label>
                <select name="total_semesters">
                    <?php for ($i = 6; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>" <?= (($_POST['total_semesters'] ?? 8) == $i) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-full">Create Account</button>
    </form>

    <p class="auth-switch">Already registered? <a href="login.php">Sign in</a></p>
</div>

</body>
</html>
