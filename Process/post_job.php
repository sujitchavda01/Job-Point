<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
include '../DB Connection/config.php';

if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    exit();
}

// Check user session
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    $_SESSION['status_title'] = "Access Denied";
    $_SESSION['status'] = "You must be logged in to post a job.";
    $_SESSION['status_code'] = "error";
    header("Location: ../");
    exit();
}

$UserId = $_SESSION['user_id'];

try {
    $conn->begin_transaction();

    // Sanitize and validate input
    $JobTitle = htmlspecialchars($_POST['job_title']);
    $JobType = htmlspecialchars($_POST['job_type']);
    $JobMode = htmlspecialchars($_POST['job_mode']);
    $JobDescription = htmlspecialchars($_POST['job_description']);
    $RequiredQualification = htmlspecialchars($_POST['education']);
    $SkillsRequired = htmlspecialchars($_POST['serviceType']);
    $ApplicationDeadlineDate = htmlspecialchars($_POST['application_deadline_date']);
    $ApplicationDeadlineTime = htmlspecialchars($_POST['application_deadline_time']);
    $Vacancy = intval($_POST['vacancy']);
    $PostDate = date('Y-m-d H:i:s');
    $Salary = floatval($_POST['salary']);

    $Building = htmlspecialchars($_POST['building']);
    $Street = htmlspecialchars($_POST['street']);
    $City = htmlspecialchars($_POST['city']);
    $State = htmlspecialchars($_POST['state']);
    $Country = htmlspecialchars($_POST['country']);
    $Pincode = htmlspecialchars($_POST['pincode']);

    // Insert address
    $AddressSql = "INSERT INTO address (building, street, city, state, country, pincode) VALUES (?, ?, ?, ?, ?, ?)";
    $AddressStmt = $conn->prepare($AddressSql);
    $AddressStmt->bind_param("ssssss", $Building, $Street, $City, $State, $Country, $Pincode);

    if (!$AddressStmt->execute()) {
        throw new Exception("Error inserting address: " . $AddressStmt->error);
    }

    $AddressId = $AddressStmt->insert_id;
    $AddressStmt->close();

    $ApplicationDeadline = $ApplicationDeadlineDate . ' ' . $ApplicationDeadlineTime;

    // Insert job post
    $JobSql = "INSERT INTO job_posts (job_title, job_type, job_mode, job_description, required_qualification, skills_required, application_deadline, vacancy, post_date, salary, user_id, address_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $JobStmt = $conn->prepare($JobSql);
    $JobStmt->bind_param("sssssssisdii", $JobTitle, $JobType, $JobMode, $JobDescription, $RequiredQualification, $SkillsRequired, $ApplicationDeadline, $Vacancy, $PostDate, $Salary, $UserId, $AddressId);

    if (!$JobStmt->execute()) {
        throw new Exception("Error inserting job post: " . $JobStmt->error);
    }

    $jobId = $JobStmt->insert_id;
    $JobStmt->close();

    // Image upload handling
    $imageName = null;
    if (isset($_FILES['featuring_image']) && $_FILES['featuring_image']['error'] === UPLOAD_ERR_OK) {
        $TargetDir = "../images/post/";
        $ImageFileType = strtolower(pathinfo($_FILES['featuring_image']['name'], PATHINFO_EXTENSION));
        $UniqueName = uniqid('job_', true) . '.' . $ImageFileType;
        $TargetFile = $TargetDir . $UniqueName;

        if ($_FILES['featuring_image']['size'] > (5 * 1024 * 1024)) {
            throw new Exception("File is too large. Maximum size is 5MB.");
        }

        if (!in_array($ImageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            throw new Exception("Only JPG, JPEG, PNG & GIF files are allowed.");
        }

        if (!getimagesize($_FILES['featuring_image']['tmp_name'])) {
            throw new Exception("File is not a valid image.");
        }

        if (!move_uploaded_file($_FILES['featuring_image']['tmp_name'], $TargetFile)) {
            throw new Exception("Error uploading file.");
        }

        $imageName = $UniqueName;

        // Update job post with image name
        $UpdateImageSql = "UPDATE job_posts SET featuring_image = ? WHERE job_id = ?";
        $UpdateImageStmt = $conn->prepare($UpdateImageSql);
        $UpdateImageStmt->bind_param("si", $imageName, $jobId);

        if (!$UpdateImageStmt->execute()) {
            throw new Exception("Error updating job post with image name: " . $UpdateImageStmt->error);
        }

        $UpdateImageStmt->close();
    }

    $conn->commit();

    $_SESSION['status_title'] = "Success";
    $_SESSION['status'] = "Job posted successfully.";
    $_SESSION['status_code'] = "success";
    header("Location: ../");
    exit();
} catch (Exception $e) {
    $conn->rollback();
    error_log("Error: " . $e->getMessage());

    $_SESSION['status_title'] = "Error";
    $_SESSION['status'] = $e->getMessage();
    $_SESSION['status_code'] = "error";
    header("Location: ../");
    exit();
} finally {
    $conn->close();
}
?>
