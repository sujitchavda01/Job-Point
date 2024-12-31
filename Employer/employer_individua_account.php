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
                         ei.first_name, ei.middle_name, ei.last_name, 
                         a.building, a.street, a.city, a.state, a.country, a.pincode
                  FROM users u 
                  JOIN employers_individual ei ON u.user_id = ei.user_id
                  JOIN address a ON ei.address_id = a.address_id
                  WHERE u.user_id = ? AND u.user_type = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $user_id, $user_type);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user_data = $result->fetch_assoc();
            ?>
            <div class="container mt-5 mb-5">
                <h2 class="text">Employer Account</h2>
                <div class="row bg-white p-4 rounded shadow m-0">
                    <div class="col-md-3 border-end pe-4 mb-4 mb-md-0" style="border-right: 2px solid #007bff;">
                        <div class="text-center">
                            <div class="rounded-circle bg-secondary text-white d-inline-flex justify-content-center align-items-center"
                                style="width: 150px; height: 150px; overflow: hidden;">
                                <img src="../images/profile/<?php echo htmlspecialchars($user_data['profile_photo'] ?? 'default profile photo.png'); ?>"
                                    alt="Profile Photo" style="border-radius: 50%; height: 200px; width: 200px;scale:0.8;" loading="lazy"
                                    id="profile_photo_display">
                            </div>
                            <h4 class="mt-3 text-primary">
                                <?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?>
                            </h4>
                        </div>
                        <div class="list-group mt-4">
                            <a href="#" class="list-group-item list-group-item-action active" data-tab="profileDisplay"
                                id="viewProfileBtn">
                                <i class="fas fa-user-circle me-2"></i> View Profile
                            </a>
                            <a href="#" class="list-group-item list-group-item-action" data-tab="profileEdit" id="editProfileBtn">
                                <i class="fas fa-cogs me-2" style="color: #6c757d;"></i> Edit Profile
                            </a>
                            <a href="#" class="list-group-item list-group-item-action" data-tab="changePassword">
                                <i class="fas fa-key me-2" style="color: #6c757d;"></i> Change Password
                            </a>
                            <a href="#" class="list-group-item list-group-item-action" data-tab="deleteAccount">
                                <i class="fas fa-trash-alt me-2" style="color: #dc3545;"></i> Delete Account
                            </a>
                            <a href="../Process/logout.php" class="list-group-item list-group-item-action" data-tab="logout" style="color:rgb(231, 62, 62);">
                                <i class="fa fa-sign-out me-2" aria-hidden="true"></i> Logout
                            </a>

                        </div>
                    </div>

                    <!-- Tab Content -->
                    <div class="col-md-9 ps-md-4">
                        <!-- View Profile Tab -->
                        <div id="profileDisplay" class="tab-content active" style="display: block;">
                            <h3 class="text">Basic Information</h3>
                            <div class="row mb-3">
                                <div class="col-sm-6 col-lg-4 mb-3">
                                    <h5>First Name:</h5>
                                    <p id="firstNameView"><?php echo htmlspecialchars($user_data['first_name'] ?? '-'); ?></p>
                                </div>
                                <div class="col-sm-6 col-lg-4 mb-3">
                                    <h5>Middle Name:</h5>
                                    <p id="middleNameView"><?php echo htmlspecialchars($user_data['middle_name'] ?? '-'); ?></p>
                                </div>
                                <div class="col-sm-6 col-lg-4 mb-3">
                                    <h5>Last Name:</h5>
                                    <p id="lastNameView"><?php echo htmlspecialchars($user_data['last_name'] ?? '-'); ?></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <h3 class="text">Contact Information</h3>
                                <div class="col-sm-6 mb-3">
                                    <h5>Email:</h5>
                                    <p><?php echo htmlspecialchars($user_data['email'] ?? '-'); ?></p>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <h5>Contact No:</h5>
                                    <p id="contactNoView"><?php echo htmlspecialchars($user_data['contact_no'] ?? '-'); ?></p>
                                </div>
                            </div>

                            <div class="row">
                                <h3 class="text">Address</h3>
                                <div class="col-sm-4 mb-3">
                                    <h5>Building:</h5>
                                    <p id="buildingView"><?php echo htmlspecialchars($user_data['building'] ?? '-'); ?></p>
                                </div>
                                <div class="col-sm-4 mb-3">
                                    <h5>Street:</h5>
                                    <p id="streetView"><?php echo htmlspecialchars($user_data['street'] ?? '-'); ?></p>
                                </div>
                                <div class="col-sm-4 mb-3">
                                    <h5>City:</h5>
                                    <p id="cityView"><?php echo htmlspecialchars($user_data['city'] ?? '-'); ?></p>
                                </div>
                                <div class="col-sm-4 mb-3">
                                    <h5>State:</h5>
                                    <p id="stateView"><?php echo htmlspecialchars($user_data['state'] ?? '-'); ?></p>
                                </div>
                                <div class="col-sm-4 mb-3">
                                    <h5>Country:</h5>
                                    <p id="countryView"><?php echo htmlspecialchars($user_data['country'] ?? '-'); ?></p>
                                </div>
                                <div class="col-sm-4 mb-3">
                                    <h5>Pincode:</h5>
                                    <p id="pincodeView"><?php echo htmlspecialchars($user_data['pincode'] ?? '-'); ?></p>
                                </div>
                            </div>
                        </div>
                    

                        <!-- edit profile -->
                        <div id="profileEdit" class="tab-content" style="display: none;">
                            <h3 class="text">Edit Profile</h3>
                            <form action="../Process/employer_individua_account_manage.php" method="post" enctype="multipart/form-data">
                                <!-- Profile Image -->
                                <div class="col-sm-6 col-lg-12 mb-3">
                                    <h5>Profile Image:</h5>
                                    <div class="rounded-circle bg-secondary text-white d-inline-flex justify-content-center align-items-center position-relative"
                                        style="width: 150px; height: 150px; overflow: hidden;">
                                        <img src="../images/profile/<?php echo htmlspecialchars($user_data['profile_photo'] ?? 'default_profile_photo.png'); ?>"
                                            alt="Profile Photo" style="border-radius: 50%; height: 100%; width: 100%;opacity: 0.1;"
                                            id="profileImagePreview">
                                        <input type="file" id="profileImageInput" style="display: none;" accept="image/*"
                                            name="profile_photo" onchange="previewImage(event)">
                                        <i class="fas fa-camera position-absolute" style="color: white; font-size: 40px;"
                                            onclick="document.getElementById('profileImageInput').click();"></i>
                                    </div>
                                </div>

                                <!-- Basic Information -->
                                <div class="row mb-3">
                                    <h3 class="text">Basic Information</h3>
                                    <div class="col-sm-6 col-lg-4 mb-3">
                                        <h5>First Name<span class="star">*</span>:</h5>
                                        <input type="text" name="first_name" class="form-control"
                                            value="<?php echo htmlspecialchars($user_data['first_name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-sm-6 col-lg-4 mb-3">
                                        <h5>Middle Name:</h5>
                                        <input type="text" name="middle_name" class="form-control"
                                            value="<?php echo htmlspecialchars($user_data['middle_name'] ?? ''); ?>">
                                    </div>
                                    <div class="col-sm-6 col-lg-4 mb-3">
                                        <h5>Last Name<span class="star">*</span>:</h5>
                                        <input type="text" name="last_name" class="form-control"
                                            value="<?php echo htmlspecialchars($user_data['last_name'] ?? ''); ?>" required>
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
                                        <h5>Contact No<span class="star">*</span>:</h5>
                                        <input type="text" name="contact_no" class="form-control" pattern="[0-9]{10}" title="Phone number must be 10 digits."
                                            value="<?php echo htmlspecialchars($user_data['contact_no'] ?? ''); ?>" required>
                                    </div>
                                </div>

                                <!-- Address -->
                                <div class="row">
                                    <h3 class="text">Address</h3>
                                    <div class="col-sm-4 mb-3">
                                        <h5>Building:</h5>
                                        <input type="text" name="building" class="form-control"
                                            value="<?php echo htmlspecialchars($user_data['building'] ?? ''); ?>">
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <h5>Street:</h5>
                                        <input type="text" name="street" class="form-control"
                                            value="<?php echo htmlspecialchars($user_data['street'] ?? ''); ?>">
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <h5>City:</h5>
                                        <input type="text" name="city" class="form-control"
                                            value="<?php echo htmlspecialchars($user_data['city'] ?? ''); ?>">
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <h5>State:</h5>
                                        <input type="text" name="state" class="form-control"
                                            value="<?php echo htmlspecialchars($user_data['state'] ?? ''); ?>">
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <h5>Country:</h5>
                                        <input type="text" name="country" class="form-control"
                                            value="<?php echo htmlspecialchars($user_data['country'] ?? ''); ?>">
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <h5>Pincode:</h5>
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
                            <form id="changePasswordForm" action="../Process/employer_individua_account_manage.php" method="post">
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
                            <form action="../Process/employer_individua_account_manage.php" method="post">
                                <button id="confirmDelete" class="btn btn-danger" name="delete_ei_Account">Yes, Delete My
                                    Account</button>
                            </form>
                        </div>




                    </div>
                </div>
            </div>
            <script>
                   document.getElementById("viewProfileBtn").addEventListener("click", function () {
    document.getElementById("profileDisplay").style.display = "block";
    document.getElementById("profileEdit").style.display = "none";
});

