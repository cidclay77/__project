<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'course_management');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    // First connect without database selected
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    
    // Reconnect with database selected
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Check if tables exist
    $result = $pdo->query("SHOW TABLES LIKE 'users'")->rowCount();
    if ($result == 0) {
        // Only create tables if they don't exist
        $sql = file_get_contents(__DIR__ . '/../supabase/migrations/20250524201302_lucky_plain.sql');
        // Remove CREATE DATABASE and USE statements
        $sql = preg_replace('/CREATE DATABASE.*?;/is', '', $sql);
        $sql = preg_replace('/USE.*?;/is', '', $sql);
        $pdo->exec($sql);
    }
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}