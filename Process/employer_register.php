<?php
session_start(); // Start the session

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Include the database configuration file
    require '../DB Connection/config.php'; // Ensure this path is correct

    // Check if the form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Start a transaction
        $conn->begin_transaction();

        // Check which form was submitted
        if (isset($_POST['register_organization'])) {
            // Handling Employer Organization Registration
            $company_name = $conn->real_escape_string(trim($_POST['company_name']));
            $registration_number = $conn->real_escape_string(trim($_POST['registration_number']));
            $building = $conn->real_escape_string(trim($_POST['building']));
            $street = $conn->real_escape_string(trim($_POST['street']));
            $city = $conn->real_escape_string(trim($_POST['city']));
            $state = $conn->real_escape_string(trim($_POST['state']));
            $country = $conn->real_escape_string(trim($_POST['country']));
            $pincode = $conn->real_escape_string(trim($_POST['pincode']));
            $recruiter_name = $conn->real_escape_string(trim($_POST['recruiter_name']));
            $company_contact_no = $conn->real_escape_string(trim($_POST['company_contact_no']));
            $recruiter_contact_no = $conn->real_escape_string(trim($_POST['recruiter_contact_no']));
            $company_website = $conn->real_escape_string(trim($_POST['company_website']));
            $company_description = $conn->real_escape_string(trim($_POST['company_description']));
            $email = $conn->real_escape_string(trim($_POST['email']));
            $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);

            // Insert into `address` table
            $insert_address = "INSERT INTO address (building, street, city, state, country, pincode) VALUES ('$building', '$street', '$city', '$state', '$country', '$pincode')";
            if ($conn->query($insert_address) === TRUE) {
                $address_id = $conn->insert_id; // Get the last inserted address ID

                // Insert into `users` table
                $insert_user = "INSERT INTO users (user_type, email, password, contact_no) VALUES ('Employer Organization', '$email', '$password', '$company_contact_no')";
                if ($conn->query($insert_user) === TRUE) {
                    $user_id = $conn->insert_id; // Get the last inserted user ID

                    // Insert into `employers_organization` table
                    $insert_organization = "INSERT INTO employers_organization (user_id, company_name, registration_number, address_id, recruiter_name, company_contact_no, company_website, company_description) 
                                             VALUES ('$user_id', '$company_name', '$registration_number', '$address_id', '$recruiter_name', '$company_contact_no', '$company_website', '$company_description')";
                    if ($conn->query($insert_organization) === TRUE) {
                        // Commit the transaction if all inserts are successful
                        $conn->commit();

                        $_SESSION['status_title'] = "Success!";
                        $_SESSION['status'] = "Registration successful!";
                        $_SESSION['status_code'] = "success";
                        header("Location: ../");
                        exit();
                    } else {
                        throw new Exception("Error inserting into employers_organization: " . $conn->error);
                    }
                } else {
                    throw new Exception("Error inserting into users: " . $conn->error);
                }
            } else {
                throw new Exception("Error inserting into address: " . $conn->error);
            }
        } elseif (isset($_POST['register_individual'])) {
            // Handling Individual Registration
            $first_name = trim($_POST['first_name']);
            $middle_name = trim($_POST['middle_name']);
            $last_name = trim($_POST['last_name']);
            $building = trim($_POST['building']);
            $street = trim($_POST['street']);
            $city = trim($_POST['city']);
            $state = trim($_POST['state']);
            $country = trim($_POST['country']);
            $pincode = trim($_POST['pincode']);
            $email = trim($_POST['email']);
            $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT); // Hash the password
            $contact_no = trim($_POST['contact_no']);

            // Validate input (Basic validation, improve as needed)
            $required_fields = [$first_name, $middle_name, $last_name, $building, $street, $city, $state, $country, $pincode, $email, $password, $contact_no];
            if (in_array("", $required_fields)) {
                throw new Exception("All fields are required.");
            }

            // Insert address
            $sql_address = "INSERT INTO address (building, street, city, state, country, pincode) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_address = $conn->prepare($sql_address);
            if (!$stmt_address) {
                throw new Exception("Prepare statement failed for address: " . $conn->error);
            }

            $stmt_address->bind_param("ssssss", $building, $street, $city, $state, $country, $pincode);
            if (!$stmt_address->execute()) {
                throw new Exception("Execute failed for address: " . $stmt_address->error);
            }

            $address_id = $conn->insert_id; // Get the last inserted address ID

            // Insert user
            $sql_user = "INSERT INTO users (user_type, email, password, contact_no) VALUES ('Employer Individual', ?, ?, ?)";
            $stmt_user = $conn->prepare($sql_user);
            if (!$stmt_user) {
                throw new Exception("Prepare statement failed for user: " . $conn->error);
            }

            $stmt_user->bind_param("sss", $email, $password, $contact_no);
            if (!$stmt_user->execute()) {
                throw new Exception("Execute failed for user: " . $stmt_user->error);
            }

            $user_id = $conn->insert_id; // Get the last inserted user ID

            // Insert employer individual
            $sql_employer = "INSERT INTO employers_individual (user_id, first_name, middle_name, last_name, address_id) VALUES (?, ?, ?, ?, ?)";
            $stmt_employer = $conn->prepare($sql_employer);
            if (!$stmt_employer) {
                throw new Exception("Prepare statement failed for employer: " . $conn->error);
            }

            $stmt_employer->bind_param("isssi", $user_id, $first_name, $middle_name, $last_name, $address_id);
            if (!$stmt_employer->execute()) {
                throw new Exception("Execute failed for employer: " . $stmt_employer->error);
            }

            // Commit the transaction
            $conn->commit();

            // Set success message and redirect
            $_SESSION['status_title'] = "Success";
            $_SESSION['status'] = "Registration successful!";
            $_SESSION['status_code'] = "success";
            header("Location: ../"); // Redirect to a success page
            exit;

        } else {
            throw new Exception("Invalid submission.");
        }
    } else {
        header("Location: ../"); // Redirect back to the registration page
        exit;
    }
} catch (mysqli_sql_exception $e) {
    // Handle database-related errors
    error_log("Database error: " . $e->getMessage());
    $_SESSION['status_title'] = "Error";
    $_SESSION['status'] = "A database error occurred: " . $e->getMessage(); // Include the error message
    $_SESSION['status_code'] = "error";
    header("Location: ../"); // Redirect back to the registration page
    exit;
} catch (Exception $e) {
    // Handle general errors
    error_log("General error: " . $e->getMessage());
    $_SESSION['status_title'] = "Error";
    $_SESSION['status'] = "An error occurred: " . $e->getMessage(); // Include the error message
    $_SESSION['status_code'] = "error";
    header("Location: ../"); // Redirect back to the registration page
    exit;
} finally {
    $conn->close(); // Close the database connection
}
?>
