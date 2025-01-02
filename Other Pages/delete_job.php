<?php
ob_start(); // Ensure output buffering is turned on
session_start(); // Start the session
require '../DB Connection/config.php'; // Database connection
require "../vendor/autoload.php"; // Load PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    $_SESSION['status_title'] = "😔 Sorry 😔";
    $_SESSION['status'] = "You must log in to access this page.";
    $_SESSION['status_code'] = "error";
    header("Location: http://localhost/Job%20Point/");
    exit;
}

// Check if 'job_id' is provided in the query string
if (isset($_GET['job_id'])) {
    $job_id = intval($_GET['job_id']); // Ensure it's an integer to prevent SQL injection

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check for applications related to the job that are NOT "Done" or "Approved"
    $applicants_query = $conn->prepare("SELECT seeker_id FROM job_applications WHERE job_id = ? AND (status IS NULL OR status NOT IN ('Done', 'Approved'))");
    $applicants_query->bind_param("i", $job_id);
    $applicants_query->execute();
    $result = $applicants_query->get_result();
    
    // Collect seeker IDs of applicants
    $applicants = [];
    while ($row = $result->fetch_assoc()) {
        $applicants[] = $row['seeker_id'];
    }

    // Delete applications related to the job
    $delete_apps_stmt = $conn->prepare("DELETE FROM job_applications WHERE job_id = ?");
    $delete_apps_stmt->bind_param("i", $job_id);
    $delete_apps_stmt->execute();
    $delete_apps_stmt->close(); // Close the statement

    // Now delete the job post and its related address
    $delete_job_stmt = $conn->prepare("DELETE j, a FROM jobs j LEFT JOIN address a ON j.address_id = a.address_id WHERE j.job_id = ?");
    $delete_job_stmt->bind_param("i", $job_id);

    if ($delete_job_stmt->execute()) {
        $_SESSION['status_title'] = "Success!";
        $_SESSION['status'] = "Job post and related applications deleted successfully.";
        $_SESSION['status_code'] = "success";

        // Send emails to applicants whose status is NOT "Done" or "Approved"
        foreach ($applicants as $seeker_id) {
            // Retrieve email of the seeker
            $email = getEmailBySeekerId($seeker_id); // Implement this function according to your database structure
            if ($email) {
                sendEmail($email, "Job Post Deleted", "Dear Applicant,<br><br>The job you applied for has been deleted.<br><br>Best regards,<br>Job Point Team");
            }
        }
    } else {
        $_SESSION['status_title'] = "Error!";
        $_SESSION['status'] = "Failed to delete the job post and applications.";
        $_SESSION['status_code'] = "error";
    }

    $delete_job_stmt->close(); // Close the statement
    $conn->close(); // Close the database connection

    // Redirect to the job history page or any other page
    header("Location: post_job_history.php");
    exit;
} else {
    // Handle the case where 'job_id' is not provided
    $_SESSION['status_title'] = "Error!";
    $_SESSION['status'] = "No job ID provided.";
    $_SESSION['status_code'] = "error";
    header("Location: post_job_history.php");
    exit;
}

// Function to get email by seeker ID
function getEmailBySeekerId($seeker_id) {
    global $conn; // Use the global database connection
    $query = $conn->prepare("SELECT email FROM job_seekers WHERE seeker_id = ?"); // Assuming you have a seekers table
    $query->bind_param("i", $seeker_id);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();
    return $row['email'] ?? null; // Return email or null if not found
}

// Function to send email using PHPMailer
function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->isHTML(true);
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->Username = "sujitchavda01@gmail.com"; // Your email
        $mail->Password = "gwkszfmuwzstrwxu"; // Your email password
        $mail->setFrom("sujitchavda01@gmail.com", "Job Point"); // Your company name
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $body;

        // Send the email
        $mail->send();
    } catch (Exception $e) {
        // Handle error (optional)
        error_log("Mailer Error: {$mail->ErrorInfo}");
    }
}
?>
