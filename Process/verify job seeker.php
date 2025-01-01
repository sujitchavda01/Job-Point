<?php
session_start();
ob_start();

require '../DB Connection/config.php'; // Database connection

// Debug session variables
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    $_SESSION['status_title'] = "😔 Sorry 😔";
    $_SESSION['status'] = "You must log in to access this page.";
    $_SESSION['status_code'] = "error";
    header("Location: http://localhost/Job%20Point/");
    exit();
}

// Check if the logged-in user is an employer
$allowedUserTypes = ['Employer Individual', 'Employer Organization'];
if (!in_array($_SESSION['user_type'], $allowedUserTypes)) {
    $_SESSION['status_title'] = "Unauthorized Access";
    $_SESSION['status'] = "You must log in as an Employer to access this page.";
    $_SESSION['status_code'] = "error";
    header("Location: http://localhost/Job%20Point/");
    exit();
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
    } else {
        // Verification code is incorrect
        $_SESSION['status_title'] = "❌ Error ❌";
        $_SESSION['status'] = "The verification code is incorrect.";
        $_SESSION['status_code'] = "error";
    }

    // Redirect back to the verification page or desired location
    header("Location: http://localhost/Job%20Point/Process/verify_job_seeker.php");
    exit();
}

$jobId = (int)$_POST['job_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Job Seeker</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .center-card {
            max-width: 400px;
            margin: auto;
            margin-top: 100px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card center-card">
            <div class="card-header text-center">
                Verify Job Seeker
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="verification_code">Enter Verification Code:</label>
                        <input type="text" class="form-control" id="verification_code" name="verification_code" pattern="\d{9}" required placeholder="9-digit code" maxlength="9">
                    </div>
                    <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($jobId); ?>">
                    <button type="submit" class="btn w-100" style="background-color:#4fa671;color:white;">Verify</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<?php
    // Display the status message if set
    if (isset($_SESSION['status']) && $_SESSION['status'] != '') {
        ?>
        <script>
            swal({
                title: "<?php echo $_SESSION['status_title']; ?>",
                text: "<?php echo $_SESSION['status']; ?>",
                icon: "<?php echo $_SESSION['status_code']; ?>",
                button: "OK",
            });
        </script>
        <?php
        unset($_SESSION['status_title']);
        unset($_SESSION['status']);
        unset($_SESSION['status_code']);
    }
?>

<?php ob_end_flush(); ?>
