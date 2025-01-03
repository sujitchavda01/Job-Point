<?php
session_start();
ob_start();
require '../DB Connection/config.php'; // Ensure this is the correct path to your database connection script

function setSessionStatus($title, $message, $code)
{
    $_SESSION['status_title'] = $title;
    $_SESSION['status'] = $message;
    $_SESSION['status_code'] = $code;
}

function redirectTo($url)
{
    header("Location: $url");
    exit;
}

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    setSessionStatus("🫨 Sorry 🫨", "You must log in to access this page.", "error");
    redirectTo("http://localhost/Job%20Point/");
}

// Check if the job ID is provided
if (!isset($_GET['job_id']) || empty($_GET['job_id'])) {
    setSessionStatus("❌ Error ❌", "Job ID is missing.", "error");
    redirectTo("../JobSeeker/job_apply_history.php");
}

$jobId = (int)$_GET['job_id'];

// Fetch the job application record to validate its status
$query = "SELECT status FROM job_applications WHERE job_id = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    setSessionStatus("❌ Error ❌", "Failed to prepare the database query.", "error");
    redirectTo("../JobSeeker/job_apply_history.php");
}

$stmt->bind_param("i", $jobId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $application = $result->fetch_assoc();
    $status = $application['status'];

    // Only allow deletion if the status is not Done or Approved
    if ($status !== 'Done' && $status !== 'Approved') {
        $deleteQuery = "DELETE FROM job_applications WHERE job_id = ?";
        $deleteStmt = $conn->prepare($deleteQuery);

        if ($deleteStmt === false) {
            setSessionStatus("❌ Error ❌", "Failed to prepare the delete query.", "error");
            redirectTo("../JobSeeker/job_apply_history.php");
        }

        $deleteStmt->bind_param("i", $jobId);
        $deleteStmt->execute();

        if ($deleteStmt->affected_rows > 0) {
            setSessionStatus("✔ Success ✔", "Job application deleted successfully.", "success");
        } else {
            setSessionStatus("❌ Error ❌", "Failed to delete the job application.", "error");
        }
    } else {
        setSessionStatus("⚠️ Warning ⚠️", "You cannot delete this application as it is already Done or Approved.", "warning");
    }
} else {
    setSessionStatus("❌ Error ❌", "No application found for the specified job.", "error");
}

redirectTo("../JobSeeker/job_apply_history.php");
ob_end_flush();
