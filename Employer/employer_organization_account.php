<?php
ob_start(); // Start output buffering
include '../base other/header.php';
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
include '../DB Connection/config.php';

try {
    // Check if the user is logged in by verifying session variables
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
        $_SESSION['status_title'] = "🤓 Sorry 🤓";
        $_SESSION['status'] = "Unauthorized Access Attempt Detected";
        $_SESSION['status_code'] = "error";
        header("Location: ../");
        exit();
    } else {
        $user_id = $_SESSION['user_id'];
        $user_type = $_SESSION['user_type'];

        // Fetch user data from the database
        $query = "SELECT u.profile_photo, u.email, u.contact_no, 
                         eo.company_name, eo.registration_number, eo.banner_photo, eo.recruiter_name, 
                         eo.company_contact_no, eo.company_website, eo.company_description, 
                         a.building, a.street, a.city, a.state, a.country, a.pincode
                  FROM users u 
                  JOIN employers_organization eo ON u.user_id = eo.user_id
                  JOIN address a ON eo.address_id = a.address_id
                  WHERE u.user_id = ? AND u.user_type = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $user_id, $user_type);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user_data = $result->fetch_assoc();
            ?>
            <style>
                .banner-container {
                    position: relative;
                    height: 250px;
                    background: url('../images/banner/<?php echo htmlspecialchars($user_data['banner_photo'] ?? 'default_banner.jpg'); ?>') no-repeat center center;
                    

                    background-size: cover;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }
                .banner-container.reduced-opacity {
                    opacity: 0.99; /* Adjust the opacity value as needed */
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
                    padding: 0px;
                    border-top: 4px solid white;
                    border-left: 4px solid white;
                    border-right: 4px solid white;
                    /* border: 4px solid white; */
                }

                .profile-header {
                    margin-top: 100px;
                    text-align: center;
                }

                .profile-header h4 {
                    margin-top: 15px;
                    font-size: 24px;
                    color: #007bff;
                }

                .content-container {
                    margin-top: 150px;
                }
                .camera-icon {
                position: absolute;
                width: 50px;
                height: 50px;
                bottom: 10px; /* Adjust as needed */
                right: 10px; /* Adjust as needed */
                font-size: 24px; /* Size of the icon */
                color: white; /* Color of the icon */
                background: rgba(0, 0, 0, 0.5); /* Optional: semi-transparent background */
                border-radius: 50%; /* Optional: make it circular */
                padding: 5px; /* Optional: padding for spacing */
                display: flex; /* Center the icon */
                justify-content: center; /* Center horizontally */
                align-items: center; /* Center vertically */
                cursor: pointer; /* Change cursor to pointer */
            }

            .banner-container.reduced-opacity::after {
                opacity: 1; /* Show overlay when reduced-opacity is applied */
            }

            /* Hide camera icon by default */
            .camera-icon {
                display: none; /* Hide icon initially */
            }
            #changeBannerPhoto:hover{
                width: 55px;
                height: 55px;
            }
            </style>

            
            <div class="container mt-4 mb-5">
            <h3 class="text">Employer Account</h3>
                <div class="row bg-white pb-4 rounded mt-2 shadow content-container">

                <div class="banner-container mb-4" id="bannerContainer">
                    <!-- Camera Icon for Changing Banner Photo -->
                    <div class="camera-icon" id="changeBannerPhoto" style="position: absolute; bottom: 10px; right: 10px; cursor: pointer;" onclick="document.getElementById('bannerImageInput').click();">
                        <i class="fas fa-camera"></i>
                    </div>

                   

                    <!-- Profile Photo Container -->
                    <div class="profile-photo-container" id="profilePhotoContainer">
                        <img src="../images/profile/<?php echo htmlspecialchars($user_data['profile_photo'] ?? 'default profile photo.png'); ?>" 
                            alt="Profile Photo" id="profile_photo_display" loading="lazy">
                    </div>

                    <!-- Edit Profile Photo Container -->
                    <div class="profile-photo-container" id="editProfilePhotoContainer" style="display: none;">
                        <div class="rounded-circle bg-secondary text-white d-inline-flex justify-content-center align-items-center position-relative"
                            style="width: 150px; height: 150px; overflow: hidden; display: inline-block; vertical-align: top; margin-right: 10px;">
                            <img src="../images/profile/<?php echo htmlspecialchars($user_data['profile_photo'] ?? 'default profile photo.png'); ?>"
                                alt="Profile Photo" style="border-radius: 50%; height: 100%; width: 100%; opacity: 0.5;" id="profileImagePreview">
                            
                            <i class="fas fa-camera position-absolute" style="color: white; font-size: 40px;" onclick="document.getElementById('profileImageInput').click();"></i>
                        </div>
                    </div>
                </div>




                
                    <div class="col-md-3 border-end mt-5 pe-4 ps-4 mb-4 mb-md-0" style="border-right: 2px solid #007bff;">
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action active" data-tab="profileDisplay" id="viewProfileBtn">
                                <i class="fas fa-user-circle me-2"></i> View Profile
                            </a>
                            <a href="#" class="list-group-item list-group-item-action" data-tab="profileEdit" id="editProfileBtn">
                                <i class="fas fa-cogs me-2" style="color: #6c757d;"></i> Edit Profile
                            </a>
                            <a href="#" class="list-group-item list-group-item-action" data-tab="changePassword">
                                <i class="fas fa-key me-2" style="color: #6c757d;"></i> Change Password
                            </a>
                            <a href="#" class="list-group-item list-group-item-action" data-tab="deleteAccount">
                                <i class="fas fa-trash-alt me-2" style="color: #6c757d;"></i> Delete Account
                            </a>
                            <a href="../Process/logout.php" class="list-group-item list-group-item-action" data-tab="logout" style="color:rgb(231, 62, 62);">
                                <i class="fa fa-sign-out me-2" aria-hidden="true"></i> Logout
                            </a>
                        </div>
                    </div>

                    <!-- Tab Content -->
                    <div class="col-md-9 mt-5 ps-md-4">
                        <!-- View Profile Tab -->
                        <div id="profileDisplay" class="tab-content active" style="display: block;">
                            <!-- <h3 class="text">Organization </h3> -->
                            <div class="row mb-3">
                                <h3 class="text">Company Information</h3>
                                <div class="col-sm-6 col-lg-4 mb-3">
                                    <h5>Company Name:</h5>
                                    <p><?php echo htmlspecialchars($user_data['company_name'] ?? '-'); ?></p>
                                </div>
                                <div class="col-sm-6 col-lg-4 mb-3">
                                    <h5>Company Registration Number:</h5>
                                    <p><?php echo htmlspecialchars($user_data['registration_number'] ?? '-'); ?></p>
                                </div>
                                <div class="col-sm-4 mb-3">
                                    <h5>Company Website:</h5>
                                    <p><a href=<?php echo htmlspecialchars($user_data['company_website'] ?? '-'); ?> target="_blank"><?php echo htmlspecialchars($user_data['company_website'] ?? '-'); ?></a></p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <h3 class="text">Recruiter Information</h3>
                                <div class="col-sm-6 col-lg-6 mb-3">
                                    <h5>Recruiter Name:</h5>
                                    <p><?php echo htmlspecialchars($user_data['recruiter_name'] ?? '-'); ?></p>
                                </div>
                                <div class="col-sm-6 col-lg-6 mb-3">
                                    <h5>Contact No.</h5>
                                    <p><?php echo htmlspecialchars($user_data['contact_no'] ?? '-'); ?></p>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <h3 class="text">Contact Information</h3>
                                <div class="col-sm-6 mb-3">
                                    <h5>Email:</h5>
                                    <p><?php echo htmlspecialchars($user_data['email'] ?? '-'); ?></p>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <h5>Company Contact No:</h5>
                                    <p><?php echo htmlspecialchars($user_data['company_contact_no'] ?? '-'); ?></p>
                                </div>
                                
                            </div>

                            
                            <div class="row">
                                <h3 class="text">Address</h3>
                                <div class="col-sm-4 mb-3">
                                    <h5>Building:</h5>
                                    <p><?php echo htmlspecialchars($user_data['building'] ?? '-'); ?></p>
                                </div>
                                <div class="col-sm-4 mb-3">
                                    <h5>Street:</h5>
                                    <p><?php echo htmlspecialchars($user_data['street'] ?? '-'); ?></p>
                                </div>
                                <div class="col-sm-4 mb-3">
                                    <h5>City:</h5>
                                    <p><?php echo htmlspecialchars($user_data['city'] ?? '-'); ?></p>
                                </div>
                                <div class="col-sm-4 mb-3">
                                    <h5>State:</h5>
                                    <p><?php echo htmlspecialchars($user_data['state'] ?? '-'); ?></p>
                                </div>
                                <div class="col-sm-4 mb-3">
                                    <h5>Country:</h5>
                                    <p><?php echo htmlspecialchars($user_data['country'] ?? '-'); ?></p>
                                </div>
                                <div class="col-sm-4 mb-3">
                                    <h5>Pincode:</h5>
                                    <p><?php echo htmlspecialchars($user_data['pincode'] ?? '-'); ?></p>
                                </div>
                            </div>

                            <!-- Add other sections like Contact Information and Address here -->
                        </div>


                        <!-- edit profile -->
                        <div id="profileEdit" class="tab-content" style="display: none;" id="edit_profile">
                           
                        <form id="edit_profile" action="../Process/employer_organization_account_manage.php" method="post" enctype="multipart/form-data">
                                
                            <input type="file" id="bannerImageInput" form="edit_profile" style="display: none;" accept="image/*" name="banner_photo" onchange="previewBannerImage(event)">
                            <input type="file" id="profileImageInput" style="display: none;" accept="image/*" name="profile_photo" onchange="previewImage(event)" form="edit_profile">

                                <!-- Basic Information -->
                                <div class="row mb-3">
                                    <h3 class="text">Company Information</h3>
                                    <div class="col-sm-6 col-lg-4 mb-3">
                                        <h5>Company Name:<span class="star">*</span>:</h5>
                                        <input type="text" name="company_name" class="form-control"
                                            value="<?php echo htmlspecialchars($user_data['company_name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-sm-6 col-lg-4 mb-3">
                                        <h5>Company Registration Number<span class="star">*</span>:</h5>
                                        <input type="text" name="company_reg_no" class="form-control"
                                            value="<?php echo htmlspecialchars($user_data['registration_number'] ?? ''); ?>">
                                    </div>
                                    <div class="col-sm-6 col-lg-4 mb-3">
                                        <h5>Company Website:</h5>
                                        <input type="link" name="company_website" class="form-control"
                                            value="<?php echo htmlspecialchars($user_data['company_website'] ?? ''); ?>" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <h3 class="text">Company Information</h3>
                                    <div class="col-sm-6 col-lg-6 mb-3">
                                        <h5>Recruiter Name:<span class="star">*</span>:</h5>
                                        <input type="text" name="recruiter_name" class="form-control"
                                            value="<?php echo htmlspecialchars($user_data['recruiter_name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-sm-6 col-lg-6 mb-3">
                                        <h5>Contact No.<span class="star">*</span>:</h5>
                                        <input type="text" name="recruiter_no" class="form-control"
                                            value="<?php echo htmlspecialchars($user_data['contact_no'] ?? ''); ?>">
                                    </div>
                                </div>

                                <!-- Contact Information -->
                                <div class="row mb-3">
                                    <h3 class="text">Contact Information</h3>
                                    <div class="col-sm-6 mb-3">
                                        <h5>Email<span class="star">*</span>:</h5>
                                        <p><?php echo htmlspecialchars($user_data['email'] ?? ''); ?></p>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <h5>Company Contact No.<span class="star">*</span>:</h5>
                                        <input type="text" name="contact_no" class="form-control" pattern="[0-9]{10}" title="Phone number must be 10 digits."
                                            value="<?php echo htmlspecialchars($user_data['company_contact_no'] ?? ''); ?>" required>
                                    </div>
                                </div>

                                <!-- Address -->
                                <div class="row">
                                    <h3 class="text">Address</h3>
                                    <div class="col-sm-4 mb-3">
                                        <h5>Building<span class="star">*</span>:</h5>
                                        <input type="text" name="building" class="form-control"
                                            value="<?php echo htmlspecialchars($user_data['building'] ?? ''); ?>">
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <h5>Street<span class="star">*</span>:</h5>
                                        <input type="text" name="street" class="form-control"
                                            value="<?php echo htmlspecialchars($user_data['street'] ?? ''); ?>">
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <h5>City<span class="star">*</span>:</h5>
                                        <input type="text" name="city" class="form-control"
                                            value="<?php echo htmlspecialchars($user_data['city'] ?? ''); ?>">
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <h5>State<span class="star">*</span>:</h5>
                                        <input type="text" name="state" class="form-control"
                                            value="<?php echo htmlspecialchars($user_data['state'] ?? ''); ?>">
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <h5>Country<span class="star">*</span>:</h5>
                                        <input type="text" name="country" class="form-control"
                                            value="<?php echo htmlspecialchars($user_data['country'] ?? ''); ?>">
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <h5>Pincode<span class="star">*</span>:</h5>
                                        <input type="text" name="pincode" class="form-control"
                                            value="<?php echo htmlspecialchars($user_data['pincode'] ?? ''); ?>">
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="text-start">
                                    <button class="btn btn-apply w-100" type="submit" name="ea_update_profile">Update Profile</button>
                                </div>
                            </form>
                        </div>





                        <!-- Change Password Section (Initially hidden) -->
                        <div id="changePassword" class="tab-content" style="display: none;">
                            <h3 class="text">Change Password</h3>
                            <form id="changePasswordForm" action="../Process/employer_organization_account_manage.php" method="post">
                                <div class="mb-3">
                                    <label for="currentPassword" class="form-label">Current Password<span
                                            class="star">*</span></label>
                                    <input type="password" class="form-control" id="currentPassword" minlength="8" title="Password must be at least 8 characters." name="currentPassword" required>
                                </div>
                                <div class="mb-3">
                                    <label for="newPassword" class="form-label">New Password<span class="star">*</span></label>
                                    <input type="password" class="form-control" id="newPassword" minlength="8" title="Password must be at least 8 characters." name="newPassword" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirmPassword" class="form-label">Confirm New Password<span
                                            class="star">*</span></label>
                                    <input type="password" class="form-control" id="confirmPassword" minlength="8" title="Password must be at least 8 characters." name="confirmPassword" required>
                                </div>
                                <button type="submit" class="btn btn-apply" name="ei_reset_password">Change Password</button>
                            </form>
                        </div>


                        <!-- Delete Account Section (Initially hidden) -->
                        <div id="deleteAccount" class="tab-content" style="display: none;">
                            <h3 class="text">Delete Account</h3>
                            <p>Are you sure you want to delete your account?</p>
                            <form action="../Process/employer_organization_account_manage.php" method="post">
                                <button id="confirmDelete" class="btn btn-danger" name="delete_ei_Account">Yes, Delete My
                                    Account</button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
            <script>
