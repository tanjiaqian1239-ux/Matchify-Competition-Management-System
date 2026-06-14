<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = require __DIR__ . "/mailer.php";

try {
    $mail->setFrom('tanjiaqian1239@gmail.com', 'Matchify');
    $mail->addAddress('tanjiaqian1239@gmail.com');
    $mail->Subject = "Test Email";
    $mail->Body = "This is a test email.";
    $mail->send();
    echo "Email sent successfully!";
} catch (Exception $e) {
    echo "Error: " . $mail->ErrorInfo;
}
?>