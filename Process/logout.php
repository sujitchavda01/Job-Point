<?php
session_start();

try {
    if (isset($_SESSION['user_id'])) {
        $_SESSION = []; // Clear all session variables
        session_destroy(); // Destroy the session

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

        header("Location: ../");
        exit();
    } else {
        $_SESSION['status_title'] = "🤪 Sorry 🤪";
        $_SESSION['status'] = "Unauthorized Access Attempt Detected";
        $_SESSION['status_code'] = "error";
        header("Location: ../");
        exit();
    }
} catch (Exception $e) {
    error_log("Logout error: " . $e->getMessage());
    $_SESSION['status_title'] = "Error!";
    $_SESSION['status'] = "An unexpected error occurred.";
    $_SESSION['status_code'] = "error";
    header("Location: ../");
    exit();
}
?>
