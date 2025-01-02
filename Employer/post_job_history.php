<?php
ob_start(); // Start output buffering

include '../base other/header.php'; // Include your header file

// Database connection
require '../DB Connection/config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    $_SESSION['status_title'] = "❌ Error ❌";
    $_SESSION['status'] = "You must be logged in to view this page.";
    $_SESSION['status_code'] = "error";
    header("Location: http://localhost/Job%20Point/"); // Redirect to login if not logged in
    exit();
}
else{
$userId = $_SESSION['user_id'];

try {
    // Fetch posted jobs by the employer
    $jobQuery = "
        SELECT jp.*, addr.building, addr.street, addr.city, addr.state, addr.country, addr.pincode
        FROM job_posts jp
        JOIN address addr ON jp.address_id = addr.address_id
        JOIN users u ON jp.user_id = u.user_id
        WHERE u.user_id = ?
        ORDER BY jp.application_deadline DESC;
    ";
    $stmt = $conn->prepare($jobQuery);
    $stmt->bind_param("i", $userId); // Bind the userId as an integer parameter
    $stmt->execute();
    $jobResult = $stmt->get_result();

    if (!$jobResult || mysqli_num_rows($jobResult) === 0) {
        throw new Exception("No jobs posted yet.");
    }

    $jobs = mysqli_fetch_all($jobResult, MYSQLI_ASSOC); // Fetch all jobs into an array
} catch (Exception $e) {
    $_SESSION['status_title'] = "❌ Error ❌";
    $_SESSION['status'] = $e->getMessage();
    $_SESSION['status_code'] = "error";
    header("Location: ../"); // Redirect to home or appropriate page
    exit();
}
?>

<!-- Job Posts -->
<div class="container">
    <h3 class="text-center my-4">Your Posted Jobs</h3>
    <?php foreach ($jobs as $job): ?>
    <a href="../Other Pages/job_details.php?job_id=<?php echo $job['job_id']; ?>" class="text-decoration-none text-dark">
    <div class="row justify-content-center mb-4">
        <div class="job-card d-flex align-items-center col-lg-11" style="border: 1px solid #ddd; padding: 15px; position: relative;">
            <img src="../images/post/<?php echo htmlspecialchars($job['featuring_image']); ?>" alt="Job Icon" style="width: 100px; height: 100px; object-fit: cover;">
            <div class="ms-3 w-100">
                <h5><?php echo htmlspecialchars($job['job_title']); ?></h5>
                <div class="job-info d-flex gap-3" style="margin-bottom: 10px;">
                    <span>Salary: <?php echo htmlspecialchars($job['salary']); ?></span>
                    <span>Positions: <?php echo htmlspecialchars($job['vacancy']); ?></span>
                    <span>Application Deadline:
                        <?php
                        // Format the application deadline
                        $date = new DateTime($job['application_deadline']);
                        echo $date->format('d-m-Y h:i A'); // Display date and time in dd-mm-yyyy hh:mm AM/PM format
                        ?>
                    </span>
                </div>
                <div class="text-muted" style="margin-bottom: 10px;">
                    <small>
                        Location: 
                        <?php 
                        // Display the address details
                        $address = sprintf(
                            "%s, %s, %s, %s, %s - %s",
                            htmlspecialchars($job['building']),
                            htmlspecialchars($job['street']),
                            htmlspecialchars($job['city']),
                            htmlspecialchars($job['state']),
                            htmlspecialchars($job['country']),
                            htmlspecialchars($job['pincode'])
                        );
                        echo $address;
                        ?>
                    </small>
                </div>
                <p class="job-description text-muted" style="margin-bottom: 10px; max-height: 60px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    <small>Job Description:
                        <?php
                        // Display only the starting portion of the job description
                        echo htmlspecialchars(substr($job['job_description'], 0, 70)) . '...<u>more</u>';
                        ?>
                    </small>
                </p>
                <!-- Buttons -->
                <div class="d-flex justify-content-end" style="position: absolute; bottom: 15px; right: 15px;">
                    <a href="applicants.php?job_id=<?php echo $job['job_id']; ?>" class="btn btn-primary me-2">
                        <i class="bi bi-person-vcard"></i> View Applicants
                    </a>
                    <a href="delete_job.php?job_id=<?php echo $job['job_id']; ?>" class="btn btn-danger">
                        <i class="bi bi-trash3-fill"></i> Delete Post
                    </a>
                </div>
            </div>
        </div>
    </div>
    </a>
    <?php endforeach; ?>
</div>

<?php include '../base other/footer.php'; }?>

<?php ob_end_flush(); // End output buffering and flush output ?>
