<?php
session_start(); // Start the session

try {
    require '../DB Connection/config.php'; // Include the database config file

    // Check the database connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['register_jobseeker']) {
        // Sanitize inputs
        $first_name = $conn->real_escape_string($_POST['firstName']);
        $middle_name = $conn->real_escape_string($_POST['middleName']);
        $last_name = $conn->real_escape_string($_POST['lastName']);
        $gender = $conn->real_escape_string($_POST['gender']);
        $date_of_birth = $conn->real_escape_string($_POST['dateOfBirth']);
        $email = $conn->real_escape_string($_POST['email']);
        $contact_no = $conn->real_escape_string($_POST['Contact_No']);
        $password = password_hash($conn->real_escape_string($_POST['password']), PASSWORD_BCRYPT);
        $service_type = $conn->real_escape_string($_POST['serviceType']);
        $education = $conn->real_escape_string($_POST['education']);
        $experience = $conn->real_escape_string($_POST['experience']);
        $bio = $conn->real_escape_string($_POST['bio']);

        // Start a transaction
        $conn->begin_transaction();

        // Insert into `users` table
        $insert_user = "INSERT INTO users (user_type, email, password, contact_no) VALUES ('Job Seeker', '$email', '$password', '$contact_no')";
        if ($conn->query($insert_user) === TRUE) {
            $user_id = $conn->insert_id; // Get the last inserted user ID

            // Insert into `job_seekers` table
            $insert_seeker = "INSERT INTO job_seekers (user_id, first_name, middle_name, last_name, gender, date_of_birth, service_type, education, experience, bio) 
                              VALUES ('$user_id', '$first_name', '$middle_name', '$last_name', '$gender', '$date_of_birth', '$service_type', '$education', '$experience', '$bio')";

            if ($conn->query($insert_seeker) === TRUE) {
                // Commit the transaction if both inserts are successful
                $conn->commit();

                $_SESSION['status_title'] = "Success!";
                $_SESSION['status'] = "Registration successful!";
                $_SESSION['status_code'] = "success";
                header("Location: ../");
                exit();
            } else {
                throw new Exception("Error inserting into job_seekers: " . $conn->error);
            }
        } else {
            throw new Exception("Error inserting into users: " . $conn->error);
        }
    }else{
        header("Location: ../"); // Redirect back to the registration page
        exit;
    }
} catch (Exception $e) {
    // Rollback the transaction if any query fails
    $conn->rollback();

    $_SESSION['status_title'] = "Error!";
    $_SESSION['status'] = $e->getMessage();
    $_SESSION['status_code'] = "error";
    header("Location: ../");
    exit();
} finally {
    $conn->close(); // Close the database connection
}
?>
