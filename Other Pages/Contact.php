<?php
ob_start(); // Start output buffering

include '../base other/header.php'; 

// Include PHPMailer classes
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $message = $_POST['message'];

    // Email configuration
    $to = '';
    $subject = 'Contact Form Submission';
    $body = "<p><strong>Email:</strong> $email</p><p><strong>Message:</strong></p><p>$message</p>";

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->isHTML(true);
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->Username = '';
        $mail->Password = '';
        $mail->setFrom('', 'Job Point Contact Us');
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        
        $_SESSION['status_title'] = "Success!";
        $_SESSION['status'] = "Your message has been sent successfully!";
        $_SESSION['status_code'] = "success";
        header("Location: ../"); 
        exit(); 
    } catch (Exception $e) {
        $_SESSION['status_title'] = "Error!";
        $_SESSION['status'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        $_SESSION['status_code'] = "error";
        header("Location: ../"); 
        exit(); 
    }
}
?>


<style>
    .contact-container {
        max-width: 600px;
        margin: 0 auto;
        padding: 20px;
        border: 1px solid #ccc;
        border-radius: 8px;
        background-color: #f9f9f9;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    .contact-info {
        margin-bottom: 20px;
    }
    .contact-info h5 {
        margin-bottom: 10px;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .social-icons {
        margin-top: 10px;
    }
    .social-icons a {
        margin: 0 10px;
        color: #333;
        font-size: 24px;
        transition: color 0.3s;
    }
    .social-icons a:hover {
        color: #007bff;
    }
</style>

<div class="container mt-4 mb-5">
    <div class="contact-container">
        <h3 class="text-center">Contact Us</h3>

        <?php if (isset($_SESSION['status'])): ?>
            <div class="alert alert-<?php echo $_SESSION['status_code']; ?>">
                <strong><?php echo $_SESSION['status_title']; ?></strong> <?php echo $_SESSION['status']; ?>
            </div>
            <?php 
            // Unset session variables after displaying the message
            unset($_SESSION['status_title']);
            unset($_SESSION['status']);
            unset($_SESSION['status_code']);
            ?>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="email">Your Email Address:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="message">Your Message:</label>
                <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-apply btn-block w-100">Submit</button>
        </form>
        
        <div class="text-center mt-4 mb-2">
            <p>Or reach us on:</p>
            <div class="social-icons d-flex justify-content-center">
                <a href="https://wa.me/yourwhatsapplink" target="_blank" title="WhatsApp" class="mx-3">
                    <i class="bi bi-whatsapp"></i>
                </a>
                <a href="https://facebook.com/yourfacebooklink" target="_blank" title="Facebook" class="mx-3">
                    <i class="bi bi-facebook"></i>
                </a>
                <a href="https://instagram.com/yourinstagramlink" target="_blank" title="Instagram" class="mx-3">
                    <i class="bi bi-instagram"></i>
                </a>
                <a href="https://twitter.com/yourtwitterlink" target="_blank" title="Twitter" class="mx-3">
                    <i class="bi bi-twitter"></i>
                </a>
            </div>
        </div>

    </div>
</div>

<?php include '../base other/footer.php'; ?>
