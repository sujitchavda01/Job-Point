<?php
session_start(); // Start the session

try {
    // Check if the session exists
    if (isset($_SESSION['user_id'])) {
        // Clear all session variables
        $_SESSION = [];

        // Destroy the session
        session_destroy();

        // Invalidate the session cookie (if any)
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000, 
                $params["path"], 
                $params["domain"], 
                $params["secure"], 
                $params["httponly"]
            );
        }

        // Redirect to the login page with a success message
        header("Location: ../");
        exit();
    } else {
        // If no session exists, redirect to the login page
        header("Location: ../");
        exit();
    }
} catch (Exception $e) {
    // Log the error (optional)
    error_log("Logout error: " . $e->getMessage());

    // Redirect to the login page with an error message
    $_SESSION['status_title'] = "Error!";
    $_SESSION['status'] = "An error occurred during logout.";
    $_SESSION['status_code'] = "error";
    header("Location: ../");
    exit();
}
?>
