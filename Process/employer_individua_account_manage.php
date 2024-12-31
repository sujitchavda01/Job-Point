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
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ea_update_profile'])) {
        // Fetch existing data
        $query = "SELECT u.*, ei.*, a.* 
                  FROM users u 
                  JOIN employers_individual ei ON u.user_id = ei.user_id 
                  JOIN address a ON ei.address_id = a.address_id 
                  WHERE u.user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $existing_data = $result->fetch_assoc();

        // Check if user exists
        if (!$existing_data) {
            throw new Exception("User not found.");
        }

        // Prepare data for update
        $first_name = $_POST['first_name'] ?? $existing_data['first_name'];
        $middle_name = $_POST['middle_name'] ?? $existing_data['middle_name'];
        $last_name = $_POST['last_name'] ?? $existing_data['last_name'];
        $contact_no = $_POST['contact_no'] ?? $existing_data['contact_no'];
        $building = $_POST['building'] ?? $existing_data['building'];
        $street = $_POST['street'] ?? $existing_data['street'];
        $city = $_POST['city'] ?? $existing_data['city'];
        $state = $_POST['state'] ?? $existing_data['state'];
        $country = $_POST['country'] ?? $existing_data['country'];
        $pincode = $_POST['pincode'] ?? $existing_data['pincode'];

        // Handle profile photo upload
        $profile_photo = $existing_data['profile_photo'];
        $old_photo_path = "../images/profile/" . $profile_photo;

        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $TargetDir = "../images/profile/";
            $ImageFileType = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
            $UniqueName = uniqid('profile_', true) . '.' . $ImageFileType;
            $TargetFile = $TargetDir . $UniqueName;

            if ($_FILES['profile_photo']['size'] > (5 * 1024 * 1024)) {
                throw new Exception("File is too large. Maximum size is 5MB.");
            }

            if (!in_array($ImageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                throw new Exception("Only JPG, JPEG, PNG & GIF files are allowed.");
            }

            if (!move_uploaded_file($_FILES['profile_photo']['tmp_name'], $TargetFile)) {
                throw new Exception("Error uploading profile photo.");
            }

            $profile_photo = $UniqueName;
        }

        // Begin transaction
        $conn->autocommit(false);

        // Update users table
        $update_users = "UPDATE users SET contact_no = ?, profile_photo = ? WHERE user_id = ?";
        $stmt_users = $conn->prepare($update_users);
        $stmt_users->bind_param("ssi", $contact_no, $profile_photo, $user_id);
        if (!$stmt_users->execute()) {
            throw new Exception("Error updating users: " . $stmt_users->error);
        }

        // Update employers_individual table
        $update_individual = "UPDATE employers_individual SET first_name = ?, middle_name = ?, last_name = ? WHERE user_id = ?";
        $stmt_individual = $conn->prepare($update_individual);
        $stmt_individual->bind_param("sssi", $first_name, $middle_name, $last_name, $user_id);
        if (!$stmt_individual->execute()) {
            throw new Exception("Error updating employers_individual: " . $stmt_individual->error);
        }

        // Update address table
        $address_id = $existing_data['address_id'];
        $update_address = "UPDATE address SET building = ?, street = ?, city = ?, state = ?, country = ?, pincode = ? WHERE address_id = ?";
        $stmt_address = $conn->prepare($update_address);
        $stmt_address->bind_param("ssssssi", $building, $street, $city, $state, $country, $pincode, $address_id);
        if (!$stmt_address->execute()) {
            throw new Exception("Error updating address: " . $stmt_address->error);
        }

        // Commit transaction
        $conn->commit();
        $conn->autocommit(true);

        // Delete old photo if a new one was uploaded
        if ($profile_photo !== $existing_data['profile_photo'] && file_exists($old_photo_path)) {
            unlink($old_photo_path);
        }

        $_SESSION['status_title'] = "✅ Success ✅";
        $_SESSION['status'] = "Profile updated successfully.";
        $_SESSION['status_code'] = "success";
        header("Location: ../Employer/employer_individua_account.php");
        exit();
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ei_reset_password'])) {
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
         header("Location: ../Employer/employer_individua_account.php");
         exit();
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ei_Account'])) {
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
            header("Location: ../Employer/employer_individua_account.php");
            exit();
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();

                // Set error message
                $_SESSION['status_title'] = "❌ Error ❌";
                $_SESSION['status'] = $e->getMessage();
                $_SESSION['status_code'] = "error";
                header("Location: ../Employer/employer_individua_account.php");
                exit();
            } finally {
                // Enable autocommit again
                $conn->autocommit(true);
            }
        }

    
    else {
        // Handle invalid request method
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
    header("Location: ../Employer/employer_individua_account.php");
    exit();
}
?>
