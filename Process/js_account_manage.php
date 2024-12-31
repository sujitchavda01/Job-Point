<?php
session_start();
require_once '../DB Connection/config.php';

try {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
        $_SESSION['status_title'] = "😏 Sorry 😏";
        $_SESSION['status'] = "Unauthorized Access Attempt Detected";
        $_SESSION['status_code'] = "error";
        header("Location: ../");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Update Profile
        if (isset($_POST['js_update_profile'])){
            $query = "SELECT * FROM job_seekers JOIN users ON job_seekers.user_id = users.user_id WHERE users.user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $existing_data = $result->fetch_assoc();

            if (!$existing_data) {
                throw new Exception("User not found.");
            }

            // Prepare data for update
            $first_name = $_POST['first_name'] ?? $existing_data['first_name'];
            $middle_name = $_POST['middle_name'] ?? $existing_data['middle_name'];
            $last_name = $_POST['last_name'] ?? $existing_data['last_name'];
            $gender = $_POST['gender'] ?? $existing_data['gender'];
            $date_of_birth = $_POST['date_of_birth'] ?? $existing_data['date_of_birth'];
            $bio = $_POST['bio'] ?? $existing_data['bio'];
            $contact_no = $_POST['contact_no'] ?? $existing_data['contact_no'];
            $service_type = $_POST['service_type'] ?? $existing_data['service_type'];
            $education = $_POST['education'] ?? $existing_data['education'];
            $experience = $_POST['experience'] ?? $existing_data['experience'];

            if (!empty($date_of_birth)) {
                // Calculate the date difference
                $dob = new DateTime($date_of_birth);
                $today = new DateTime();
                $age = $today->diff($dob)->y; // Get age in years
            
                // Check if the user is at least 18 years old
                if ($age < 18) {
                    throw new Exception("You must be at least 18 years old.");
                }
            }
            
            // Initialize profile photo and resume variables
            $profile_photo = $existing_data['profile_photo']; // Default to existing photo
            $resume = $existing_data['resume']; // Default to existing resume

            // Process profile photo upload
            $new_profile_photo = null; // Variable to hold the new photo name
            $old_photo_path = "../images/profile/" . $profile_photo; // Path for old photo

            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                $TargetDir = "../images/profile/";

                $ImageFileType = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
                $UniqueName = uniqid('profile_', true) . '.' . $ImageFileType;
                $TargetFile = $TargetDir . $UniqueName;
                $fileSize = $_FILES['profile_photo']['size'];

                // Validate file size and format
                if ($fileSize > (5 * 1024 * 1024)) { // 5MB limit
                    throw new Exception("File is too large. Maximum size is 5MB.");
                }

                if (!in_array($ImageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                    throw new Exception("Only JPG, JPEG, PNG & GIF files are allowed.");
                }

                if (!getimagesize($_FILES['profile_photo']['tmp_name'])) {
                    throw new Exception("File is not a valid image.");
                }

                // Check if directory exists
                if (!is_dir($TargetDir)) {
                    if (!mkdir($TargetDir, 0777, true)) {
                        throw new Exception("Failed to create directory: $TargetDir");
                    }
                }

                // Move uploaded file
                if (!move_uploaded_file($_FILES['profile_photo']['tmp_name'], $TargetFile)) {
                    error_log("Failed to move uploaded file.");
                    throw new Exception("Error uploading profile photo.");
                }

                // Save only the unique name of the image
                $new_profile_photo = $UniqueName; // Update to new photo name
            } else {
                // If no new photo is uploaded, keep the old photo name
                $new_profile_photo = $profile_photo;
            }

            // Process resume upload
            $new_resume = null; // Variable to hold the new resume name
            $old_resume_path = "../uploads/resumes/" . $resume; // Path for old resume

            if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
                $ResumeTargetDir = "../uploads/resumes/";

                $ResumeFileType = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
                $UniqueResumeName = uniqid('resume_', true) . '.' . $ResumeFileType;
                $ResumeTargetFile = $ResumeTargetDir . $UniqueResumeName;
                $resumeFileSize = $_FILES['resume']['size'];

                // Validate file size and format
                if ($resumeFileSize > (10 * 1024 * 1024)) { // 10MB limit
                    throw new Exception("Resume file is too large. Maximum size is 10MB.");
                }

                if ($ResumeFileType !== 'pdf') {
                    throw new Exception("Only PDF files are allowed for the resume.");
                }

                // Check if directory exists
                if (!is_dir($ResumeTargetDir)) {
                    if (!mkdir($ResumeTargetDir, 0777, true)) {
                        throw new Exception("Failed to create directory: $ResumeTargetDir");
                    }
                }

                // Move uploaded resume file
                if (!move_uploaded_file($_FILES['resume']['tmp_name'], $ResumeTargetFile)) {
                    error_log("Failed to move uploaded resume file.");
                    throw new Exception("Error uploading resume.");
                }

                // Save only the unique name of the resume
                $new_resume = $UniqueResumeName; // Update to new resume name
            } else {
                // If no new resume is uploaded, keep the old resume name
                $new_resume = $resume;
            }

            // Begin transaction
            $conn->autocommit(false);

            // Update users table
            $update_users = "UPDATE users SET contact_no = ?, profile_photo = ? WHERE user_id = ?";
            $stmt_users = $conn->prepare($update_users);
            $stmt_users->bind_param("ssi", $contact_no, $new_profile_photo, $user_id);
            if (!$stmt_users->execute()) {
                throw new Exception("Error updating users: " . $stmt_users->error);
            }

            // Update job_seekers table
            $update_seekers = "UPDATE job_seekers 
                SET first_name = ?, middle_name = ?, last_name = ?, gender = ?, date_of_birth = ?, bio = ?, service_type = ?, education = ?, experience = ?,resume=?
                WHERE user_id = ?";
            $stmt_seekers = $conn->prepare($update_seekers);
            $stmt_seekers->bind_param(
                "ssssssssssi",
                $first_name,
                $middle_name,
                $last_name,
                $gender,
                $date_of_birth,
                $bio,
                $service_type,
                $education,
                $experience,
                $new_resume,
                $user_id
            );
            if (!$stmt_seekers->execute()) {
                throw new Exception("Error updating job_seekers: " . $stmt_seekers->error);
            }

            // Commit transaction
            $conn->commit();
            $conn->autocommit(true);

            // Delete old photo if a new one was uploaded
            if ($new_profile_photo !== $profile_photo && file_exists($old_photo_path)) {
                unlink($old_photo_path); // Delete old photo
            }

            // Delete old resume if a new one was uploaded
            if ($new_resume !== $resume && file_exists($old_resume_path)) {
                unlink($old_resume_path); // Delete old resume
            }

            $_SESSION['status_title'] = "✅ Success ✅";
            $_SESSION['status'] = "Profile updated successfully.";
            $_SESSION['status_code'] = "success";
            header("Location: ../JobSeeker/account.php");
        }
        
        // Change Password
        if (isset($_POST['js_reset_password'])) {
            // Fetch user ID
            $user_id = $_SESSION['user_id'];
        
            // Get current password and new password inputs
            $current_password = $_POST['currentPassword'];
            $new_password = $_POST['newPassword'];
            $confirm_password = $_POST['confirmPassword'];
        
            // Fetch current password from the database
            $query = "SELECT password FROM users WHERE user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_data = $result->fetch_assoc();
        
            if (!$user_data) {
                throw new Exception("User not found.");
            }
        
            // Verify current password
            if (!password_verify($current_password, $user_data['password'])) {
                throw new Exception("Current password is incorrect.");
            }
        
            // Check if new password and confirm password match
            if ($new_password !== $confirm_password) {
                throw new Exception("New password and confirm password do not match.");
            }
        
            // Sanitize and hash the new password
            $hashed_new_password = password_hash($conn->real_escape_string($new_password), PASSWORD_BCRYPT);
        
            // Update the password in the database
            $update_password_query = "UPDATE users SET password = ? WHERE user_id = ?";
            $stmt_update = $conn->prepare($update_password_query);
            $stmt_update->bind_param("si", $hashed_new_password, $user_id);
        
            if (!$stmt_update->execute()) {
                throw new Exception("Error updating password: " . $stmt_update->error);
            }
        
            // Set success message
            $_SESSION['status_title'] = "✅ Success ✅";
            $_SESSION['status'] = "Password changed successfully.";
            $_SESSION['status_code'] = "success";
            header("Location: ../JobSeeker/account.php");
            exit();
        }


        // Delete Account
        if (isset($_POST["delete_js_Account"])) {
            $user_id = $_SESSION['user_id']; // Get the user ID from session
            $current_time = date('Y-m-d H:i:s'); // Get the current date and time
        
            // Begin transaction
            $conn->autocommit(false);
            
            try {
                // Step 1: Mark the account for deletion by setting the deleted_at timestamp
                $update_query = "UPDATE users SET deleted_at = ? WHERE user_id = ?";
                $stmt_users = $conn->prepare($update_query);
                $stmt_users->bind_param("si", $current_time, $user_id);
        
                if (!$stmt_users->execute()) {
                    throw new Exception("Error marking account for deletion in users table: " . $stmt_users->error);
                }
        
                // Commit transaction
                $conn->commit();
                
                // Set success message
                $_SESSION['status_title'] = "✅ Success ✅";
                $_SESSION['status'] = "Your account will be deleted after 15 days.";
                $_SESSION['status_code'] = "success";
                header("Location: ../JobSeeker/account.php");
                exit();
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                
                // Set error message
                $_SESSION['status_title'] = "❌ Error ❌";
                $_SESSION['status'] = $e->getMessage();
                $_SESSION['status_code'] = "error";
                header("Location: ../JobSeeker/account.php");
                exit();
            } finally {
                // Enable autocommit again
                $conn->autocommit(true);
            }
        }
        
        
        
    } else {
        throw new Exception("Invalid request method.");
    }

} catch (Exception $e) {
    if ($conn->errno) {
        $conn->rollback();
        $conn->autocommit(true);
    }
    $_SESSION['status_title'] = "❌ Error ❌";
    $_SESSION['status'] = $e->getMessage();
    $_SESSION['status_code'] = "error";

    

    header("Location: ../JobSeeker/account.php");
    exit();
}
?>