// Store original image URLs
const originalBannerPhoto = document.querySelector('#bannerContainer').style.backgroundImage;
const originalProfilePhoto = document.getElementById("profile_photo_display").src;

document.getElementById("editProfileBtn").addEventListener("click", function () {
    // Set the banner opacity to reduced for edit mode
    document.querySelector('.banner-container').classList.add('reduced-opacity');
    document.getElementById("changeBannerPhoto").style.display = "flex"; // Show camera icon

    // Show edit profile section and hide profile display section
    document.getElementById("profileDisplay").style.display = "none";
    document.getElementById("profileEdit").style.display = "block";

    // Hide profile photo container and show edit profile photo container
    document.getElementById("profilePhotoContainer").style.display = "none";
    document.getElementById("editProfilePhotoContainer").style.display = "block";
});

function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById("profileImagePreview").src = e.target.result;
            document.getElementById("profile_photo_display").src = e.target.result; // Corrected line
        };
        reader.readAsDataURL(file);
    }
}

document.getElementById("viewProfileBtn").addEventListener("click", function () {
    // Reset the banner opacity to full
    document.querySelector('.banner-container').classList.remove('reduced-opacity');
    document.getElementById("changeBannerPhoto").style.display = "none"; // Hide camera icon

    // Show profile display section and hide edit profile section
    document.getElementById("profileDisplay").style.display = "block";
    document.getElementById("profileEdit").style.display = "none";

    // Show profile photo container and hide edit profile photo container
    document.getElementById("profilePhotoContainer").style.display = "block";
    document.getElementById("editProfilePhotoContainer").style.display = "none";

    // Restore original images
    restoreOriginalImages();
});

