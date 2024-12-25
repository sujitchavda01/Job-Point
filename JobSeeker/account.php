<?php 
ob_start(); // Start output buffering
include '../base other/header.php';
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Check if the user is logged in by verifying session variables
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
        $_SESSION['status_title'] = "🥸 Sorry 🥸";
        $_SESSION['status'] = "Unauthorized Access Attempt Detected";
        $_SESSION['status_code'] = "error";
        header("Location: ../");
        exit();
    }else{
        ?>
        


        
        <?php
    }
} catch (Exception $e) {
    // Handle general errors
    error_log("Error: " . $e->getMessage());
    $_SESSION['status_title'] = "Error!";
    $_SESSION['status'] = "An unexpected error occurred.";
    $_SESSION['status_code'] = "error";
    header("Location: ../");
    exit();
}

include '../base other/footer.php'; 
ob_end_flush(); // Flush the output buffer
?>
