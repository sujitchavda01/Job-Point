<?php
session_start();
require '../DB Connection/config.php';
require '../vendor/autoload.php'; // Include PHPMailer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

if (!isset($_SESSION['user_id'])) {
    $_SESSION['status_title'] = "❌ Error ❌";
    $_SESSION['status'] = "You must be logged in to perform this action.";
    $_SESSION['status_code'] = "error";
    header("Location: ../"); // Redirect to login
    exit();
}

if (!isset($_GET['job_id'])) {
    $_SESSION['status_title'] = "❌ Error ❌";
    $_SESSION['status'] = "Invalid request.";
    $_SESSION['status_code'] = "error";
    header("Location: ../"); // Redirect to home
    exit();
}

$jobId = intval($_GET['job_id']);
$userId = $_SESSION['user_id'];

try {
    // Check for related data in job_applications
    $checkApplicationsQuery = "SELECT seeker_id FROM job_applications WHERE job_id = ?";
    $stmt = $conn->prepare($checkApplicationsQuery);
    $stmt->bind_param("i", $jobId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $seekerEmails = [];
    while ($row = $result->fetch_assoc()) {
        // Fetch user_id from job_seeker using seeker_id
        $seekerId = $row['seeker_id'];
        $fetchUserIdQuery = "SELECT user_id FROM job_seeker WHERE seeker_id = ?";
        $userIdStmt = $conn->prepare($fetchUserIdQuery);
        $userIdStmt->bind_param("i", $seekerId);
        $userIdStmt->execute();
        $userIdResult = $userIdStmt->get_result();

        if ($userRow = $userIdResult->fetch_assoc()) {
            $fetchedUserId = $userRow['user_id'];

            // Fetch the email from users using user_id
            $fetchEmailQuery = "SELECT email FROM users WHERE user_id = ?";
            $emailStmt = $conn->prepare($fetchEmailQuery);
            $emailStmt->bind_param("i", $fetchedUserId);
            $emailStmt->execute();
            $emailResult = $emailStmt->get_result();
            if ($emailRow = $emailResult->fetch_assoc()) {
                $seekerEmails[] = $emailRow['email'];
            }
            $emailStmt->close();
        }
        $userIdStmt->close();
    }

    // If applications exist, notify all seekers
    if (!empty($seekerEmails)) {
        $jobTitle = ""; // Placeholder for job title
        $fetchJobTitleQuery = "SELECT job_title FROM job_posts WHERE job_id = ?";
        $titleStmt = $conn->prepare($fetchJobTitleQuery);
        $titleStmt->bind_param("i", $jobId);
        $titleStmt->execute();
        $titleResult = $titleStmt->get_result();
        if ($titleRow = $titleResult->fetch_assoc()) {
            $jobTitle = $titleRow['job_title'];
        }

        // Prepare email content
        $subject = "Job Post Deleted: $jobTitle";
        $message = "Dear Job Seeker,<br><br>The job post '<strong>$jobTitle</strong>' has been deleted by the employer.<br><br>Thank you for your interest.<br><br>Best Regards,<br>Job Point Team";

        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->isHTML(true);
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->Username = " "; // Your email
        $mail->Password = ""; // Your email password
        $mail->setFrom(" ", "Job Point"); // Sender information

        // Send email to each seeker
        foreach ($seekerEmails as $email) {
            $mail->addAddress($email); // Add recipient
            $mail->Subject = $subject;
            $mail->Body = $message;

            // Send the email
            if (!$mail->send()) {
                // Optionally log or handle the error for each failed email
                error_log("Email could not be sent to $email. Mailer Error: {$mail->ErrorInfo}");
            }
            // Clear all recipients for the next loop iteration
            $mail->clearAddresses();
        }
    }

    // Fetch the address_id of the job to delete related address
    $fetchAddressQuery = "SELECT address_id FROM job_posts WHERE job_id = ? AND user_id = ?";
    $stmt = $conn->prepare($fetchAddressQuery);
    $stmt->bind_param("ii", $jobId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $jobData = $result->fetch_assoc();

    if (!$jobData) {
        throw new Exception("Job post not found or you do not have permission to delete it.");
    }

    $addressId = $jobData['address_id'];

    // Start transaction
    $conn->begin_transaction();

    // Delete job post
    $deleteJobQuery = "DELETE FROM job_posts WHERE job_id = ?";
    $stmt = $conn->prepare($deleteJobQuery);
    $stmt->bind_param("i", $jobId);
    $stmt->execute();

    // Delete related address
    $deleteAddressQuery = "DELETE FROM address WHERE address_id = ?";
    $stmt = $conn->prepare($deleteAddressQuery);
    $stmt->bind_param("i", $addressId);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    $_SESSION['status_title'] = "✅ Success ✅";
    $_SESSION['status'] = "Job post and related address deleted successfully, and notifications sent to applicants.";
    $_SESSION['status_code'] = "success";
    header("Location: ../"); // Redirect to home or job list page
    exit();
} catch (Exception $e) {
    // Rollback transaction if needed
    if ($conn->in_transaction) {
        $conn->rollback();
    }

    $_SESSION['status_title'] = "❌ Error ❌";
    $_SESSION['status'] = $e->getMessage();
    $_SESSION['status_code'] = "error";
    header("Location: ../"); // Redirect to home or job list page
    exit();
} finally {
    if (isset($stmt) && $stmt !== false) {
        $stmt->close();
    }
    $conn->close();
}
?>
