<?php
require '../DB Connection/config.php';

// Get parameters from the AJAX request
$location = isset($_GET['location']) ? $_GET['location'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$jobsPerPage = isset($_GET['jobsPerPage']) ? (int)$_GET['jobsPerPage'] : 2; // Set to 2 as per your initial example

$offset = ($page - 1) * $jobsPerPage;

// Build the SQL query with filters
$query = "SELECT jp.job_id, jp.featuring_image, jp.job_title, jp.salary, jp.vacancy, jp.application_deadline,
            jp.job_description, addr.city, addr.state
            FROM job_posts jp
            JOIN address addr ON jp.address_id = addr.address_id
            JOIN users u ON jp.user_id = u.user_id
            WHERE u.user_type IN ('Employer Individual', 'Employer Organization')";

if ($location) {
    $query .= " AND addr.city = '" . mysqli_real_escape_string($conn, $location) . "'";
}

// Remove the category filter if the column does not exist
if ($category) {
    // Uncomment this line if you have the category column available
    // $query .= " AND jp.category = '" . mysqli_real_escape_string($conn, $category) . "'";
}

// Add limit for pagination
$query .= " LIMIT $offset, $jobsPerPage";

$result = mysqli_query($conn, $query);

// Output the job listings
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Output job cards (similar to your main file)
        echo '<a href="job_details.php?job_id=' . $row['job_id'] . '" class="text-decoration-none text-dark">';
        echo '<div class="job-card d-flex align-items-center">';
        echo '<div class="image-container">';
        echo '<img src="../images/post/' . htmlspecialchars($row['featuring_image']) . '" alt="Job Icon">';
        echo '</div>';
        echo '<div class="ms-3 w-100">';
        echo '<h5>' . htmlspecialchars($row['job_title']) . '</h5>';
        echo '<div class="job-info d-flex gap-3">';
        echo '<span>Salary: ' . htmlspecialchars($row['salary']) . '</span>';
        echo '<span>Positions: ' . htmlspecialchars($row['vacancy']) . '</span>';
        echo '<span>Application Deadline: ' . (new DateTime($row['application_deadline']))->format('d-m-Y h:i A') . '</span>';
        echo '</div>';
        echo '<div class="text-muted">';
        echo '<small>Location: ' . htmlspecialchars($row['city'] . ', ' . $row['state']) . '</small>';
        echo '</div>';
        echo '<p class="job-description text-muted"><small>Job Description: ' . htmlspecialchars(substr($row['job_description'], 0, 70)) . '...<u>more</u></small></p>';
        echo '<button class="btn btn-apply mt-2 ms-auto d-block">Apply For Job</button>';
        echo '</div>';
        echo '</div></a>';
    }
} else {
    // Return an empty response to indicate no more jobs available
    echo ""; // An empty response is used to indicate no jobs were found
}
?>
