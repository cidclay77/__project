<?php
session_start();
if(!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true){
    header("location: ../index.php");
    exit;
}

require_once "../config/database.php";

// Check if user is admin
try {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION["id"]]);
    $current_user = $stmt->fetch();
    
    if(!$current_user || $current_user['role'] !== 'admin') {
        header("location: ../dashboard.php");
        exit;
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

if(isset($_GET["id"]) && !empty($_GET["id"])){
    // Prevent user from deleting themselves
    if($_GET["id"] == $_SESSION["id"]) {
        $_SESSION['error_message'] = "Você não pode excluir sua própria conta.";
        header("location: index.php");
        exit;
    }

    try {
        // Check if user has courses assigned
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE instructor_id = ?");
        $stmt->execute([$_GET["id"]]);
        $course_count = $stmt->fetchColumn();

        if($course_count > 0) {
            $_SESSION['error_message'] = "Não é possível excluir este usuário pois ele possui cursos atribuídos.";
            header("location: index.php");
            exit;
        }

        // Delete user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$_GET["id"]]);
        
        $_SESSION['success_message'] = "Usuário excluído com sucesso!";
        header("location: index.php");
        exit;
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Erro ao excluir usuário: " . $e->getMessage();
        header("location: index.php");
        exit;
    }
} else {
    header("location: index.php");
    exit;
}
?>