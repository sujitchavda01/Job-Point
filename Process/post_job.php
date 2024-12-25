<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
include '../DB Connection/config.php';

// Check database connection
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    exit();
}

// Function to convert strings to PascalCase
function toPascalCase($string) {
    return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
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
$UserType = $_SESSION['user_type'];

try {
    $conn->begin_transaction();

    // Input sanitization
    $JobTitle = toPascalCase(filter_input(INPUT_POST, 'job_title', FILTER_SANITIZE_STRING));
    $JobType = toPascalCase(filter_input(INPUT_POST, 'job_type', FILTER_SANITIZE_STRING));
    $JobMode = toPascalCase(filter_input(INPUT_POST, 'job_mode', FILTER_SANITIZE_STRING));
    $JobDescription = toPascalCase(filter_input(INPUT_POST, 'job_description', FILTER_SANITIZE_STRING));
    $RequiredQualification = toPascalCase(filter_input(INPUT_POST, 'education', FILTER_SANITIZE_STRING));
    $SkillsRequired = toPascalCase(filter_input(INPUT_POST, 'serviceType', FILTER_SANITIZE_STRING));
    $ApplicationDeadlineDate = filter_input(INPUT_POST, 'application_deadline_date', FILTER_SANITIZE_STRING);
    $ApplicationDeadlineTime = filter_input(INPUT_POST, 'application_deadline_time', FILTER_SANITIZE_STRING);
    $Vacancy = filter_input(INPUT_POST, 'vacancy', FILTER_VALIDATE_INT);
    $PostDate = date('Y-m-d H:i:s'); // Capture the current date and time
    $Salary = filter_input(INPUT_POST, 'salary', FILTER_VALIDATE_FLOAT);

    // Address details
    $Building = toPascalCase(filter_input(INPUT_POST, 'building', FILTER_SANITIZE_STRING));
    $Street = toPascalCase(filter_input(INPUT_POST, 'street', FILTER_SANITIZE_STRING));
    $City = toPascalCase(filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING));
    $State = toPascalCase(filter_input(INPUT_POST, 'state', FILTER_SANITIZE_STRING));
    $Country = toPascalCase(filter_input(INPUT_POST, 'country', FILTER_SANITIZE_STRING));
    $Pincode = filter_input(INPUT_POST, 'pincode', FILTER_SANITIZE_STRING);

    // Insert Address
    $AddressSql = "INSERT INTO address (building, street, city, state, country, pincode) VALUES (?, ?, ?, ?, ?, ?)";
    $AddressStmt = $conn->prepare($AddressSql);
    $AddressStmt->bind_param("ssssss", $Building, $Street, $City, $State, $Country, $Pincode);
    
    if (!$AddressStmt->execute()) {
        throw new Exception("Error inserting address: " . $AddressStmt->error);
    }
    
    $AddressId = $AddressStmt->insert_id; // Get the inserted address ID
    $AddressStmt->close();

    // Combine date and time for application deadline
    $ApplicationDeadline = $ApplicationDeadlineDate . ' ' . $ApplicationDeadlineTime;

    // Insert Job Post
    $JobSql = "INSERT INTO job_posts (job_title, job_type, job_mode, job_description, required_qualification, skills_required, application_deadline, vacancy, post_date, salary, user_id, address_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $JobStmt = $conn->prepare($JobSql);
    $JobStmt->bind_param("sssssssisdii", $JobTitle, $JobType, $JobMode, $JobDescription, $RequiredQualification, $SkillsRequired, $ApplicationDeadline, $Vacancy, $PostDate, $Salary, $UserId, $AddressId);

    if (!$JobStmt->execute()) {
        throw new Exception("Error inserting job post: " . $JobStmt->error);
    }
    
    $jobId = $JobStmt->insert_id; // Get the inserted job ID
    $JobStmt->close();

    // Image Upload
    $imageName = null; // Initialize image name variable
    if (isset($_FILES['featuring_image']) && $_FILES['featuring_image']['error'] === UPLOAD_ERR_OK) {
        $TargetDir = "../images/post/";
        $ImageFileType = strtolower(pathinfo($_FILES['featuring_image']['name'], PATHINFO_EXTENSION));
        $UniqueName = uniqid('job_', true) . '.' . $ImageFileType;
        $TargetFile = $TargetDir . $UniqueName;
        $fileSize = $_FILES['featuring_image']['size'];

        // Validate file size and format
        if ($fileSize > (5 * 1024 * 1024)) { // 5MB in bytes
            throw new Exception("File is too large. Maximum size is 5MB.");
        }

        if (!in_array($ImageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            throw new Exception("Only JPG, JPEG, PNG & GIF files are allowed.");
        }

        if (!getimagesize($_FILES['featuring_image']['tmp_name'])) {
            throw new Exception("File is not a valid image.");
        }

        // Move uploaded file
        if (!move_uploaded_file($_FILES['featuring_image']['tmp_name'], $TargetFile)) {
            throw new Exception("Error uploading file.");
        }

        // Store only the unique name of the image
        $imageName = $UniqueName; // Save only the image name
    }

    // Update job post with the image name (not the full path)
    if ($imageName !== null) {
        $updateJobImageSql = "UPDATE job_posts SET featuring_image = ? WHERE job_id = ?";
        $updateJobImageStmt = $conn->prepare($updateJobImageSql);
        $updateJobImageStmt->bind_param("si", $imageName, $jobId);

        if (!$updateJobImageStmt->execute()) {
            throw new Exception("Error updating job post with image name: " . $updateJobImageStmt->error);
        }
        $updateJobImageStmt->close();
    }

    // Commit transaction
    $conn->commit();

    // Set success message only if everything was successful
    $_SESSION['status_title'] = "Success";
    $_SESSION['status'] = "Job posted successfully.";
    $_SESSION['status_code'] = "success";
    header("Location: ../");
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    error_log("Error: " . $e->getMessage());
    
    // Do not set success message; handle error
    $_SESSION['status_title'] = "Error";
    $_SESSION['status'] = $e->getMessage();
    $_SESSION['status_code'] = "error";
    header("Location: ../");
    exit();
} finally {
    // Close the connection once at the end
    $conn->close();
}
?>
