<?php
session_start();
ob_start();

require '../DB Connection/config.php';

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
$jobId = (int)$_POST['job_id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and retrieve query parameters
    if (!isset($_POST['job_id']) || !isset($_POST['verification_code']) || empty($_POST['job_id']) || empty($_POST['verification_code'])) {
        $jobId = isset($_POST['job_id']) ? $_POST['job_id'] : null;
        $_SESSION['status_title'] = "❌ Error ❌";
        $_SESSION['status'] = "Missing job ID or verification code.";
        $_SESSION['status_code'] = "error";
        $redirectUrl = "http://localhost/Job%20Point/Process/verify_job_seeker.php";
        if ($jobId) {
            $redirectUrl .= "?job_id=" . urlencode($jobId);
        }
        header("Location: $redirectUrl");
        exit();
    }

    // Sanitize input
    
    $verificationCode = htmlspecialchars($_POST['verification_code'], ENT_QUOTES, 'UTF-8');

    // Fetch the job application using job_id and verify the code
    $query = "
        SELECT ja.application_id, ja.verification_code, js.seeker_id 
        FROM job_applications ja
        JOIN job_seekers js ON ja.seeker_id = js.seeker_id
        WHERE ja.job_id = ? AND ja.verification_code = ?;
    ";

    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("is", $jobId, $verificationCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Verification code is correct
        $application = $result->fetch_assoc();
        $applicationId = $application['application_id'];
        $seekerId = $application['seeker_id'];

        // Update job application status to "Done"
        $updateApplicationQuery = "UPDATE job_applications SET status = 'Done' WHERE application_id = ?;";
        $updateAppStmt = $conn->prepare($updateApplicationQuery);
        $updateAppStmt->bind_param("i", $applicationId);
        $updateAppStmt->execute();

        // Update job status in job_seekers table to "not active"
        $updateSeekerQuery = "UPDATE job_seekers SET job_status = 'not active' WHERE seeker_id = ?;";
        $updateSeekerStmt = $conn->prepare($updateSeekerQuery);
        $updateSeekerStmt->bind_param("i", $seekerId);
        $updateSeekerStmt->execute();

        $_SESSION['status_title'] = "✔ Success ✔";
        $_SESSION['status'] = "Verification code is correct. Job application status updated.";
        $_SESSION['status_code'] = "success";
        header("Location: http://localhost/Job%20Point/");
    } else {
        // Verification code is incorrect
        $_SESSION['status_title'] = "❌ Error ❌";
        $_SESSION['status'] = "The verification code is incorrect.";
        $_SESSION['status_code'] = "error";
        header("Location: http://localhost/Job%20Point/Process/verify_job_seeker.php?job_id=" . urlencode($jobId));
    }

    exit();
} else {
    $_SESSION['status_title'] = "❌ Error ❌";
    $_SESSION['status'] = "Something went wrong.";
    $_SESSION['status_code'] = "error";
    header("Location: http://localhost/Job%20Point/Process/verify_job_seeker.php?job_id=$jobId");
}

ob_end_flush();
