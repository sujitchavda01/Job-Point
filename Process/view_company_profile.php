<?php
// Start output buffering
ob_start();

// Start session
include '../base other/header.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']); // Adjust based on your login logic

// Assuming you have the company ID from the previous page
$organization_id = isset($_GET['organization_id']) ? $_GET['organization_id'] : null;

// Redirect if not logged in
if (!$is_logged_in) {
    $_SESSION['status_title'] = "Access Denied!";
    $_SESSION['status'] = "You need to be logged in to view this information.";
    $_SESSION['status_code'] = "warning";
    header("Location: ../Other Pages/companies.php");
    exit();
}

// Fetch company details if user is logged in
if ($organization_id) {
    require '../DB Connection/config.php';

    // Fetch company details
    $query = "
    SELECT eo.company_name, eo.registration_number, eo.company_website, 
           u.profile_photo, eo.banner_photo, eo.recruiter_name,
           u.contact_no, u.email, 
           eo.company_contact_no, a.building, a.street, a.city, 
           a.state, a.country, a.pincode
    FROM employers_organization eo
    JOIN users u ON eo.user_id = u.user_id
    JOIN address a ON eo.address_id = a.address_id
    WHERE eo.organization_id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $organization_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $company_data = $result->fetch_assoc();
}
?>

<style>
    .banner-container {
        position: relative;
        height: 250px;
        background: url('../images/banner/<?php echo htmlspecialchars($company_data['banner_photo'] ?? 'default_banner.jpg'); ?>') no-repeat center center;
        background-size: cover;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .profile-photo-container {
        position: absolute;
        bottom: -75px;
        text-align: center;
    }
    .profile-photo-container img {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        border-top: 4px solid white;
        border-left: 4px solid white;
        border-right: 4px solid white;
    }
    .content-container {
        margin-top: 150px;
    }
</style>

<div class="container mt-4 mb-5">
    <h3 class="text">Company Profile</h3>
    <div class="row bg-white pb-4 rounded mt-2 shadow content-container">
        <div class="banner-container mb-4">
            <div class="profile-photo-container">
                <img src="../images/profile/<?php echo htmlspecialchars($company_data['profile_photo'] ?? 'default_profile_photo.png'); ?>" 
                     alt="Profile Photo" loading="lazy">
            </div>
        </div>
        
        <div class="col-md-12 mt-5 p-3">
            <div class="row mb-3">
                <h3 class="text">Company Information</h3>
                <div class="col-sm-6 col-lg-4 mb-3">
                    <h5>Company Name:</h5>
                    <p><?php echo htmlspecialchars($company_data['company_name'] ?? '-'); ?></p>
                </div>
                <div class="col-sm-6 col-lg-4 mb-3">
                    <h5>Company Registration Number:</h5>
                    <p><?php echo htmlspecialchars($company_data['registration_number'] ?? '-'); ?></p>
                </div>
                <div class="col-sm-4 mb-3">
                    <h5>Company Website:</h5>
                    <p><a href="<?php echo htmlspecialchars($company_data['company_website'] ?? '#'); ?>" target="_blank"><?php echo htmlspecialchars($company_data['company_website'] ?? '-'); ?></a></p>
                </div>
            </div>
            <div class="row mb-3">
                <h3 class="text">Recruiter Information</h3>
                <div class="col-sm-6 col-lg-6 mb-3">
                    <h5>Recruiter Name:</h5>
                    <p><?php echo htmlspecialchars($company_data['recruiter_name'] ?? '-'); ?></p>
                </div>
                <div class="col-sm-6 col-lg-6 mb-3">
                    <h5>Contact No:</h5>
                    <p><?php echo htmlspecialchars($company_data['contact_no'] ?? '-'); ?></p>
                </div>
            </div>
            <div class="row mb-3">
                <h3 class="text">Contact Information</h3>
                <div class="col-sm-6 mb-3">
                    <h5>Email:</h5>
                    <p><?php echo htmlspecialchars($company_data['email'] ?? '-'); ?></p>
                </div>
                <div class="col-sm-6 mb-3">
                    <h5>Company Contact No:</h5>
                    <p><?php echo htmlspecialchars($company_data['company_contact_no'] ?? '-'); ?></p>
                </div>
            </div>
            <div class="row">
                <h3 class="text">Address</h3>
                <div class="col-sm-4 mb-3">
                    <h5>Building:</h5>
                    <p><?php echo htmlspecialchars($company_data['building'] ?? '-'); ?></p>
                </div>
                <div class="col-sm-4 mb-3">
                    <h5>Street:</h5>
                    <p><?php echo htmlspecialchars($company_data['street'] ?? '-'); ?></p>
                </div>
                <div class="col-sm-4 mb-3">
                    <h5>City:</h5>
                    <p><?php echo htmlspecialchars($company_data['city'] ?? '-'); ?></p>
                </div>
                <div class="col-sm-4 mb-3">
                    <h5>State:</h5>
                    <p><?php echo htmlspecialchars($company_data['state'] ?? '-'); ?></p>
                </div>
                <div class="col-sm-4 mb-3">
                    <h5>Country:</h5>
                    <p><?php echo htmlspecialchars($company_data['country'] ?? '-'); ?></p>
                </div>
                <div class="col-sm-4 mb-3">
                    <h5>Pincode:</h5>
                    <p><?php echo htmlspecialchars($company_data['pincode'] ?? '-'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Optionally, display status message if set
if (isset($_SESSION['status'])) {
    echo "<div class='alert alert-{$_SESSION['status_code']}'>{$_SESSION['status_title']}: {$_SESSION['status']}</div>";
    unset($_SESSION['status']);
}
include '../base other/footer.php';
ob_end_flush(); // Flush the output buffer
?>
