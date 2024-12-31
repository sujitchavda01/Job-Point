<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
require "vendor/autoload.php"; 

// $to = $email; // Ensure $email is defined before this line
$subject = "Registration"; // Corrected spelling
$body = "HELLO"; // Added missing semicolon here
$mail = new PHPMailer(true);
// $mail->SMTPDebug = SMTP::DEBUG_SERVER;
$mail->isSMTP();
$mail->SMTPAuth = true;
$mail->IsHTML(true);
$mail->AddReplyTo("lakhanim205@gmail.com");
$mail->Host = "smtp.gmail.com";
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
$mail->Username = "lakhanim205@gmail.com";
$mail->Password = "ttsxraxjkkrvugyb";
$mail->setFrom("lakhanim205@gmail.com", "Text Test Mail");
$mail->addAddress("sujitchavda01@gmail.com", "");
$mail->Subject = $subject;
$mail->Body = $body;

if($mail->send()) {
    echo "Mail sent successfully!";
} else {
    echo "Mail could not be sent.";
}
?>