document.getElementById("editProfileBtn").addEventListener("click", function () {
    document.getElementById("profileDisplay").style.display = "none";
    document.getElementById("profileEdit").style.display = "block";
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
                // Tab functionality
const tabs = document.querySelectorAll('.list-group-item');
const tabContents = document.querySelectorAll('.tab-content');

// Function to hide all tab contents
function hideAllContents() {
    tabContents.forEach(content => {
        content.classList.remove('active'); // Remove the active class
        content.style.display = 'none'; // Hide the content
    });
}

// Function to remove the active class from all tabs
function deactivateAllTabs() {
    tabs.forEach(tab => {
        tab.classList.remove('active');
        const icon = tab.querySelector('i');
        if (icon) {
            icon.style.color = ''; // Change back to default color
        }
    });
}

// Set up click event for each tab
tabs.forEach(tab => {
    tab.addEventListener('click', function (e) {
        e.preventDefault();

        const targetTab = this.getAttribute('data-tab');
        if (targetTab === 'logout') {
            return; // Allow logout to work normally
        }

        // Hide all tab contents and deactivate all tabs
        hideAllContents();
        deactivateAllTabs();

        // Activate the clicked tab and display its content
        this.classList.add('active');
        const targetContent = document.getElementById(targetTab);
        targetContent.classList.add('active');
        targetContent.style.display = 'block'; // Show the content
    });
});

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