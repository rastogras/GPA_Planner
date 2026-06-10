-- JKUAT GPA Planner Database Schema
-- Run this in phpMyAdmin or MySQL CLI: mysql -u root -p < database.sql

CREATE DATABASE IF NOT EXISTS gpa_planner CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gpa_planner;

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    course VARCHAR(100),
    department VARCHAR(100),
    total_semesters INT DEFAULT 8,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS semesters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    semester_name VARCHAR(50) NOT NULL,
    year_of_study INT NOT NULL,
    semester_number INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    semester_id INT NOT NULL,
    course_code VARCHAR(20),
    course_name VARCHAR(100) NOT NULL,
    credit_hours INT NOT NULL DEFAULT 3,
    grade_letter VARCHAR(3),
    grade_points DECIMAL(3,1),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE
);

-- Sample data for testing
INSERT INTO students (student_id, full_name, email, password_hash, course, department, total_semesters)
VALUES ('SCT221-0001/2022', 'John Doe', 'john@students.jkuat.ac.ke', '$2y$10$examplehashhere', 'BSc Computer Technology', 'Computing', 8);
