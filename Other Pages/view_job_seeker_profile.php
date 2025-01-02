<?php
ob_start(); // Ensure the session is started
include '../base other/header.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    $_SESSION['status_title'] = "😔 Sorry 😔";
    $_SESSION['status'] = "You must log in to access this page.";
    $_SESSION['status_code'] = "error";
    header("Location: http://localhost/Job%20Point/");
    exit;
}

include '../DB Connection/config.php';

// Retrieve seeker ID from the GET request
$seeker_id = $_GET['seeker_id'] ?? null;

if ($seeker_id === null) {
    $_SESSION['status_title'] = "⚠️ Error ⚠️";
    $_SESSION['status'] = "Invalid request. Seeker ID is missing.";
    $_SESSION['status_code'] = "error";
    header("Location: http://localhost/Job%20Point/");
    exit;
}

// Fetch user data based on seeker ID
$query = "
    SELECT 
        js.seeker_id, u.profile_photo, js.first_name, js.middle_name, js.last_name, js.service_type, 
        js.education, js.date_of_birth, js.experience, js.bio, js.gender, 
        js.resume, u.email, u.contact_no 
    FROM 
        job_seekers AS js
    JOIN 
        users AS u ON js.user_id = u.user_id
    WHERE 
        js.seeker_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $seeker_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// Check if the user data was found
if (!$user_data) {
    $_SESSION['status_title'] = "⚠️ Error ⚠️";
    $_SESSION['status'] = "No profile found for this seeker ID.";
    $_SESSION['status_code'] = "error";
    header("Location: http://localhost/Job%20Point/");
    exit;
}
?>
<style>
    .profile-photo-container {
                    position: absolute;
                    top: 15vh;
                    text-align: center;
                }

</style>
<div class="container mt-5 mb-5">
    <h2 class="text">Profile Information</h2>
    <div class="row bg-white p-4 rounded shadow m-0" style="border: 4px solid #94E3C4;">
    <!-- <div class="profile-photo-container" id="profilePhotoContainer" style="width: 150px; height: 150px;overflow:hidden; ">
        <img src="../images/profile/<?php echo htmlspecialchars($user_data['profile_photo'] ?? 'default profile photo.png'); ?>" 
            alt="Profile Photo" id="profile_photo_display" loading="lazy">
    </div> -->
    <div class=" profile-photo-container rounded-circle bg-secondary text-white d-inline-flex justify-content-center align-items-center"
        style="width: 150px; height: 150px; overflow: hidden;">
        <img src="../images/profile/<?php echo htmlspecialchars($user_data['profile_photo']); ?>"
            alt="Profile Photo" style="border-radius: 50%; height: 200px; width: 150px; loading="lazy"
            id="profile_photo_display">
    </div>
        <div id="profileDisplay" class="tab-content" style="display: block;">
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
                            <a href="../uploads/resumes/<?php echo htmlspecialchars($user_data['resume']); ?>">Download Resume</a>
                        <?php else: ?>
                            <a href="#" class="disabled" onclick="return false;" style="color: gray; text-decoration: none;">No Resume Available</a>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include '../base other/footer.php';
?>
