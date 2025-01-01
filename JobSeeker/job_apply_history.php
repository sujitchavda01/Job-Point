<?php
include '../base other/header.php'; 
require '../DB Connection/config.php'; // Database connection

// Check if the user is logged in and is a Job Seeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Job Seeker') {
    $_SESSION['status_title'] = "Unauthorized Access";
    $_SESSION['status'] = "You must log in as a Job Seeker to view your application history.";
    $_SESSION['status_code'] = "error";
    header("Location: ../login.php");
    exit();
}

$userId = (int)$_SESSION['user_id'];

try {
    // Fetch the seeker_id for the logged-in user
    $seekerQuery = "SELECT seeker_id FROM job_seekers WHERE user_id = ?";
    $stmt = $conn->prepare($seekerQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $seekerResult = $stmt->get_result();

    if ($seekerResult->num_rows === 0) {
        throw new Exception("No profile found. Please complete your profile to view application history.");
    }

    $seekerData = $seekerResult->fetch_assoc();
    $seekerId = (int)$seekerData['seeker_id'];

    // Fetch job applications for the seeker
    $applicationQuery = "
        SELECT 
            ja.application_id, ja.status, jp.job_title, jp.job_type, jp.job_mode, 
            jp.salary, jp.application_deadline, jp.featuring_image, 
            eo.company_name, ei.first_name AS employer_first_name, ei.last_name AS employer_last_name
        FROM job_applications ja
        JOIN job_posts jp ON ja.job_id = jp.job_id
        LEFT JOIN employers_organization eo ON jp.user_id = eo.user_id
        LEFT JOIN employers_individual ei ON jp.user_id = ei.user_id
        WHERE ja.seeker_id = ?
        ORDER BY ja.application_id DESC;
    ";

    $stmt = $conn->prepare($applicationQuery);
    $stmt->bind_param("i", $seekerId);
    $stmt->execute();
    $applicationsResult = $stmt->get_result();

    if ($applicationsResult->num_rows === 0) {
        $applications = [];
    } else {
        $applications = $applicationsResult->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    $_SESSION['status_title'] = "❌ Error ❌";
    $_SESSION['status'] = $e->getMessage();
    $_SESSION['status_code'] = "error";
    header("Location: ../");
    exit();
}
?>


  

    <div class="container mt-5 mb-5">
        <h1 class="mb-4">Your Job Application History</h1>
        
        <?php if (empty($applications)) : ?>
            <p>No applications found. Start applying for jobs today!</p>
        <?php else : ?>
            <table class="table table-bordered">
                <thead  class="text-center">
                    <tr>
                        <th>#</th>
                        <th>Job Title</th>
                        <th>Company</th>
                        <th>Job Type</th>
                        <th>Job Mode</th>
                        <th>Salary</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Withdraw Application</th>
                    </tr>
                </thead>
                <tbody  class="text-center">
                    <?php foreach ($applications as $index => $application) : ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($application['job_title']); ?></td>
                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $application['company_name'] 
                                    ?? $application['employer_first_name'] . ' ' . $application['employer_last_name'] 
                                    ?? "Unknown Employer"
                                );
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($application['job_type']); ?></td>
                            <td><?php echo htmlspecialchars($application['job_mode']); ?></td>
                            <td><?php echo htmlspecialchars($application['salary']); ?></td>
                            <td>
                                <?php
                                $deadline = new DateTime($application['application_deadline']);
                                echo $deadline->format('d-m-Y');
                                ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($application['status'] ?? 'Pending'); ?>
                            </td>
                            <td>
                                <?php
                                    if(!($application['status']==='Approved') and !($application['status']==='Done')){
                                ?>
                                    <a href="applicants.php?job_id=" class="btn btn-danger me-2">
                                        <i class="bi bi-x-circle"></i> Cancel Job Application
                                    </a>
                                <?php
                                    }else{?>
                                        <a href="#" class="btn border me-2" style="background-color:#cf576b;color:white;" Title="You can't Withdraw Application Because Your work is Already Assign And Approved By Employer">
                                            <i class="bi bi-x-circle"></i> Cancel Job Application
                                        </a>
                                    <?php    
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <?php include '../base other/footer.php'; ?> <!-- Include footer -->

