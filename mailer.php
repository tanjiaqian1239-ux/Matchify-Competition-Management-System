<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/PHPMailer/src/PHPMailer.php";
require __DIR__ . "/PHPMailer/src/SMTP.php";
require __DIR__ . "/PHPMailer/src/Exception.php";

$mail = new PHPMailer(true);

$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'tanjiaqian1239@gmail.com';
$mail->Password = 'rbjk fqzq prhf owve';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

$mail->isHTML(true);

return $mail;