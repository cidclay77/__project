<?php
session_start();
if(!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true){
    http_response_code(401);
    exit;
}

require_once "../config/database.php";

try {
    $data = [
        'totalCourses' => 0,
        'totalStudents' => 0,
        'activeEnrollments' => 0,
        'totalCertificates' => 0,
        'recentCourses' => [],
        'recentEnrollments' => []
    ];

    // Get totals
    $stmt = $pdo->query("SELECT COUNT(*) FROM courses");
    $data['totalCourses'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM students");
    $data['totalStudents'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'active'");
    $data['activeEnrollments'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM certificates");
    $data['totalCertificates'] = $stmt->fetchColumn();

    // Get recent courses
    $stmt = $pdo->query("SELECT name, DATE_FORMAT(start_date, '%d/%m/%Y') as start_date, status FROM courses ORDER BY created_at DESC LIMIT 5");
    $data['recentCourses'] = $stmt->fetchAll();

    // Get recent enrollments
    $stmt = $pdo->query("
        SELECT s.name as student_name, c.name as course_name, DATE_FORMAT(e.enrollment_date, '%d/%m/%Y') as enrollment_date
        FROM enrollments e
        JOIN students s ON e.student_id = s.id
        JOIN courses c ON e.course_id = c.id
        ORDER BY e.created_at DESC
        LIMIT 5
    ");
    $data['recentEnrollments'] = $stmt->fetchAll();

    header('Content-Type: application/json');
    echo json_encode($data);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}