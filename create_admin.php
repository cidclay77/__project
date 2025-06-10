<?php
require_once "config/database.php";

try {
    $username = "admin";
    $password = password_hash("admin123", PASSWORD_DEFAULT);
    $name = "Administrator";
    $email = "admin@example.com";
    $role = "admin";

    $stmt = $pdo->prepare("INSERT INTO users (username, password, name, email, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$username, $password, $name, $email, $role]);
    
    echo "Admin user created successfully!\n";
    echo "Username: admin\n";
    echo "Password: admin123\n";
} catch(PDOException $e) {
    if($e->getCode() == 23000) {
        echo "Admin user already exists!\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}