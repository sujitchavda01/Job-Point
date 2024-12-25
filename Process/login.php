<?php
// Set secure session cookie parameters before starting the session
session_set_cookie_params([
    'lifetime' => 0, // Session expires when the browser is closed
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true, // Requires HTTPS
    'httponly' => true, // Prevents JavaScript access
    'samesite' => 'Strict' // Prevents cross-site requests
]);

session_start(); // Start the session

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require '../DB Connection/config.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_user'])) {
        $loginIdentifier = trim($_POST['loginIdentifier']);
        $loginPassword = $_POST['loginPassword'];

        $stmt = $conn->prepare("SELECT user_id, user_type, password FROM users WHERE email = ? OR contact_no = ?");
        $stmt->bind_param("ss", $loginIdentifier, $loginIdentifier);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($user_id, $user_type, $hashed_password);
            $stmt->fetch();

            if (password_verify($loginPassword, $hashed_password)) {
                session_regenerate_id(true); // Regenerate session ID to prevent fixation attacks
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_type'] = $user_type;

                // Redirect based on user type
                if ($user_type === 'Employer Individual' || $user_type === 'Employer Organization') {
                    $_SESSION['status_title'] = "Welcome, Employer!";
                    $_SESSION['status'] = "You Are Logged In. Find The Right Job Seeker Now.";
                    $_SESSION['status_code'] = "success";
                    $redirect_url = "../Employer/Employer.php";
                } else {
                    $_SESSION['status_title'] = "Welcome, Job Seeker!";
                    $_SESSION['status'] = "You're Logged In. Start Your job Search Now!";
                    $_SESSION['status_code'] = "success";
                    $redirect_url = "../JobSeeker/Job_Seeker.php";
                }
                
                header("Location: $redirect_url");
                exit();
                
                
            } else {
                sleep(1); // Delay to mitigate brute force attacks
                $_SESSION['status_title'] = "Error!";
                $_SESSION['status'] = "Invalid password.";
                $_SESSION['status_code'] = "error";
                header("Location: ../");
                exit();
            }
        } else {
            sleep(1); // Delay to mitigate brute force attacks
            $_SESSION['status_title'] = "Error!";
            $_SESSION['status'] = "No user found with that email or mobile number.";
            $_SESSION['status_code'] = "error";
            header("Location: ../");
            exit();
        }

        $stmt->close();
    } else {
        $_SESSION['status_title'] = "😑 Sorry 😑";
        $_SESSION['status'] = "Unauthorized Access Attempt Detected";
        $_SESSION['status_code'] = "error";
        header("Location: ../");
        exit();
    }
} catch (mysqli_sql_exception $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['status_title'] = "Error!";
    $_SESSION['status'] = "A database error occurred.";
    $_SESSION['status_code'] = "error";
    header("Location: ../");
    exit();
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    $_SESSION['status_title'] = "Error!";
    $_SESSION['status'] = "An unexpected error occurred.";
    $_SESSION['status_code'] = "error";
    header("Location: ../");
    exit();
} finally {
    $conn->close();
}
?>
