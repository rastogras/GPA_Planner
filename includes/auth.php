<?php
// includes/auth.php
require_once __DIR__ . '/db.php';

class Auth {

    public static function register(string $studentId, string $name, string $email, string $password, string $course, string $dept, int $totalSems): array {
        $db   = Database::getInstance();
        $hash = password_hash($password, PASSWORD_BCRYPT);

        // Check if email or student ID already exists
        $exists = $db->query('SELECT id FROM students WHERE email = ? OR student_id = ?', 'ss', $email, $studentId);
        if (!empty($exists)) {
            return ['success' => false, 'message' => 'Student ID or email already registered.'];
        }

        $result = $db->query(
            'INSERT INTO students (student_id, full_name, email, password_hash, course, department, total_semesters) VALUES (?,?,?,?,?,?,?)',
            'ssssssi', $studentId, $name, $email, $hash, $course, $dept, $totalSems
        );

        if (isset($result['insert_id']) && $result['insert_id'] > 0) {
            return ['success' => true, 'id' => $result['insert_id']];
        }
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }

    public static function login(string $email, string $password): array {
        $db   = Database::getInstance();
        $rows = $db->query('SELECT * FROM students WHERE email = ?', 's', $email);

        if (empty($rows)) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        $student = $rows[0];
        if (!password_verify($password, $student['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        $_SESSION['student_id']   = $student['id'];
        $_SESSION['student_name'] = $student['full_name'];
        $_SESSION['student_sid']  = $student['student_id'];
        $_SESSION['logged_in']    = true;
        $_SESSION['last_active']  = time();

        return ['success' => true, 'student' => $student];
    }

    public static function logout(): void {
        session_unset();
        session_destroy();
    }

    public static function isLoggedIn(): bool {
        if (empty($_SESSION['logged_in'])) return false;
        if ((time() - ($_SESSION['last_active'] ?? 0)) > SESSION_TIMEOUT) {
            self::logout();
            return false;
        }
        $_SESSION['last_active'] = time();
        return true;
    }

    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }
}
