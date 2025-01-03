<?php include '../base other/header.php'; ?>

<style>
    .card {
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative; /* Required for absolute positioning of the profile photo */
    }
    .card-body {
        flex-grow: 1; /* Make the body take up the available space */
        padding-top: 50px; /* Add padding to the top to accommodate the overlapping profile photo */
    }
    .company-banner img {
        height: 150px; /* Fixed height for the banner */
        width: 100%; 
        object-fit: cover; /* Ensure the image covers the area */
    }
    .company-profile {
        position: absolute; /* Absolute positioning to overlap the banner */
        top: 100px; /* Adjust this value to position the profile image correctly */
        left: 50%; /* Center the profile photo */
        transform: translateX(-50%); /* Centering adjustment */
        z-index: 1; /* Bring the profile photo in front */
    }
    .company-profile img {
        border-radius: 50%; 
        height: 120px; /* Adjust height as needed */
        width: 120px; /* Adjust width as needed */
        border: 3px solid white; /* Optional: add a border around the profile photo */
    }
</style>

<div class="container mt-5 mb-5">
    <div class="row m-0">
        <?php
        // Database connection
        require '../DB Connection/config.php';

        // Fetch company details
        $query = "
            SELECT eo.organization_id, eo.company_name, eo.banner_photo, eo.company_description, 
                   eo.company_website, u.profile_photo 
            FROM employers_organization eo
            JOIN users u ON eo.user_id = u.user_id
        ";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0):
            while ($company = $result->fetch_assoc()):
        ?>
        <div class="col-md-3 col-sm-6 mb-4"> <!-- This ensures 4 cards per row on medium screens -->
            <div class="card shadow">
                <div class="company-banner mb-3">
                    <img src="../images/banner/<?php echo htmlspecialchars($company['banner_photo']); ?>" 
                         alt="Banner Photo">
                </div>
                <div class="company-profile text-center">
                    <img src="../images/profile/<?php echo htmlspecialchars($company['profile_photo']); ?>" 
                         alt="Profile Photo" loading="lazy">
                </div>
                <div class="card-body">
                    <h5 class="card-title text-center mb-1"> 
                        <?php echo htmlspecialchars($company['company_name']); ?> 
                    </h5>
                    <p class="card-text text-center text-truncate mb-2" title="<?php echo htmlspecialchars($company['company_description']); ?>">
                        <?php echo htmlspecialchars($company['company_description']); ?>
                    </p>
                    <div class="view-profile text-center m-0">
                        <a href="../Process/view_company_profile.php?organization_id=<?php echo htmlspecialchars($company['organization_id']); ?>" 
                           class="btn btn-custom btn-job-seeker" 
                           style="text-align: center; display: inline-block; width: 100%;">
                            View More
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
            endwhile;
        else:
        ?>
        <div class="col-12 text-center">
            <p>No companies found.</p>
        </div>
        <?php
        endif;
        ?>
    </div>
</div>

<?php include '../base other/footer.php'; ?>
