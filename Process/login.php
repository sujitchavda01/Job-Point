<?php
session_start(); // Start the session

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Include the database configuration file
    require '../DB Connection/config.php'; // Ensure this path is correct

    // Check if the form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['login_user']) {
        // Sanitize and validate input
        $loginIdentifier = trim($_POST['loginIdentifier']);
        $loginPassword = $_POST['loginPassword'];

        // Prepare the SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT user_id, user_type, password FROM users WHERE email = ? OR contact_no = ?");
        $stmt->bind_param("ss", $loginIdentifier, $loginIdentifier); // Bind parameters for email and contact number
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            // Fetch user data
            $stmt->bind_result($user_id, $user_type, $hashed_password);
            $stmt->fetch();

            // Verify the password
            if (password_verify($loginPassword, $hashed_password)) {
                // Successful login
                session_regenerate_id(true); // Regenerate session ID
                $_SESSION['user_id'] = $user_id; // Store user ID in session
                $_SESSION['user_type'] = $user_type; // Store user type in session

                // Redirect based on user type
                if ($user_type === 'Employer') {
                    header("Location: employer_dashboard.php"); // Redirect to employer dashboard
                } else if ($user_type === 'Employee') {
                    header("Location: employee_dashboard.php"); // Redirect to employee dashboard
                } else {
                    header("Location: default_dashboard.php"); // Redirect to a default dashboard
                }
                exit(); // Make sure to exit after redirect
            } else {
                // Invalid password
                $_SESSION['status_title'] = "Error!";
                $_SESSION['status'] = "Invalid password.";
                $_SESSION['status_code'] = "error";
                header("Location: ../"); // Redirect back to login page
                exit();
            }
        } else {
            // No user found
            $_SESSION['status_title'] = "Error!";
            $_SESSION['status'] = "No user found with that email or mobile number.";
            $_SESSION['status_code'] = "error";
            header("Location: ../"); // Redirect back to login page
            exit();
        }

        // Close statement
        $stmt->close();
    } else {
        // Redirect back to login page if not a POST request
        header("Location: ../");
        exit();
    }
} catch (mysqli_sql_exception $e) {
    // Handle database-related errors
    error_log("Database error: " . $e->getMessage());
    $_SESSION['status_title'] = "Error!";
    $_SESSION['status'] = "A database error occurred: " . $e->getMessage(); // Include the error message
    $_SESSION['status_code'] = "error";
    header("Location: ../"); // Redirect back to login page
    exit();
} catch (Exception $e) {
    // Handle general errors
    error_log("General error: " . $e->getMessage());
    $_SESSION['status_title'] = "Error!";
    $_SESSION['status'] = "An error occurred: " . $e->getMessage(); // Include the error message
    $_SESSION['status_code'] = "error";
    header("Location: ../"); // Redirect back to login page
    exit();
} finally {
    // Close the database connection
    $conn->close();
}
?>
