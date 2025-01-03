<?php
session_start(); // Start the session

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Include the database configuration file
    require '../DB Connection/config.php'; // Ensure this path is correct

    // Utility function to convert text to PascalCase
    function toPascalCase($string) {
        return str_replace(' ', '', ucwords(strtolower(trim($string))));
    }

    // Check if the form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Start a transaction
        $conn->begin_transaction();

        // Check which form was submitted
        if (isset($_POST['register_organization'])) {
            // Handling Employer Organization Registration
            $company_name = toPascalCase($conn->real_escape_string($_POST['company_name']));
            $registration_number = toPascalCase($conn->real_escape_string($_POST['registration_number']));
            $building = toPascalCase($conn->real_escape_string($_POST['building']));
            $street = toPascalCase($conn->real_escape_string($_POST['street']));
            $city = toPascalCase($conn->real_escape_string($_POST['city']));
            $state = toPascalCase($conn->real_escape_string($_POST['state']));
            $country = toPascalCase($conn->real_escape_string($_POST['country']));
            $pincode = trim($conn->real_escape_string($_POST['pincode']));
            $recruiter_name = toPascalCase($conn->real_escape_string($_POST['recruiter_name']));
            $company_contact_no = trim($conn->real_escape_string($_POST['company_contact_no']));
            $recruiter_contact_no = trim($conn->real_escape_string($_POST['recruiter_contact_no']));
            $company_website = trim($conn->real_escape_string($_POST['company_website']));
            $company_description = toPascalCase($conn->real_escape_string($_POST['company_description']));
            $email = trim($conn->real_escape_string($_POST['email']));
            $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);

            // Insert into `address` table
            $insert_address = "INSERT INTO address (building, street, city, state, country, pincode) VALUES ('$building', '$street', '$city', '$state', '$country', '$pincode')";
            if ($conn->query($insert_address) === TRUE) {
                $address_id = $conn->insert_id; // Get the last inserted address ID

                // Insert into `users` table
                $insert_user = "INSERT INTO users (user_type, email, password, contact_no) VALUES ('Employer Organization', '$email', '$password', '$$recruiter_contact_no')";
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
            $first_name = toPascalCase($_POST['first_name']);
            $middle_name = toPascalCase($_POST['middle_name']);
            $last_name = toPascalCase($_POST['last_name']);
            $building = toPascalCase($_POST['building']);
            $street = toPascalCase($_POST['street']);
            $city = toPascalCase($_POST['city']);
            $state = toPascalCase($_POST['state']);
            $country = toPascalCase($_POST['country']);
            $pincode = trim($_POST['pincode']);
            $email = trim($_POST['email']);
            $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT); // Hash the password
            $contact_no = trim($_POST['contact_no']);

            // Validate input
            $required_fields = [$first_name, $middle_name, $last_name, $building, $street, $city, $state, $country, $pincode, $email, $password, $contact_no];
            if (in_array("", $required_fields)) {
                throw new Exception("All fields are required.");
            }

            // Insert into `address` table
            $sql_address = "INSERT INTO address (building, street, city, state, country, pincode) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_address = $conn->prepare($sql_address);
            if (!$stmt_address) {
                throw new Exception("Prepare statement failed for address: " . $conn->error);
            }
            $stmt_address->bind_param("ssssss", $building, $street, $city, $state, $country, $pincode);
            if (!$stmt_address->execute()) {
                throw new Exception("Execute failed for address: " . $stmt_address->error);
            }
            $address_id = $conn->insert_id;

            // Insert into `users` table
            $sql_user = "INSERT INTO users (user_type, email, password, contact_no) VALUES ('Employer Individual', ?, ?, ?)";
            $stmt_user = $conn->prepare($sql_user);
            if (!$stmt_user) {
                throw new Exception("Prepare statement failed for user: " . $conn->error);
            }
            $stmt_user->bind_param("sss", $email, $password, $contact_no);
            if (!$stmt_user->execute()) {
                throw new Exception("Execute failed for user: " . $stmt_user->error);
            }
            $user_id = $conn->insert_id;

            // Insert into `employers_individual` table
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

            $_SESSION['status_title'] = "Success!";
            $_SESSION['status'] = "Registration successful!";
            $_SESSION['status_code'] = "success";
            header("Location: ../");
            exit();
        } else {
            throw new Exception("Invalid submission.");
        }
    } else {
        header("Location: ../");
        exit();
    }
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    error_log("Error: " . $e->getMessage());
    $_SESSION['status_title'] = "Error!";
    $_SESSION['status'] = $e->getMessage();
    $_SESSION['status_code'] = "error";
    header("Location: ../");
    exit();
} finally {
    $conn->close();
}
?>
