<?php
ob_start(); // Start output buffering

include '../base other/header.php';

// Database connection
require '../DB Connection/config.php';

// Check if job_id is provided in the URL
if (!isset($_GET['job_id']) || empty($_GET['job_id'])) {
    $_SESSION['status_title'] = "❌ Error ❌";
    $_SESSION['status'] = "Job ID is missing. Please go back and select a job post.";
    $_SESSION['status_code'] = "error";
    header("Location: ../Employer/employer_organization_account.php");
    exit();
}

$jobId = (int)$_GET['job_id'];

try {
    // Fetch job details along with employer information
    $jobQuery = "
        SELECT 
            jp.job_id, jp.featuring_image, jp.job_title, jp.salary, jp.vacancy, 
            jp.application_deadline, jp.job_description, jp.skills_required, 
            addr.city, addr.state, addr.pincode AS zip_code, addr.country,
            u.email AS user_email, u.contact_no AS user_contact, u.user_type,
            COALESCE(eo.company_name, CONCAT(ei.first_name, ' ', ei.middle_name, ' ', ei.last_name)) AS user_name
        FROM job_posts jp
        JOIN address addr ON jp.address_id = addr.address_id
        JOIN users u ON jp.user_id = u.user_id
        LEFT JOIN employers_organization eo ON u.user_id = eo.user_id AND u.user_type = 'organization'
        LEFT JOIN employers_individual ei ON u.user_id = ei.user_id AND u.user_type = 'individual'
        WHERE jp.job_id = ?
        LIMIT 1;
    ";

    $stmt = $conn->prepare($jobQuery);
    $stmt->bind_param("i", $jobId); // Bind the jobId as an integer parameter
    $stmt->execute();
    $jobResult = $stmt->get_result();

    if (!$jobResult || mysqli_num_rows($jobResult) === 0) {
        throw new Exception("Job not found. Please go back and select a valid job post.");
    }

    $jobDetails = mysqli_fetch_assoc($jobResult);
} catch (Exception $e) {
    $_SESSION['status_title'] = "❌ Error ❌";
    $_SESSION['status'] = $e->getMessage();
    $_SESSION['status_code'] = "error";
    header("Location: ../");
    exit();
}
?>

<section class="hero-banner">
    <div class="container">
        <h1><?php echo htmlspecialchars($jobDetails['job_title'] ?? ''); ?></h1>
    </div>
</section>

<div class="container mt-5">
    <div class="row">
        <!-- Job Details -->
        <div class="col-lg-8">
            <div class="job-details-card card p-4 mb-4">
                <h2 class="mb-3">Job Details</h2>
                <hr>
                <img src="../images/post/<?php echo htmlspecialchars($jobDetails['featuring_image'] ?? ''); ?>" 
                     alt="Job Image" class="img-fluid mb-3 rounded" style="height:40vh;">
                <p><strong>Job Title:</strong> <?php echo htmlspecialchars($jobDetails['job_title'] ?? ''); ?></p>
                <p><strong>Salary:</strong> <?php echo htmlspecialchars($jobDetails['salary'] ?? ''); ?></p>
                <p><strong>Vacancies:</strong> <?php echo htmlspecialchars($jobDetails['vacancy'] ?? ''); ?></p>
                <p><strong>Application Deadline:</strong> 
                    <?php
                    $deadline = new DateTime($jobDetails['application_deadline'] ?? 'now'); // Provide default value
                    echo $deadline->format('d-m-Y h:i A');
                    ?>
                </p>
                <p><strong>Skills Required:</strong> <?php echo htmlspecialchars($jobDetails['skills_required'] ?? ''); ?></p>
                <p><strong>Location:</strong> 
                    <?php echo htmlspecialchars($jobDetails['city'] ?? '') . ', ' . htmlspecialchars($jobDetails['state'] ?? '') . ', ' . htmlspecialchars($jobDetails['country'] ?? ''); ?>
                </p>
                <p><strong>Zip Code:</strong> <?php echo htmlspecialchars($jobDetails['zip_code'] ?? ''); ?></p>
                <p><strong>Job Description:</strong></p>
                <p><?php echo nl2br(htmlspecialchars($jobDetails['job_description'] ?? '')); ?></p>
            </div>
        </div>

        <!-- Employer Details -->
        <div class="col-lg-4">
            <div class="employer-details-card card p-4 mb-4">
                <h2 class="mb-3">About the Employer</h2>
                <hr>
                <p><strong>Employer Name:</strong> <?php echo htmlspecialchars($jobDetails['user_name'] ?? 'Not Available'); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($jobDetails['user_email'] ?? 'Not Available'); ?></p>
                <p><strong>Contact:</strong> <?php echo htmlspecialchars($jobDetails['user_contact'] ?? 'Not Available'); ?></p>
            </div>

            <div class="apply-now-card card p-4">
                <h2 class="mb-3">Apply Now</h2>
                <hr>
                <p>Interested in this job? Click the button below to apply.</p>
                <a href="../Process/apply_job.php?job_id=<?php echo $jobDetails['job_id']; ?>" 
                   class="btn w-100 btn-apply">Apply For This Job</a>
            </div>
        </div>
    </div>
</div>
<?php include '../base other/footer.php'; ?>

<?php ob_end_flush(); // End output buffering and flush output ?>
