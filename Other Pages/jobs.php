<?php include '../base other/header.php'; ?>
<?php
// Database connection
require '../DB Connection/config.php';

// Initial fetch for job posts to display on page load
$query = "SELECT jp.job_id, jp.featuring_image, jp.job_title, jp.salary, jp.vacancy, jp.application_deadline,
            jp.job_description, addr.city, addr.state
            FROM job_posts jp
            JOIN address addr ON jp.address_id = addr.address_id
            JOIN users u ON jp.user_id = u.user_id
            WHERE u.user_type IN ('Employer Individual', 'Employer Organization')
            LIMIT 2;"; // Limit initial jobs displayed
$result = mysqli_query($conn, $query);
?>

<section class="hero-banner">
    <div class="container">
        <h1>JOBS LISTING</h1>
    </div>
</section>

<div class="container mt-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <!-- Sidebar Location -->
            <div class="sidebar">
                <h5>JOB LOCATIONS</h5>
                <hr/>
                <ul class="list-unstyled" id="job-location-list">
                    <li data-location="">All Locations</li>
                    <li data-location="AHMEDABAD">AHMEDABAD</li>
                    <li data-location="DELHI">DELHI</li>
                    <li data-location="HYDERABAD">HYDERABAD</li>
                    <li data-location="INDORE">INDORE</li>
                    <li data-location="MUMBAI">MUMBAI</li>
                    <li data-location="PUNE">PUNE</li>
                    <li data-location="RAJKOT">RAJKOT</li>
                    <li data-location="SURAT">SURAT</li>
                </ul>
            </div>

            <!-- Sidebar Category -->
            <div class="sidebar">
                <h5>JOB CATEGORY</h5>
                <hr/>
                <ul class="list-unstyled" id="job-category-list">
                    <li data-category="">All Categories</li>
                    <li data-category="IT">IT</li>
                    <li data-category="Finance">Finance</li>
                    <li data-category="Healthcare">Healthcare</li>
                    <li data-category="Engineering">Engineering</li>
                    <li data-category="Marketing">Marketing</li>
                </ul>
            </div>
        </div>

        <!-- Job Listings -->
        <div class="col-lg-9">
            <h2 class="mb-3">Latest Jobs</h2>
            <hr />
            <div id="job-listing-container">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <a href="job_details.php?job_id=<?php echo $row['job_id']; ?>" class="text-decoration-none text-dark">
                            <div class="job-card d-flex align-items-center">
                                <div class="image-container">
                                    <img src="../images/post/<?php echo htmlspecialchars($row['featuring_image']); ?>" alt="Job Icon">
                                </div>
                                <div class="ms-3 w-100">
                                    <h5><?php echo htmlspecialchars($row['job_title']); ?></h5>
                                    <div class="job-info d-flex gap-3">
                                        <span>Salary: <?php echo htmlspecialchars($row['salary']); ?></span>
                                        <span>Positions: <?php echo htmlspecialchars($row['vacancy']); ?></span>
                                        <span>Application Deadline:
                                            <?php
                                            // Format the application deadline
                                            $date = new DateTime($row['application_deadline']);
                                            echo $date->format('d-m-Y h:i A'); // Display date and time in dd-mm-yyyy hh:mm AM/PM format
                                            ?>
                                        </span>
                                    </div>
                                    <div class="text-muted">
                                        <small>Location: <?php echo htmlspecialchars($row['city'] . ', ' . $row['state']); ?></small>
                                    </div>
                                    <p class="job-description text-muted">
                                        <small>Job Description:
                                        <?php
                                        // Display only the starting portion of the job description
                                        echo htmlspecialchars(substr($row['job_description'], 0, 70)) . '...<u>more</u>';
                                        ?></small>
                                    </p>
                                </div>
                            </div>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No posts found.</p>
                <?php endif; ?>
            </div>
            <p id="no-more-jobs" style="display: none; color: red;">No more job posts found.</p>
            <!-- Centering Load More Jobs Button -->
            <div class="text-center mt-3" id="load-more-button-div">
                <button id="load-more-btn" class="btn btn-primary">Load More Jobs</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let currentPage = 1;
    const jobsPerPage = 2; // Number of jobs to load per request

    function loadJobs(location = '', category = '', page = 1) {
        $.ajax({
            url: '../Process/fetch_jobs.php',
            method: 'GET',
            data: {
                location: location,
                category: category,
                page: page,
                jobsPerPage: jobsPerPage
            },
            success: function(data) {
                if (data.trim() === "") { // Check if no jobs were returned
                    $('#load-more-btn').prop('disabled', true); // Disable the load more button
                    $('#no-more-jobs').show(); // Show the no more jobs message
                } else {
                    $('#load-more-btn').prop('disabled', false); // Enable the load more button
                    $('#no-more-jobs').hide(); // Hide no more jobs message
                    if (page === 1) {
                        $('#job-listing-container').html(data); // Clear the previous jobs
                    } else {
                        $('#job-listing-container').append(data); // Append new jobs
                    }
                }
                currentPage = page;
            }
        });
    }

    // Initial load
    loadJobs();

    // Handle click on location
    $('#job-location-list li').on('click', function() {
        $('#job-location-list li').removeClass('selected');
        $(this).addClass('selected');
        const location = $(this).data('location');
        currentPage = 1; // Reset current page to 1 on filter change
        loadJobs(location);
    });

    // Handle click on category
    $('#job-category-list li').on('click', function() {
        $('#job-category-list li').removeClass('selected');
        $(this).addClass('selected');
        const category = $(this).data('category');
        currentPage = 1; // Reset current page to 1 on filter change
        loadJobs('', category);
    });

    // Load more jobs
    $('#load-more-btn').on('click', function() {
        loadJobs('', '', currentPage + 1); // Load next page
    });
});
</script>

<?php include '../base other/footer.php'; ?>
