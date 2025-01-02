<?php
session_start();
require_once '../DB Connection/config.php';

try {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
        $_SESSION['status_title'] = "😾 Sorry 😾";
        $_SESSION['status'] = "Unauthorized Access Attempt Detected";
        $_SESSION['status_code'] = "error";
        header("Location: ../");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    // Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ea_update_profile'])) {
    // Fetch existing user data
    $query = "SELECT u.*, eo.*, a.* 
              FROM users u 
              JOIN employers_organization eo ON u.user_id = eo.user_id 
              JOIN address a ON eo.address_id = a.address_id 
              WHERE u.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $existing_data = $stmt->get_result()->fetch_assoc();

    if (!$existing_data) {
        throw new Exception("User not found.");
    }

    // Define variables from POST data or existing data
    $company_name = $_POST['company_name'] ?? $existing_data['company_name'];
    $registration_number = $_POST['company_reg_no'] ?? $existing_data['registration_number'];
    $company_website = $_POST['company_website'] ?? $existing_data['company_website'];
    $recruiter_name = $_POST['recruiter_name'] ?? $existing_data['recruiter_name'];
    $contact_no = $_POST['recruiter_no'] ?? $existing_data['contact_no'];
    $building = $_POST['building'] ?? $existing_data['building'];
    $street = $_POST['street'] ?? $existing_data['street'];
    $city = $_POST['city'] ?? $existing_data['city'];
    $state = $_POST['state'] ?? $existing_data['state'];
    $country = $_POST['country'] ?? $existing_data['country'];
    $pincode = $_POST['pincode'] ?? $existing_data['pincode'];

    // Start database transaction
    $conn->autocommit(false);
    try {
        // Handle Banner Photo Upload
        $old_banner_photo = $existing_data['banner_photo']; // Get the existing banner photo
        $old_banner_path = "../images/banner/" . $old_banner_photo;

        if (isset($_FILES['banner_photo']) && $_FILES['banner_photo']['error'] === UPLOAD_ERR_OK) {
            // Delete old banner photo if it exists
            if (file_exists($old_banner_path) && !empty($old_banner_photo)) {
                unlink($old_banner_path);
            }

            // Process new banner photo
            $target_dir_banner = "../images/banner/";
            $banner_file_type = strtolower(pathinfo($_FILES['banner_photo']['name'], PATHINFO_EXTENSION));
            $unique_banner_name = uniqid('banner_', true) . '.' . $banner_file_type;
            $target_file_banner = $target_dir_banner . $unique_banner_name;

            // Validate file size and type
            if ($_FILES['banner_photo']['size'] > (5 * 1024 * 1024)) {
                throw new Exception("Banner photo file is too large. Maximum size is 5MB.");
            }
            if (!in_array($banner_file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                throw new Exception("Only JPG, JPEG, PNG & GIF files are allowed for the banner photo.");
            }
            if (!move_uploaded_file($_FILES['banner_photo']['tmp_name'], $target_file_banner)) {
                throw new Exception("Error uploading banner photo.");
            }

            // Update banner photo variable
            $banner_photo = $unique_banner_name;
        } else {
            $banner_photo = $old_banner_photo; // Keep the old banner photo if no new one is uploaded
        }

        // Handle Profile Photo Upload
        $old_profile_photo = $existing_data['profile_photo']; // Get the existing profile photo
        $old_profile_path = "../images/profile/" . $old_profile_photo;

        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            // Delete old profile photo if it exists
            // if (file_exists($old_profile_path) && !empty($old_profile_photo)) {
            //     unlink($old_profile_path);
            // }
            if ($profile_photo !== 'default profile photo.png' && $new_profile_photo !== $profile_photo && file_exists($old_photo_path)) {
                unlink($old_photo_path);
            }

            // Process new profile photo
            $target_dir_profile = "../images/profile/";
            $image_file_type = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
            $unique_profile_name = uniqid('profile_', true) . '.' . $image_file_type;
            $target_file_profile = $target_dir_profile . $unique_profile_name;

            // Validate file size and type
            if ($_FILES['profile_photo']['size'] > (5 * 1024 * 1024)) {
                throw new Exception("Profile photo file is too large. Maximum size is 5MB.");
            }
            if (!in_array($image_file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                throw new Exception("Only JPG, JPEG, PNG & GIF files are allowed for the profile photo.");
            }
            if (!move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_file_profile)) {
                throw new Exception("Error uploading profile photo.");
            }

            // Update profile photo variable
            $profile_photo = $unique_profile_name;
        } else {
            $profile_photo = $old_profile_photo; // Keep the old profile photo if no new one is uploaded
        }

        // Update user profile
        $update_users = "UPDATE users SET contact_no = ?, profile_photo = ? WHERE user_id = ?";
        $stmt_users = $conn->prepare($update_users);
        $stmt_users->bind_param("ssi", $contact_no, $profile_photo, $user_id);
        if (!$stmt_users->execute()) {
            throw new Exception("Error updating users: " . $stmt_users->error);
        }

        // Update organization information including banner photo
        $update_organization = "UPDATE employers_organization 
                                SET company_name = ?, registration_number = ?, company_website = ?, recruiter_name = ?, banner_photo = ? 
                                WHERE user_id = ?";
        $stmt_org = $conn->prepare($update_organization);
        $stmt_org->bind_param("ssssss", $company_name, $registration_number, $company_website, $recruiter_name, $banner_photo, $user_id);
        if (!$stmt_org->execute()) {
            throw new Exception("Error updating employers_organization: " . $stmt_org->error);
        }

        // Update address details
        $address_id = $existing_data['address_id'];
        $update_address = "UPDATE address SET building = ?, street = ?, city = ?, state = ?, country = ?, pincode = ? 
                           WHERE address_id = ?";
        $stmt_address = $conn->prepare($update_address);
        $stmt_address->bind_param("sssssii", $building, $street, $city, $state, $country, $pincode, $address_id);
        if (!$stmt_address->execute()) {
            throw new Exception("Error updating address: " . $stmt_address->error);
        }

        // Commit transaction
        $conn->commit();
        $_SESSION['status_title'] = "✅ Success ✅";
        $_SESSION['status'] = "Profile updated successfully.";
        $_SESSION['status_code'] = "success";
        header("Location: ../Employer/employer_organization_account.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction if any error occurs
        $conn->rollback();
        $_SESSION['status_title'] = "❌ Error ❌";
        $_SESSION['status'] = $e->getMessage();
        $_SESSION['status_code'] = "error";
        header("Location: ../Employer/employer_organization_account.php");
        exit();
    }
}

    

    

    // Change Password
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ei_reset_password'])) {
        $current_password = $_POST['currentPassword'];
        $new_password = $_POST['newPassword'];
        $confirm_password = $_POST['confirmPassword'];

        $query = "SELECT password FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();

        if (!$user_data || !password_verify($current_password, $user_data['password'])) {
            throw new Exception("Current password is incorrect.");
        }

        if ($new_password !== $confirm_password) {
            throw new Exception("New password and confirm password do not match.");
        }

        $hashed_new_password = password_hash($new_password, PASSWORD_BCRYPT);
        $update_password = "UPDATE users SET password = ? WHERE user_id = ?";
        $stmt_update = $conn->prepare($update_password);
        $stmt_update->bind_param("si", $hashed_new_password, $user_id);

        if (!$stmt_update->execute()) {
            throw new Exception("Error updating password: " . $stmt_update->error);
        }

        $_SESSION['status_title'] = "✅ Success ✅";
        $_SESSION['status'] = "Password changed successfully.";
        $_SESSION['status_code'] = "success";
        header("Location: ../Employer/employer_organization_account.php");
        exit();
    }

    // Delete Account
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ei_Account'])) {
        $current_time = date('Y-m-d H:i:s');
        $update_query = "UPDATE users SET deleted_at = ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $current_time, $user_id);
    
        if (!$stmt->execute()) {
            throw new Exception("Error marking account for deletion: " . $stmt->error);
        }
    
        $_SESSION['status_title'] = "✅ Success ✅";
        $_SESSION['status'] = "Your account will be deleted after 15 days.";
        $_SESSION['status_code'] = "success";
    
        header("Location: ../Employer/employer_organization_account.php");
        exit();
    }
    
    

    throw new Exception("Invalid request.");
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['status_title'] = "❌ Error ❌";
    $_SESSION['status'] = $e->getMessage();
    $_SESSION['status_code'] = "error";
    header("Location: ../Employer/employer_organization_account.php");
    exit();
}
?>
