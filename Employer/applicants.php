<?php
ob_start(); // Ensure the session is started
include '../base other/header.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    $_SESSION['status_title'] = "😔 Sorry 😔";
    $_SESSION['status'] = "You must log in to access this page.";
    $_SESSION['status_code'] = "error";
    header("Location: http://localhost/Job%20Point/");
    exit;
}
// Include necessary files

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
            ja.status AS application_status,
            u.profile_photo AS profile_photo
        FROM job_applications ja
        JOIN job_seekers js ON ja.seeker_id = js.seeker_id
        JOIN users u ON js.user_id = u.user_id
        WHERE ja.job_id = ? AND js.job_status != 'Active';
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $jobId);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {
        return []; // Return an empty array if no non-active applicants found
    }

    $applicants = $result->fetch_all(MYSQLI_ASSOC);

    // Calculate and add average rating for each applicant
    foreach ($applicants as &$applicant) {
        $applicant['rating'] = calculateAverageRating($conn, $applicant['seeker_id']);
    }

    return $applicants;
}



// Function to calculate the average rating
function calculateAverageRating($conn, $seekerId) {
    $query = "SELECT AVG(rating) AS average_rating FROM employer_feedback WHERE seeker_id = ?;";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $seekerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return is_null($row['average_rating']) ? "No ratings yet" : round($row['average_rating'], 1);
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
        updateJobSeekerStatus($conn, $applicationId); 
        decrementVacancyCount($conn, $jobId); 
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

    // Employer email and details
    $employerQuery = "
        SELECT u.email, 
               CASE WHEN ei.user_id IS NOT NULL THEN CONCAT(ei.first_name, ' ', ei.middle_name, ' ', ei.last_name)
                    ELSE eo.company_name END AS employer_name,
               CASE WHEN ei.user_id IS NOT NULL THEN u.contact_no 
                    ELSE eo.company_contact_no END AS contact_number
        FROM job_posts jp
        JOIN users u ON jp.user_id = u.user_id
        LEFT JOIN employers_individual ei ON u.user_id = ei.user_id
        LEFT JOIN employers_organization eo ON u.user_id = eo.user_id
        WHERE jp.job_id = ?;
    ";
    
    $employerStmt = $conn->prepare($employerQuery);
    $employerStmt->bind_param("i", $jobId);
    $employerStmt->execute();
    $employerResult = $employerStmt->get_result();
    $employerRow = $employerResult->fetch_assoc();
    $to2 = $employerRow['email'];
    
    $verificationCode = str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT);
    $updateQuery = "UPDATE job_applications SET verification_code = ? WHERE application_id = ?;";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("si", $verificationCode, $applicationId);
    if (!$updateStmt->execute()) {
        setSessionStatus("❌ Error ❌", "Failed to update verification code in the database.", "error");
        return;
    }

    // Fetch job details
    $jobQuery = "
        SELECT jp.job_title, jp.job_description, jp.salary, jp.job_type, jp.job_mode, jp.application_deadline, 
               a.building, a.street, a.city, a.state, a.country, a.pincode
        FROM job_posts jp
        JOIN address a ON jp.address_id = a.address_id
        WHERE jp.job_id = ?;
    ";

    $jobStmt = $conn->prepare($jobQuery);
    $jobStmt->bind_param("i", $jobId);
    $jobStmt->execute();
    $jobResult = $jobStmt->get_result();
    $jobDetails = $jobResult->fetch_assoc();

    // Prepare email details for job seeker
    $subject = "Your Job Application Status";

    $body = "<html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
            }
            .container {
                width: 80%;
                margin: 0 auto;
                padding: 20px;
                border: 1px solid #ccc;
                border-radius: 8px;
                background-color: #f9f9f9;
            }
            h2 {
                color: #4CAF50;
            }
            .job-details, .verification-code {
                margin-top: 20px;
                padding: 10px;
                background-color: #e9ffe9;
                border: 1px solid #4CAF50;
                border-radius: 5px;
            }
            .footer {
                margin-top: 20px;
                font-size: 14px;
                color: #666;
            }
            .call-to-action {
                margin-top: 20px;
                padding: 10px;
                background-color: #4CAF50;
                color: white;
                text-align: center;
                border-radius: 5px;
            }
            .call-to-action a {
                color: white;
                text-decoration: none;
                font-weight: bold;
            }
            .verification-code {
                display: block;
                padding: 15px;
                margin: 20px 0;
                font-size: 20px;
                font-weight: bold;
                color: #ffffff;
                background-color: #2196F3; /* Blue background */
                border-radius: 5px;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>Application Status Update</h2>
            <p>Hello,</p>
            <p>Your application for the following job has been <strong>" . ($status === "approved" ? "approved" : "rejected") . "</strong>.</p>
            
            <div class='job-details '>
                <strong>Job Title:</strong> " . htmlspecialchars($jobDetails['job_title']) . "<br>
                <strong>Job Description:</strong> " . htmlspecialchars($jobDetails['job_description']) . "<br>
                <strong>Salary:</strong> " . htmlspecialchars($jobDetails['salary']) . "<br>
                <strong>Job Type:</strong> " . htmlspecialchars($jobDetails['job_type']) . "<br>
                <strong>Job Mode:</strong> " . htmlspecialchars($jobDetails['job_mode']) . "<br>
                <strong>Job Location:</strong><br>
                " . htmlspecialchars($jobDetails['building']) . ", " . htmlspecialchars($jobDetails['street']) . ", " . 
                htmlspecialchars($jobDetails['city']) . ", " . htmlspecialchars($jobDetails['state']) . ", " . 
                htmlspecialchars($jobDetails['country']) . " - " . htmlspecialchars($jobDetails['pincode']) . "<br>
            </div>
            
            <div class='verification-code'>
                Your verification code is: <strong>$verificationCode</strong>
            </div>
                   <h3>Employer Information</h3>
            <p><strong>Employer Name:</strong> " . htmlspecialchars($employerRow['employer_name']) . "</p>
            <p><strong>Contact Number:</strong> " . htmlspecialchars($employerRow['contact_number']) . "</p>
            <p><strong>Email:</strong> " . htmlspecialchars($to2) . "</p>
    
            <div class='call-to-action'>
                <p>If you have been approved, please contact the employer or be on location within <strong>24 hours</strong> to discuss the next steps for your employment. We look forward to welcoming you to the team!</p>
                <p>If you have any questions, feel free to reach out to us at <strong>jobpoint@gmail.com</strong>.</p>
            </div>
    
     
            
            
        </div>
    </body>
    </html>";
    
    

    // Send email to the job seeker
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->isHTML(true);
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->Username = " "; // Your email
    $mail->Password = ""; // Your email password
    $mail->setFrom(" ", "Job Point"); 
    $mail->addAddress($to);
    $mail->Subject = $subject;
    $mail->Body = $body;

    echo $body;

    if (!$mail->send()) {
        setSessionStatus("❌ Error ❌", "Mail could not be sent to job seeker.", "error");
        return; 
    }

    // Prepare email details for the employer
    $subject2 = "Verify Job Seeker: Application ";

    $body2 = "
    <html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333333;
            }
            .container {
                max-width: 600px;
                margin: 20px auto;
                padding: 20px;
                border: 1px solid #e0e0e0;
                border-radius: 10px;
                background-color: #f9f9f9;
            }
            .header {
                text-align: center;
                padding-bottom: 20px;
            }
            .header h2 {
                color: #4CAF50;
                margin: 0;
            }
            .content {
                margin-bottom: 20px;
            }
            .button-container {
                text-align: center;
                margin-top: 20px;
            }
            .btn {
                display: inline-block;
                padding: 10px 20px;
                background-color: #4CAF50;
                color: #ffffff;
                text-decoration: none;
                border-radius: 5px;
                font-size: 16px;
            }
            .btn:hover {
                background-color: #45a049;
            }
            .footer {
                text-align: center;
                margin-top: 20px;
                font-size: 14px;
                color: #888888;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Verify Job Seeker</h2>
            </div>
            <div class='content'>
                <p>Dear ". htmlspecialchars($employerRow['employer_name']) .",</p>
                <p>You have received a job application for the position of <strong>". htmlspecialchars($jobDetails['job_title']) ."</strong>.</p>
                
                <p>Please verify the job seeker's application details by clicking the button below. Once on the verification page, you will be prompted to enter the unique verification code provided by the job seeker to confirm their identity.</p>
                
                <p><strong>Instructions:</strong></p>
                <ul>
                    <li>Click the button below to open the verification page.</li>
                    <li>Enter the unique verification code shared by the job seeker.</li>
                    <li>Review the application and complete the verification process.</li>
                </ul>
                
                <div class='button-container'>
                    <a href='http://localhost/Job%20Point/Process/verify_job_seeker.php?job_id=" . urlencode($jobId) . "' class='btn' style='color:white;'>Verify Job Seeker</a>
                </div>
            </div>
            <div class='footer'>
                <p>Best regards,<br>Job Point Team</p>
            </div>
        </div>
    </body>
    </html>
    ";


    // Send email to the employer
    $mail->clearAddresses(); // Clear previous recipient
    $mail->addAddress($to2); // Add employer's email
    $mail->Subject = $subject2;
    $mail->Body = $body2;

    // Send the email to the employer
    if (!$mail->send()) {
        setSessionStatus("❌ Error ❌", "Mail could not be sent to employer.", "error");
    } else {
        setSessionStatus("✔ Success ✔", "Emails sent successfully to both the job seeker and employer.", "success");
    }
}



