<?php
session_start();
require '../DB Connection/config.php'; // Database connection

$jobId = (int)$_GET['job_id'];

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    $_SESSION['status_title'] = "🙂 Sorry 🙂";
    $_SESSION['status'] = "You must log in to apply for a job.";
    $_SESSION['status_code'] = "error";
    header("Location: ../login.php");
    exit();
}

// Validate the user type
if ($_SESSION['user_type'] !== 'Job Seeker') {
    $_SESSION['status_title'] = "👨 Sorry 👨";
    $_SESSION['status'] = "You must be logged in as a Job Seeker to apply for jobs.";
    $_SESSION['status_code'] = "error";
    header("Location: ../Other Pages/job_details.php?job_id=$jobId");
    exit();
}

// Check if the seeker exists
$userId = (int)$_SESSION['user_id'];
$seekerCheckQuery = "SELECT seeker_id FROM job_seekers WHERE user_id = ?";
$stmt = $conn->prepare($seekerCheckQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$seekerResult = $stmt->get_result();

if ($seekerResult->num_rows === 0) {
    $_SESSION['status_title'] = "❌ Error ❌";
    $_SESSION['status'] = "Your profile is incomplete. Please update your profile to apply for jobs.";
    $_SESSION['status_code'] = "error";
    header("Location: ../profile.php");
    exit();
}

$seekerData = $seekerResult->fetch_assoc();
$seekerId = (int)$seekerData['seeker_id'];

try {
    // Check if the user already applied for the job
    $checkApplicationQuery = "SELECT * FROM job_applications WHERE job_id = ? AND seeker_id = ?";
    $stmt = $conn->prepare($checkApplicationQuery);
    $stmt->bind_param("ii", $jobId, $seekerId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['status_title'] = "⚠️ Already Applied ⚠️";
        $_SESSION['status'] = "You have already applied for this job.";
        $_SESSION['status_code'] = "info";
        header("Location: ../Other Pages/job_details.php?job_id=$jobId");
        exit();
    }

    // Insert a new application
    $applyQuery = "INSERT INTO job_applications (job_id, seeker_id) VALUES (?, ?)";
    $stmt = $conn->prepare($applyQuery);
    $stmt->bind_param("ii", $jobId, $seekerId);

    if ($stmt->execute()) {
        $_SESSION['status_title'] = "✅ Success ✅";
        $_SESSION['status'] = "You have successfully applied for the job.";
        $_SESSION['status_code'] = "success";
        header("Location: ../Other Pages/job_details.php?job_id=$jobId");
        exit();
    } else {
        throw new Exception("Failed to apply for the job. Please try again.");
    }
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    $_SESSION['status_title'] = "❌ Error ❌";
    $_SESSION['status'] = "Something went wrong. Please try again later.";
    $_SESSION['status_code'] = "error";
    header("Location: ../Other Pages/job_details.php?job_id=$jobId");
    exit();
}
?>
