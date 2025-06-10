<?php
session_start();
if(!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true){
    header("location: ../index.php");
    exit;
}

require_once "../config/database.php";

if(isset($_GET["id"]) && !empty($_GET["id"])){
    try {
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$_GET["id"]]);
        
        header("location: index.php");
        exit;
    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    header("location: index.php");
    exit;
}