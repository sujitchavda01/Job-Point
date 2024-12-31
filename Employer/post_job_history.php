<?php
ob_start(); // Start output buffering

include '../base other/header.php'; // Include your header file

// Database connection
require '../DB Connection/config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['status_title'] = "❌ Error ❌";
    $_SESSION['status'] = "You must be logged in to view this page.";
    $_SESSION['status_code'] = "error";
    header("Location: ../login.php"); // Redirect to login if not logged in
    exit();
}

$userId = $_SESSION['user_id'];

try {
    // Fetch posted jobs by the employer
    $jobQuery = "
        SELECT 
            jp.job_id, jp.job_title, jp.application_deadline 
        FROM job_posts jp 
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

        <div class="col-lg-12 d-flex flex-column align-items-center">
    <h3 class="mb-4">Latest Jobs</h3>
    <div class="job-card d-flex align-items-center col-lg-11">
        <img src="images/post/1.png" alt="Job Icon">
        <div class="ms-3 w-100">
            <h5>Support Staff Required for Call Center</h5>
            <div class="job-info">
                <span>Salary: 5k to 15k</span>
                <span>Positions: 4</span>
                <span>Application Deadline: October 31, 2022</span>
            </div>
            <!-- <button class="btn btn-apply mt-2 ms-auto d-block">Apply For Job</button> -->
                <a href="applicants.php?job_id=<?php echo $job['job_id']; ?>" class="btn btn-primary  mt-2 ms-auto d-block w-25">
                    <i class="bi bi-person-vcard"></i> View Applicants
                </a>
                <a href="applicants.php?job_id=<?php echo $job['job_id']; ?>" class="btn btn-danger  mt-2 ms-auto d-block w-25">
                    <i class="bi bi-trash3-fill"></i> Delete Post
                </a>
        </div>
    </div>
</div>

<div class="container mt-5 mb-5">
    <h2>Your Posted Jobs</h2>
    <hr>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Job Title</th>
                <th>Application Deadline</th>
                <th>Applicants</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($jobs as $job): ?>
                <tr>
                    <td><?php echo htmlspecialchars($job['job_title']); ?></td>
                    <td>
                        <?php
                        $deadline = new DateTime($job['application_deadline']);
                        echo $deadline->format('d-m-Y h:i A');
                        ?>
                    </td>
                    <td>
                        <a href="applicants.php?job_id=<?php echo $job['job_id']; ?>" class="btn btn-primary btn-sm">
                            View Applicants
                        </a>
                        <a href="applicants.php?job_id=<?php echo $job['job_id']; ?>" class="btn btn-danger btn-sm">
                            Delete Post
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../base other/footer.php'; ?>

<?php ob_end_flush(); // End output buffering and flush output ?>
