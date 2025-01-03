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
                        <label for="verification_code">Enter Verification Code*:</label>
                        <input type="text" class="form-control" id="verification_code" name="verification_code" pattern="\d{9}" required placeholder="9-digit code" maxlength="9">
                    </div>
                    <div class="form-group">
                        <label for="rating">Rate The Worker*:</label>
                        <div class="star-rating">
                            <input type="radio" id="star1" name="rating" value="1" required />
                            <label for="star1" class="star">&#9733;</label>
                            <input type="radio" id="star2" name="rating" value="2" required />
                            <label for="star2" class="star">&#9733;</label>
                            <input type="radio" id="star3" name="rating" value="3" required />
                            <label for="star3" class="star">&#9733;</label>
                            <input type="radio" id="star4" name="rating" value="4" required />
                            <label for="star4" class="star">&#9733;</label>
                            <input type="radio" id="star5" name="rating" value="5" required />
                            <label for="star5" class="star">&#9733;</label>
                        </div>
                    </div>

                    <style>
                        .star-rating {
                            direction: rtl; /* Change direction to left-to-right */
                            display: flex;
                            justify-content: center; /* Center the stars */
                            font-size: 40px; /* Increase star size */
                        }
                        .star-rating input {
                            display: none; /* Hide the radio buttons */
                        }
                        .star {
                            color: #ccc; /* Default star color */
                            cursor: pointer;
                            margin: 0 5px; /* Add space between stars */
                        }
                        /* Change the CSS selector to target all previous siblings */
                        .star-rating input:checked ~ .star {
                            color: #ffcc00; /* Color for selected stars */
                        }
                        .star-rating input:checked + label.star,
                        .star-rating input:checked + label.star ~ label.star {
                            color: #ffcc00; /* Color for selected stars */
                        }
                        .star-rating label:hover,
                        .star-rating label:hover ~ label.star {
                            color: #ffcc00; /* Color for stars on hover */
                        }
                    </style>

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