// Function to get job title by job ID


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
<style>
    .custom-select-wrapper {
    position: relative;
    display: inline-block;
    width: 100%; /* Adjust width as needed */
}

.custom-select {
    appearance: none; /* Remove default arrow */
    -webkit-appearance: none;
    -moz-appearance: none;
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    background: white;
    font-size: 16px;
    cursor: pointer;
}

.custom-select:disabled {
    background: #e9ecef; /* Optional: disabled styling */
    cursor: not-allowed;
}

.custom-select-wrapper::after {
    content: '▼'; /* Custom arrow icon */
    position: absolute;
    top: 50%;
    right: 10px; /* Adjust spacing from right */
    transform: translateY(-50%);
    pointer-events: none; /* Prevent interference with select actions */
    font-size: 12px; /* Arrow size */
    color: #555; /* Arrow color */
}

</style>
<div class="container mt-5 mb-5" >
    <h2>Applicants for Job ID: <?php echo htmlspecialchars($jobId); ?></h2>
    <hr>
    <?php if (empty($applicants)): ?>
        <div class="alert alert-warning">No non-active job seekers found for this job.</div>
    <?php else: ?>


    <div class="container mt-5 mb-5" >
    <?php if (empty($applicants)): ?>
        <div class="alert alert-warning">No non-active job seekers found for this job.</div>
    <?php else: ?>
        <div class="row" >
            <?php foreach ($applicants as $applicant): ?>
                <div class="col-sm-3 mb-3  mb-sm-0 p-2">
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="profile-main text-center m-0">
                                <div class="rounded-circle bg-secondary text-white d-inline-flex justify-content-center align-items-center"
                                    style="width: 150px; height: 150px; overflow: hidden;">
                                    <img src="../images/profile/<?php echo htmlspecialchars($applicant['profile_photo']); ?>"
                                        alt="Profile Photo" style="border-radius: 50%; height: 200px; width: 200px;" loading="lazy"
                                        id="profile_photo_display">
                                </div>

                                <h5 class="card-title"><?php echo htmlspecialchars($applicant['applicant_name']); ?></h5>
                                <p class="rating">
                                    Rating:
                                    <span class="stars">
                                        <?php if (isset($applicant['rating']) && is_numeric($applicant['rating']) && $applicant['rating'] > 0): ?>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fa <?php echo $i <= $applicant['rating'] ? 'fa-star' : 'fa-star-o'; ?>" style="color: #ffcc00;"></i>
                                            <?php endfor; ?>
                                        <?php else: ?>
                                            <span style="color: red;">Not found yet</span>
                                        <?php endif; ?>
                                    </span>
                                </p>


                            </div>
                            <div class="info p-0">
                                
                                <p class="">Email: <?php echo htmlspecialchars($applicant['applicant_email']); ?></p>
                                <p class="">Job Status: <?php echo htmlspecialchars($applicant['application_status']); ?></p>
                                
                            </div>
                            <div class="view-profile mb-2 text-center">
                                <a href="../Other Pages/view_job_seeker_profile.php?seeker_id=<?php echo htmlspecialchars($applicant['seeker_id']); ?>" 
                                class="btn btn-custom btn-job-seeker" 
                                style="text-align: center; display: inline-block; width: 100%;">
                                    View Profile
                                </a>
                            </div>


                            <form action="" method="post" style="display: flex; align-items: center;">
                                <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($applicant['application_id']); ?>">
                                <div class="custom-select-wrapper mb-2" style="margin-right: 10px;">
                                    <select name="status" class="form-control custom-select" required <?php echo $applicant['application_status'] !== 'Pending' ? 'disabled' : ''; ?>>
                                        <option value="Pending" <?php echo $applicant['application_status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Approved" <?php echo $applicant['application_status'] === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="Rejected" <?php echo $applicant['application_status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                        <?php if ($applicant['application_status'] === 'Done'): ?>
                                            <option value="Done" <?php echo $applicant['application_status'] === 'Done' ? 'selected' : ''; ?>>Done</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-custom btn-employer" <?php echo $applicant['application_status'] !== 'Pending' ? 'disabled' : ''; ?>>Submit</button>
                            </form>
                        </div>
                    </div>
                </div>


                
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>


    <?php endif; ?>
</div>

<?php include '../base other/footer.php'; ?>
<?php ob_end_flush(); ?>
