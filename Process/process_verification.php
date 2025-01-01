<?php
session_start();
ob_start();

require '../DB Connection/config.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobId = (int)$_POST['job_id'];
    $verificationCode = $_POST['verification_code'];

    // Fetch the job application using job_id and verify the code
    $query = "
        SELECT ja.application_id, ja.verification_code, js.seeker_id 
        FROM job_applications ja
        JOIN job_seekers js ON ja.seeker_id = js.seeker_id
        WHERE ja.job_id = ? AND ja.verification_code = ?;
    ";

    $stmt = $conn->prepare($query);
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

        // Set success message
        $_SESSION['status_title'] = "✔ Success ✔";
        $_SESSION['status'] = "Verification code is correct. Job application status updated.";
        $_SESSION['status_code'] = "success";
        header("Location: http://localhost/Job%20Point/");
    } else {
        // Verification code is incorrect
        $_SESSION['status_title'] = "❌ Error ❌";
        $_SESSION['status'] = "The verification code is incorrect.";
        $_SESSION['status_code'] = "error";
    }

    // Redirect back to the verification page or desired location
    header("Location:http://localhost/Job%20Point/Process/verify job seeker.php");
    exit();
}

ob_end_flush();
?>
