<?php
ob_start();

// Include necessary files
include '../base other/header.php';
require '../DB Connection/config.php';
require "../vendor/autoload.php"; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

// Check if job_id is provided in the URL
if (!isset($_GET['job_id']) || empty($_GET['job_id'])) {
    setSessionStatus("❌ Error ❌", "Job ID is missing.", "error");
    redirectTo("post_job_history.php");
}

$jobId = (int)$_GET['job_id'];

try {
    // Fetch applicants for the job
    $applicants = fetchApplicants($conn, $jobId);
} catch (Exception $e) {
    setSessionStatus("❌ Error ❌", $e->getMessage(), "error");
    redirectTo("post_job_history.php");
}

// Handle status change if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'], $_POST['status'])) {
    handleStatusChange($conn, $jobId, $_POST['application_id'], $_POST['status']);
}

// Function to set session status and redirect
function setSessionStatus($title, $message, $code) {
    $_SESSION['status_title'] = $title;
    $_SESSION['status'] = $message;
    $_SESSION['status_code'] = $code;
}

function redirectTo($location) {
    header("Location: $location");
    exit();
}

// Function to fetch applicants from the database
function fetchApplicants($conn, $jobId) {
    $query = "
        SELECT 
            ja.application_id,
            js.seeker_id, 
            CONCAT(js.first_name, ' ', js.middle_name, ' ', js.last_name) AS applicant_name, 
            u.email AS applicant_email, 
            ja.status AS application_status
        FROM job_applications ja
        JOIN job_seekers js ON ja.seeker_id = js.seeker_id
        JOIN users u ON js.user_id = u.user_id
        WHERE ja.job_id = ? AND js.job_status != 'Active';  -- Exclude Active applicants
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $jobId);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {
        return []; // Return an empty array if no non-active applicants found
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to handle status change
function handleStatusChange($conn, $jobId, $applicationId, $newStatus) {
    $applicationId = (int)$applicationId;

    // Check vacancies and approved count
    $totalVacancies = getVacancyCount($conn, $jobId);
    $currentApprovedCount = getApprovedCount($conn, $jobId);

    if ($newStatus === 'Approved') {
        if ($currentApprovedCount >= $totalVacancies) {
            setSessionStatus("❌ Error ❌", "Cannot approve more applications than available vacancies. Current vacancies: $totalVacancies.", "error");
            redirectTo($_SERVER['PHP_SELF'] . "?job_id=" . $jobId);
        }
        sendEmailNotification($conn, $applicationId, $jobId, "approved");
        updateJobSeekerStatus($conn, $applicationId); // Update job seeker status to Active
        decrementVacancyCount($conn, $jobId); // Decrement vacancy count
    } elseif ($newStatus === 'Rejected') {
        sendEmailNotification($conn, $applicationId, $jobId, "rejected");
    }

    // Update application status
    updateApplicationStatus($conn, $applicationId, $newStatus);
    setSessionStatus("✅ Success ✅", "Application status updated successfully.", "success");
    redirectTo($_SERVER['PHP_SELF'] . "?job_id=" . $jobId);
}

// Function to get vacancy count
function getVacancyCount($conn, $jobId) {
    $query = "SELECT vacancy FROM job_posts WHERE job_id = ?;";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $jobId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return (int)$row['vacancy'];
}

// Function to get current approved count
function getApprovedCount($conn, $jobId) {
    $query = "SELECT COUNT(*) AS approved_count FROM job_applications WHERE job_id = ? AND status = 'Approved';";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $jobId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return (int)$row['approved_count'];
}

// Function to send email notification
function sendEmailNotification($conn, $applicationId, $jobId, $status) {
    // Fetch job seeker's email
    $emailQuery = "
        SELECT u.email 
        FROM job_applications ja
        JOIN job_seekers js ON ja.seeker_id = js.seeker_id
        JOIN users u ON js.user_id = u.user_id
        WHERE ja.application_id = ?;
    ";
    
    $emailStmt = $conn->prepare($emailQuery);
    $emailStmt->bind_param("i", $applicationId);
    $emailStmt->execute();
    $emailResult = $emailStmt->get_result();
    $emailRow = $emailResult->fetch_assoc();
    $to = $emailRow['email'];

    // Prepare email details
    $subject = "Your Job Application Status";
    $body = "Hello,<br>Your application for Job ID: $jobId has been " . ($status === "approved" ? "approved" : "rejected") . ".<br>Best regards,<br>Your Company";

    $mail = new PHPMailer(true);
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
    if (!$mail->send()) {
        setSessionStatus("❌ Error ❌", "Mail could not be sent.", "error");
    }
}

// Function to update application status
function updateApplicationStatus($conn, $applicationId, $newStatus) {
    $updateQuery = "UPDATE job_applications SET status = ? WHERE application_id = ?;";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("si", $newStatus, $applicationId);

    if (!$updateStmt->execute()) {
        setSessionStatus("❌ Error ❌", "Failed to update application status.", "error");
    }
}

// Function to update job seeker status to Active
function updateJobSeekerStatus($conn, $applicationId) {
    $updateQuery = "UPDATE job_seekers SET job_status = 'Active' WHERE seeker_id = (SELECT seeker_id FROM job_applications WHERE application_id = ?);";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $applicationId);

    if (!$updateStmt->execute()) {
        setSessionStatus("❌ Error ❌", "Failed to update job seeker status.", "error");
    }
}

// Function to decrement vacancy count
function decrementVacancyCount($conn, $jobId) {
    $updateQuery = "UPDATE job_posts SET vacancy = vacancy - 1 WHERE job_id = ? AND vacancy > 0;";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $jobId);

    if (!$updateStmt->execute()) {
        setSessionStatus("❌ Error ❌", "Failed to decrement vacancy count.", "error");
    }
}
?>

<div class="container mt-5 mb-5">
    <h2>Applicants for Job ID: <?php echo htmlspecialchars($jobId); ?></h2>
    <hr>
    <?php if (empty($applicants)): ?>
        <div class="alert alert-warning">No non-active job seekers found for this job.</div>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Applicant Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applicants as $applicant): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($applicant['applicant_name']); ?></td>
                        <td><?php echo htmlspecialchars($applicant['applicant_email']); ?></td>
                        <td><?php echo htmlspecialchars($applicant['application_status']); ?></td>
                        <td>
                            <form action="" method="post">
                                <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($applicant['application_id']); ?>">
                                <select name="status" required <?php echo $applicant['application_status'] !== 'Pending' ? 'disabled' : ''; ?>>
                                    <option value="Pending" <?php echo $applicant['application_status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Approved" <?php echo $applicant['application_status'] === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="Rejected" <?php echo $applicant['application_status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm" <?php echo $applicant['application_status'] !== 'Pending' ? 'disabled' : ''; ?>>Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include '../base other/footer.php'; ?>
<?php ob_end_flush(); ?>
