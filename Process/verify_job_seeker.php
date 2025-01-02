<?php
session_start();
ob_start();

// Debug session variables
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    $_SESSION['status_title'] = "😔 Sorry 😔";
    $_SESSION['status'] = "You must log in to access this page.";
    $_SESSION['status_code'] = "error";
    header("Location: http://localhost/Job%20Point/");
    exit;
}

// Check if the logged-in user is an employer
$allowedUserTypes = ['Employer Individual', 'Employer Organization'];
if (!in_array($_SESSION['user_type'], $allowedUserTypes)) {
    $_SESSION['status_title'] = "Unauthorized Access";
    $_SESSION['status'] = "You must log in as an Employer to access this page.";
    $_SESSION['status_code'] = "error";
    header("Location: http://localhost/Job%20Point/");
    exit();
}

// Check if job_id is provided in the URL
if (!isset($_GET['job_id']) || empty($_GET['job_id'])) {
    $_SESSION['status_title'] = "❌ Error ❌";
    $_SESSION['status'] = "Job ID is missing.";
    $_SESSION['status_code'] = "error";
    header("Location: http://localhost/Job%20Point/");
    exit();
}

$jobId = (int)$_GET['job_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Job Seeker</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .center-card {
            max-width: 400px; /* Set max width for the card */
            margin: auto; /* Center the card horizontally */
            margin-top: 100px; /* Add space from the top */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card center-card">
            <div class="card-header text-center">
                Verify Job Seeker
            </div>
            <div class="card-body">
                <form action="process_verification.php" method="POST">
                    <div class="form-group">
                        <label for="verification_code">Enter Verification Code:</label>
                        <input type="text" class="form-control" id="verification_code" name="verification_code" pattern="\d{9}" required placeholder="9-digit code" maxlength="9">
                    </div>
                    <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($jobId); ?>">
                    <button type="submit" class="btn w-100" style="background-color:#4fa671;color:white;">Verify</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<?php
    // Display the status message if set
    if (isset($_SESSION['status']) && $_SESSION['status'] != '') {
        ?>
        <script>
            swal({
                title: "<?php echo $_SESSION['status_title']; ?>",
                text: "<?php echo $_SESSION['status']; ?>",
                icon: "<?php echo $_SESSION['status_code']; ?>",
                button: "OK",
            });
        </script>
        <?php
        unset($_SESSION['status_title']);
        unset($_SESSION['status']);
        unset($_SESSION['status_code']);
    }
?>

<?php ob_end_flush(); ?>