// Tab functionality
const tabs = document.querySelectorAll('.list-group-item');
const tabContents = document.querySelectorAll('.tab-content');

// Function to hide all tab contents
function hideAllContents() {
    tabContents.forEach(content => {
        content.classList.remove('active');
        content.style.display = 'none';
    });
    // Default behavior: Show profile-photo-container, hide editProfilePhotoContainer
    document.getElementById("profilePhotoContainer").style.display = "block";
    document.getElementById("editProfilePhotoContainer").style.display = "none";
}

// Function to deactivate all tabs
function deactivateAllTabs() {
    tabs.forEach(tab => {
        tab.classList.remove('active');
        const icon = tab.querySelector('i');
        if (icon) {
            icon.style.color = '';
        }
    });
}

// Function to restore original images
function restoreOriginalImages() {
    document.getElementById("profile_photo_display").src = originalProfilePhoto;
    document.querySelector('#bannerContainer').style.backgroundImage = originalBannerPhoto; // Restore the original background image
}

// Set up click event for each tab
tabs.forEach(tab => {
    tab.addEventListener('click', function (e) {
        e.preventDefault();

        const targetTab = this.getAttribute('data-tab');
        if (targetTab === 'logout') {
            return; // Skip logout logic
        }

        // Hide all tab contents and deactivate all tabs
        hideAllContents();
        deactivateAllTabs();

        // Activate the clicked tab and display its content
        this.classList.add('active');
        const targetContent = document.getElementById(targetTab);
        targetContent.classList.add('active');
        targetContent.style.display = 'block';

        // Specific logic for "Edit Profile" tab
        if (targetTab === 'profileEdit') {
            document.getElementById("profilePhotoContainer").style.display = "none";
            document.getElementById("editProfilePhotoContainer").style.display = "block";

            // Set the banner opacity for edit mode
            document.querySelector('.banner-container').style.opacity = '0.5'; // Adjust this value as needed
            document.getElementById("changeBannerPhoto").style.display = "flex"; // Show camera icon
        } else {
            // Reset the banner opacity when leaving the "Edit Profile" tab
            document.querySelector('.banner-container').style.opacity = '1';
            restoreOriginalImages(); // Restore original images on tab change
        }
    });
});

// Function to preview banner image
function previewBannerImage(event) {
    const file = event.target.files[0];
    const reader = new FileReader();
    
    reader.onload = function(e) {
        const bannerContainer = document.getElementById("bannerContainer");
        bannerContainer.style.backgroundImage = `url(${e.target.result})`; // Set the background image
    }

    if (file) {
        reader.readAsDataURL(file); // Read the selected file
    }
}

// Initially activate the first tab
if (tabs.length > 0) {
    tabs[0].click();
}
</script>



            <?php
        } else {
            $_SESSION['status_title'] = "Error";
            $_SESSION['status'] = "User data not found.";
            $_SESSION['status_code'] = "error";
            header("Location: ../");
            exit();
        }
    }
} catch (Exception $e) {
    error_log("Error fetching user data: " . $e->getMessage());
    $_SESSION['status_title'] = "Error";
    $_SESSION['status'] = "An unexpected error occurred.";
    $_SESSION['status_code'] = "error";
    header("Location: ../");
    exit();
}
include '../base other/footer.php';
?>
