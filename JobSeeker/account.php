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
                         js.first_name, js.middle_name, js.last_name, 
                         js.service_type, js.education, js.date_of_birth, 
                         js.experience, js.rating, js.bio, js.gender, js.resume
                  FROM users u 
                  JOIN job_seekers js ON u.user_id = js.user_id
                  WHERE u.user_id = ? AND u.user_type = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $user_id, $user_type);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user_data = $result->fetch_assoc();
            ?>
            <div class="container mt-5 mb-5">
                <h2 class="text">Account</h2>
                <div class="row bg-white p-4 rounded shadow m-0">
                    <div class="col-md-3 border-end pe-4 mb-4 mb-md-0" style="border-right: 2px solid #007bff;">
                        <div class="text-center">
                            <div class="rounded-circle bg-secondary text-white d-inline-flex justify-content-center align-items-center"
                                style="width: 150px; height: 150px; overflow: hidden;">
                                <img src="../images/profile/<?php echo htmlspecialchars($user_data['profile_photo'] ?? 'default profile photo.png'); ?>"
                                    alt="Profile Photo" style="border-radius: 50%; height: 200px; width: 200px;" loading="lazy"
                                    id="profile_photo_display">
                            </div>
                            <h4 class="mt-3 text-primary">
                                <?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?></h4>
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
                                <div class="col-sm-6 col-lg-4 mb-3">
                                    <h5>Gender:</h5>
                                    <p id="genderView"><?php echo htmlspecialchars($user_data['gender'] ?? '-'); ?></p>
                                </div>
                                <div class="col-sm-6 col-lg-4 mb-3">
                                    <h5>Date of Birth:</h5>
                                    <p id="dobView"><?php echo htmlspecialchars($user_data['date_of_birth'] ?? '-'); ?></p>
                                </div>
                                <div class="col-sm-6 col-lg-4 mb-3">
                                    <h5>Bio:</h5>
                                    <p id="bioView"><?php echo htmlspecialchars($user_data['bio'] ?? '-'); ?></p>
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
                                <h3 class="text">Other Information</h3>
                                <div class="col-sm-6 mb-3">
                                    <h5>Service Type:</h5>
                                    <p id="serviceTypeView"><?php echo htmlspecialchars($user_data['service_type'] ?? '-'); ?></p>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <h5>Education:</h5>
                                    <p id="educationView"><?php echo htmlspecialchars($user_data['education'] ?? '-'); ?></p>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <h5>Experience:</h5>
                                    <p id="experienceView"><?php echo htmlspecialchars($user_data['experience'] ?? '-'); ?></p>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <h5>Resume:</h5>
                                    <p id="resumeView">
                                        <?php if (!empty($user_data['resume'])): ?>
                                            <a href="../uploads/resumes/<?php echo htmlspecialchars($user_data['resume']); ?>">Download
                                                Resume</a>
                                        <?php else: ?>
                                            <a href="#" class="disabled" onclick="return false;"
                                                style="color: gray; text-decoration: none;">No Resume Available</a>
                                        <?php endif; ?>
                                    </p>
                                </div>

                            </div>
                        </div>

                        <!-- Edit Profile Form Section (Initially hidden) -->
                        <div id="profileEdit" class="tab-content" style="display: none;">
                            <h3 class="text">Edit Profile</h3>
                            <form action="../Process/js_account_manage.php" method="post" enctype="multipart/form-data">
                                <div class="col-sm-6 col-lg-12 mb-3">
                                    <h5>Profile Image:</h5>
                                    <div class="rounded-circle bg-secondary text-white d-inline-flex justify-content-center align-items-center position-relative"
                                        style="width: 150px; height: 150px; overflow: hidden;">
                                        <img src="../images/profile/<?php echo htmlspecialchars($user_data['profile_photo'] ?? 'default profile photo.png'); ?>"
                                            alt="Profile Photo" style="border-radius: 50%; height: 100%; width: 100%; opacity: 0.1;"
                                            loading="lazy" id="profileImagePreview">
                                        <input type="file" id="profileImageInput" style="display: none;" accept="image/*"
                                            onchange="previewImage(event)" name="profile_photo">
                                        <i class="fas fa-camera position-absolute"
                                            style="color: white; font-size: 40px; text-shadow: 0 0 10px rgba(255, 255, 255, 0.8);"
                                            onclick="document.getElementById('profileImageInput').click();"></i>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-6 col-lg-4 mb-3">
                                        <h5>First Name<span class="star">*</span> :</h5>
                                        <input type="text" id="firstNameInput"
                                            value="<?php echo htmlspecialchars($user_data['first_name'] ?? ''); ?>"
                                            class="form-control"  name="first_name" required />
                                    </div>
                                    <div class="col-sm-6 col-lg-4 mb-3">
                                        <h5>Middle Name<span class="star">*</span> :</h5>
                                        <input type="text" id="middleNameInput"
                                            value="<?php echo htmlspecialchars($user_data['middle_name'] ?? ''); ?>"
                                            class="form-control" name="middle_name" required />
                                    </div>
                                    <div class="col-sm-6 col-lg-4 mb-3">
                                        <h5>Last Name<span class="star">*</span> :</h5>
                                        <input type="text" id="lastNameInput" name="last_name"
                                            value="<?php echo htmlspecialchars($user_data['last_name'] ?? ''); ?>"
                                            class="form-control" required />
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-6 col-lg-4 mb-3">
                                        <h5>Gender<span class="star">*</span> :</h5>
                                        <select class="form-select" id="gender" name="gender" required>
                                            <option value="" disabled>Select gender</option>
                                            <option value="Male" <?php echo ($user_data['gender'] === 'Male') ? 'selected' : ''; ?>>
                                                Male</option>
                                            <option value="Female" <?php echo ($user_data['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                                            <option value="Other" <?php echo ($user_data['gender'] === 'Other') ? 'selected' : ''; ?>>
                                                Other</option>
                                        </select>

                                    </div>
                                    <div class="col-sm-6 col-lg-4 mb-3">
                                        <h5>Date of Birth<span class="star">*</span> :</h5>
                                        <input type="date" id="dateOfBirth" name="date_of_birth"
                                            value="<?php echo htmlspecialchars($user_data['date_of_birth'] ?? ''); ?>"
                                            class="form-control" required />
                                        <span id="dobError" style="color: red; font-size: 0.9em;"></span>
                                    </div>

                                    <div class="col-sm-6 col-lg-4 mb-3">
                                        <h5>Bio:</h5>
                                        <textarea id="bioInput" name="bio"
                                            class="form-control"><?php echo htmlspecialchars($user_data['bio'] ?? ''); ?></textarea>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <h3 class="text">Contact Information</h3>
                                    <div class="col-sm-6 mb-3">
                                        <h5>Email<span class="star">*</span> :</h5>
                                        <p><?php echo htmlspecialchars($user_data['email'] ?? '-'); ?></p>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <h5>Contact No<span class="star">*</span> :</h5>
                                        <input type="text" id="contactNoInput" name="contact_no"
                                            value="<?php echo htmlspecialchars($user_data['contact_no'] ?? ''); ?>"
                                            class="form-control" pattern="[0-9]{10}" title="Phone number must be 10 digits." required />
                                    </div>
                                </div>

                                <div class="row">
                                    <h3 class="text">Other Information</h3>
                                    <div class="col-sm-6 mb-3">
                                        <h5>Service/Skill Type<span class="star">*</span> :</h5>
                                        <select class="form-select" id="serviceType" name="service_type" required>
                                            <option value="" disabled>Select service type</option>
                                            <option value="Law" <?php echo ($user_data['service_type'] === 'Law') ? 'selected' : ''; ?>>Law</option>
                                            <option value="Health" <?php echo ($user_data['service_type'] === 'Health') ? 'selected' : ''; ?>>Health</option>
                                            <option value="Medical" <?php echo ($user_data['service_type'] === 'Medical') ? 'selected' : ''; ?>>Medical</option>
                                            <option value="Marketing" <?php echo ($user_data['service_type'] === 'Marketing') ? 'selected' : ''; ?>>Marketing</option>
                                            <option value="Real Estate" <?php echo ($user_data['service_type'] === 'Real Estate') ? 'selected' : ''; ?>>Real Estate</option>
                                            <option value="Agriculture" <?php echo ($user_data['service_type'] === 'Agriculture') ? 'selected' : ''; ?>>Agriculture</option>
                                            <option value="Consultants" <?php echo ($user_data['service_type'] === 'Consultants') ? 'selected' : ''; ?>>Consultants</option>
                                            <option value="Designing" <?php echo ($user_data['service_type'] === 'Designing') ? 'selected' : ''; ?>>Designing</option>
                                            <option value="Services" <?php echo ($user_data['service_type'] === 'Services') ? 'selected' : ''; ?>>Services</option>
                                            <option value="Engineering" <?php echo ($user_data['service_type'] === 'Engineering') ? 'selected' : ''; ?>>Engineering</option>
                                            <option value="Call Center" <?php echo ($user_data['service_type'] === 'Call Center') ? 'selected' : ''; ?>>Call Center</option>
                                            <option value="E-Commerce" <?php echo ($user_data['service_type'] === 'E-Commerce') ? 'selected' : ''; ?>>E-Commerce</option>
                                            <option value="Transport" <?php echo ($user_data['service_type'] === 'Transport') ? 'selected' : ''; ?>>Transport</option>
                                            <option value="Programming" <?php echo ($user_data['service_type'] === 'Programming') ? 'selected' : ''; ?>>Programming</option>
                                            <option value="Banking/Finance" <?php echo ($user_data['service_type'] === 'Banking/Finance') ? 'selected' : ''; ?>>
                                                Banking/Finance</option>
                                            <option value="Other" <?php echo ($user_data['service_type'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                                        </select>

                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <h5>Education<span class="star">*</span> :</h5>
                                        <select class="form-select" id="education" name="education" required>
                                            <option value="" disabled>Select education level</option>
                                            <option value="Below 10th" <?php echo ($user_data['education'] === 'Below 10th') ? 'selected' : ''; ?>>Below 10th</option>
                                            <option value="10th Pass" <?php echo ($user_data['education'] === '10th Pass') ? 'selected' : ''; ?>>10th Pass</option>
                                            <option value="12th Pass" <?php echo ($user_data['education'] === '12th Pass') ? 'selected' : ''; ?>>12th Pass</option>
                                            <option value="Diploma" <?php echo ($user_data['education'] === 'Diploma') ? 'selected' : ''; ?>>Diploma</option>
                                            <option value="Graduate" <?php echo ($user_data['education'] === 'Graduate') ? 'selected' : ''; ?>>Graduate</option>
                                            <option value="Postgraduate" <?php echo ($user_data['education'] === 'Postgraduate') ? 'selected' : ''; ?>>Postgraduate</option>
                                        </select>

                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <h5>Experience<span class="star">*</span> :</h5>
                                        <select class="form-select" id="experience" name="experience" required>
                                            <option value="" disabled>Select years of experience</option>
                                            <option value="0-1" <?php echo ($user_data['experience'] === '0-1') ? 'selected' : ''; ?>>0-1 years</option>
                                            <option value="1-3" <?php echo ($user_data['experience'] === '1-3') ? 'selected' : ''; ?>>1-3 years</option>
                                            <option value="3-5" <?php echo ($user_data['experience'] === '3-5') ? 'selected' : ''; ?>>3-5 years</option>
                                            <option value="5+" <?php echo ($user_data['experience'] === '5+') ? 'selected' : ''; ?>>5+ years</option>
                                        </select>

                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <h5>Resume<small style="color:#7a7a7a"> (PDF only)</small>:</h5>
                                        <input type="file" id="resumeInput" class="form-control" name="resume"/>
                                        <?php if (!empty($user_data['resume'])): ?>
                                            <small>Current file: <?php echo htmlspecialchars($user_data['resume']); ?></small>
                                        <?php endif; ?>

                                    </div>
                                </div>

                                <div class="text-start">
                                    <button class="btn btn-apply w-100" id="saveProfileBtn" style="" name="js_update_profile">Update Profile</button>
                                </div>
                            </form>
                        </div>

                        <!-- Change Password Section (Initially hidden) -->
                        <div id="changePassword" class="tab-content" style="display: none;">
                            <h3 class="text">Change Password</h3>
                            <form id="changePasswordForm" action="../Process/js_account_manage.php" method="post">
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
                                <button type="submit" class="btn btn-apply" name="js_reset_password">Change Password</button>
                            </form>
                        </div>

                        

                        <!-- Delete Account Section (Initially hidden) -->
                        <div id="deleteAccount" class="tab-content" style="display: none;">
                            <h3 class="text">Delete Account</h3>
                            <p>Are you sure you want to delete your account?</p>
                            <form action="../Process/js_account_manage.php" method="post">
                                <button id="confirmDelete" class="btn btn-danger" name="delete_js_Account">Yes, Delete My
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
        content.style.display = 'none';
    });
}

// Function to remove active class from all tabs
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
        const targetTab = this.getAttribute('data-tab');

        // Allow the normal link behavior for the logout link
        if (targetTab === 'logout') {
            return; // This lets the link function normally
        }

        e.preventDefault(); // Prevent default behavior for other tabs

        // Hide all tab contents and deactivate all tabs
        hideAllContents();
        deactivateAllTabs();

        // Show the clicked tab's content and activate the tab
        this.classList.add('active');
        document.getElementById(targetTab).style.display = 'block';

        // Change icon color of the active tab
        const activeIcon = this.querySelector('i');
        if (activeIcon) {
            activeIcon.style.color = 'white'; // Change color of the active icon
        }

        if (targetTab !== 'profileEdit') {
            // Set the base path for profile photos
            const basePath = '../images/profile/';

            // Set the profile photo source
            document.getElementById('profile_photo_display').src = basePath + '<?php echo htmlspecialchars($user_data['profile_photo'] ?? 'default profile photo.png'); ?>';
            document.getElementById('profileImageInput').value = '';
        }
    });
});

// Initially show the first tab's content and mark it active
if (tabs.length > 0) {
    tabs[0].click(); // Programmatically click the first tab to show its content
}
</script>

            <?php

        } else {
            // If no user data is found, handle it
            $_SESSION['status_title'] = "Error";
            $_SESSION['status'] = "User data not found.";
            $_SESSION['status_code'] = "error";
            header("Location: ../");
            exit();
        }
    }
} catch (Exception $e) {
    // Log or display the error for debugging
    error_log("Error fetching user data: " . $e->getMessage());
    $_SESSION['status_title'] = "Error";
    $_SESSION['status'] = "An unexpected error occurred.";
    $_SESSION['status_code'] = "error";
    header("Location: ../");
    exit();
}
include '../base other/footer.php';
?>