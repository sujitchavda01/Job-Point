<?php include 'base root/header.php';
include 'DB Connection/config.php';

// Fetch the latest jobs from the database
$sql = "SELECT * 
        FROM `job_posts`
        ORDER BY `post_date` DESC 
        LIMIT 3";
$result = $conn->query($sql);
// echo $result;
?>  

<div class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1>Starting Your Career So, Let's Do It! 🔥</h1>
            <p>5,000+ Jobs Available, Browse Now</p>
            <div class="search-bar">
                <input type="text" placeholder="Search...">
                <button type="submit"><i class="bi bi-search"></i></button>
            </div>
        </div>
    </div>
</div>

<section class="feature-section">
    <div class="feature">
        <i class="bi bi-briefcase-fill"></i>
        <p class="number" data-target="<?php echo $totalJobs; ?>">0</p>
        <p>JOBS POSTED</p>
    </div>
    <div class="feature">
        <i class="bi bi-person-fill"></i>
        <p class="number" data-target="<?php echo $totalMembers; ?>">0</p>
        <p>MEMBERS</p>
    </div>
    <div class="feature">
        <i class="bi bi-building"></i>
        <p class="number" data-target="<?php echo $totalCompanies; ?>">0</p>
        <p>COMPANIES</p>
    </div>
    <div class="feature">
        <i class="bi bi-globe"></i>
        <p class="number" data-target="<?php echo $totalCities; ?>">0</p>
        <p>CITIES</p>
    </div>
</section>

<div class="categories-section">
    <div class="categories-container">
        <h2 class="section-title">POPULAR JOB CATEGORIES</h2>
        <p class="text-center">A better career is out there, we will help you find it. Select your desired category below.</p>

        <div class="categories-grid">
            <div class="category-item">
                <i class="fa-solid fa-scale-balanced"></i>
                <p>Law</p>
            </div>
            <div class="category-item">
                <i class="fa-solid fa-briefcase-medical"></i>
                <p>Health</p>
            </div>
            <div class="category-item">
                <i class="fa-solid fa-stethoscope"></i>
                <p>Medical</p>
            </div>
            <div class="category-item">
                <i class="fa-solid fa-bullseye"></i>
                <p>Marketing</p>
            </div>
            <div class="category-item">
                <i class="fa-solid fa-building"></i>
                <p>Real Estate</p>
            </div>
            <div class="category-item">
                <i class="fa-solid fa-leaf"></i>
                <p>Agriculture</p>
            </div>
            <div class="category-item">
                <i class="fa-solid fa-users-gear"></i>
                <p>Consultants</p>
            </div>
            <div class="category-item">
                <i class="fa-solid fa-pencil"></i>
                <p>Designing</p>
            </div>
            <div class="category-item">
                <i class="fa-solid fa-wrench"></i>
                <p>Services</p>
            </div>
            <div class="category-item">
                <i class="fa-solid fa-gears"></i>
                <p>Engineering</p>
            </div>
            <div class="category-item">
                <i class="fa-solid fa-headset"></i>
                <p>Call Center</p>
            </div>
            <div class="category-item">
                <i class="fa-solid fa-cart-shopping"></i>
                <p>E-Commerce</p>
            </div>
            <div class="category-item">
                <i class="fa-solid fa-bus"></i>
                <p>Transport</p>
            </div>
            <div class="category-item">
                <i class="fa-solid fa-code"></i>
                <p>Programming</p>
            </div>
            <div class="category-item">
                <i class="fa-solid fa-chart-pie"></i>
                <p>Banking/Finance</p>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
<div class="row">
    <div class="col-lg-8">
        <h3 class="mb-4">Latest Jobs</h3>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <a href="Other Pages/job_details.php?job_id=<?php echo $row['job_id']; ?>" class="text-decoration-none text-dark">
                <div class="job-card d-flex align-items-center">
                    <img src="images/post/<?php echo htmlspecialchars($row['featuring_image']); ?>" alt="Job Icon">
                    <div class="ms-3 w-100">
                        <h5><?php echo htmlspecialchars($row['job_title']); ?></h5>
                        <div class="job-info">
                            <span>Salary:  <?php echo htmlspecialchars($row['salary']); ?></span>
                            <span>Positions: <?php echo htmlspecialchars($row['vacancy']); ?></span>
                            <span>Application Deadline: 
                                <?php 
                                    $date = new DateTime($row['application_deadline']);
                                    echo $date->format('d-m-Y h:i A'); 
                                ?>
                            </span>
                        </div>
                        <button class="btn btn-apply mt-2 ms-auto d-block">Apply For Job</button>
                    </div>
                </div>
                </a>
            <?php endwhile; ?>
            <?php else: ?>
            <p>No jobs available at the moment.</p>
        <?php endif; ?>
        
    </div>

    <div class="col-lg-4">
        <div class="subscribe-box">
            <h5 class="text-center">Job Notification</h5>
            <p class="text-center ">Get job notifications directly into your mailbox. Don't worry, we hate SPAM as well.</p>
            <form>
                <div class="mb-3">
                    <input type="text" class="form-control" placeholder="Your Name">
                </div>
                <div class="mb-3">
                    <input type="email" class="form-control" placeholder="Your Email">
                </div>
                <button type="submit" class="btn btn-success w-100">Subscribe</button>
            </form>
        </div>
        <div class="text-end mt-4">
            <img src="images\website\home2.png" alt="Job Notification" class="img-fluid">
        </div>
    </div>
</div>
</div>

<section class="how-it-works">
    <div class="container">
        <h2 class="text-center mb-4">HOW IT WORKS</h2>
        <p class="text-center mb-5">It is very easy to use our job portal. 3 simple steps to find your dream job.</p>

        <div class="row">
            <div class="col-md-4 text-center">
                <img src="images/website/findjob.png" alt="Find a Job" class="img-fluid mb-3"> 
                <h5>FIND A JOB</h5>
            </div>
            <div class="col-md-4 text-center">
                <img src="images/website/apply.png" alt="Apply" class="img-fluid mb-3"> 
                <h5>APPLY</h5>
            </div>
            <div class="col-md-4 text-center">
                <img src="images/website/get_interviewed.png" alt="Get Interviewed" class="img-fluid mb-3"> 
                <h5>GET JOB</h5>
            </div>
        </div>
    </div>
</section>

<?php include 'base root/footer.php'; ?>
