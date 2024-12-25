<?php
session_start(); // Start the session
include '../DB Connection/config.php'; // Include your database connection file
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    exit();
}

// Function to convert strings to PascalCase
function toPascalCase($string) {
    return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
}

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    $_SESSION['status_title'] = "Access Denied";
    $_SESSION['status'] = "You must be logged in to post a job.";
    $_SESSION['status_code'] = "error"; 
    header("Location: ../"); 
    exit();
}

// Get user ID and user type from session
$UserId = $_SESSION['user_id'];
$UserType = $_SESSION['user_type'];

try {
    // Start a transaction
    $conn->begin_transaction();

    // Validate and sanitize inputs
    $JobTitle = toPascalCase(filter_input(INPUT_POST, 'job_title', FILTER_SANITIZE_STRING));
    $JobType = toPascalCase(filter_input(INPUT_POST, 'job_type', FILTER_SANITIZE_STRING));
    $JobMode = toPascalCase(filter_input(INPUT_POST, 'job_mode', FILTER_SANITIZE_STRING));
    $JobDescription = toPascalCase(filter_input(INPUT_POST, 'job_description', FILTER_SANITIZE_STRING));
    $RequiredQualification = toPascalCase(filter_input(INPUT_POST, 'education', FILTER_SANITIZE_STRING)); 
    $SkillsRequired = toPascalCase(filter_input(INPUT_POST, 'serviceType', FILTER_SANITIZE_STRING)); 
    $ApplicationDeadlineDate = filter_input(INPUT_POST, 'application_deadline_date', FILTER_SANITIZE_STRING);
    $ApplicationDeadlineTime = filter_input(INPUT_POST, 'application_deadline_time', FILTER_SANITIZE_STRING);
    $Vacancy = filter_input(INPUT_POST, 'vacancy', FILTER_VALIDATE_INT);
    $PostDate = date('Y-m-d H:i:s'); // Current date and time
    $Salary = filter_input(INPUT_POST, 'salary', FILTER_VALIDATE_FLOAT);

    // Job location details
    $Building = toPascalCase(filter_input(INPUT_POST, 'building', FILTER_SANITIZE_STRING));
    $Street = toPascalCase(filter_input(INPUT_POST, 'street', FILTER_SANITIZE_STRING));
    $City = toPascalCase(filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING));
    $State = toPascalCase(filter_input(INPUT_POST, 'state', FILTER_SANITIZE_STRING));
    $Country = toPascalCase(filter_input(INPUT_POST, 'country', FILTER_SANITIZE_STRING));
    $Pincode = filter_input(INPUT_POST, 'pincode', FILTER_SANITIZE_STRING);

    // Handle image upload
    $FeaturingImage = '';
    if (isset($_FILES['featuring_image']) && $_FILES['featuring_image']['error'] == UPLOAD_ERR_OK) {
        $TargetDir = "../images/post/";
        $ImageFileType = strtolower(pathinfo($_FILES['featuring_image']['name'], PATHINFO_EXTENSION));
        $UniqueName = uniqid('job_', true) . '.' . $ImageFileType;
        $TargetFile = $TargetDir . $UniqueName;

        if ($_FILES['featuring_image']['size'] > 5000000) {
            throw new Exception("Sorry, your file is too large.");
        }

        $AllowedFormats = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($ImageFileType, $AllowedFormats)) {
            throw new Exception("Only JPG, JPEG, PNG & GIF files are allowed.");
        }

        if (!getimagesize($_FILES['featuring_image']['tmp_name'])) {
            throw new Exception("File is not an image.");
        }

        if (!move_uploaded_file($_FILES['featuring_image']['tmp_name'], $TargetFile)) {
            throw new Exception("There was an error uploading your file.");
        }
        $FeaturingImage = $TargetFile;
    }

    // Insert the address
    $AddressSql = "INSERT INTO address (building, street, city, state, country, pincode) VALUES (?, ?, ?, ?, ?, ?)";
    $AddressStmt = $conn->prepare($AddressSql);
    $AddressStmt->bind_param("ssssss", $Building, $Street, $City, $State, $Country, $Pincode);

    if (!$AddressStmt->execute()) {
        throw new Exception("Error inserting the address: " . $AddressStmt->error);
    }
    $AddressId = $AddressStmt->insert_id;

    // Prepare the application deadline
    $ApplicationDeadline = $ApplicationDeadlineDate . ' ' . $ApplicationDeadlineTime;

    // Insert the job post
    $JobSql = "INSERT INTO job_posts (featuring_image, job_title, job_type, job_mode, job_description, required_qualification, skills_required, 
                application_deadline, vacancy, post_date, salary, employer_id, address_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $JobStmt = $conn->prepare($JobSql);
    $JobStmt->bind_param("sssssssissdii", 
        $FeaturingImage, 
        $JobTitle, 
        $JobType, 
        $JobMode, 
        $JobDescription, 
        $RequiredQualification, 
        $SkillsRequired, 
        $ApplicationDeadline, 
        $Vacancy, 
        $PostDate, 
        $Salary, 
        $UserId, 
        $AddressId
    );

    if (!$JobStmt->execute()) {
        throw new Exception("Error inserting the job post: " . $JobStmt->error);
    }

    // Commit transaction
    $conn->commit();

    $_SESSION['status_title'] = "Success";
    $_SESSION['status'] = "Job posted successfully.";
    $_SESSION['status_code'] = "success"; 
    header("Location: ../");
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    error_log("Error: " . $e->getMessage());
    $_SESSION['status_title'] = "Error";
    $_SESSION['status'] = $e->getMessage();
    $_SESSION['status_code'] = "error"; 
    header("Location: ../");
    exit();
} finally {
    if (isset($JobStmt)) $JobStmt->close();
    if (isset($AddressStmt)) $AddressStmt->close();
    $conn->close();
}
?>
