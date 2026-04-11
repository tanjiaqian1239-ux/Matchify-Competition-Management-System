<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'tanjiaqian@gmail.com';
        $mail->Password   = 'Jiaiqan20040520';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom($email, $name);
        $mail->addAddress('yourgmail@gmail.com');

        $mail->isHTML(true);
        $mail->Subject = 'New Contact Message';

        $mail->Body = "
            <h3>New Contact Message</h3>
            <p><b>Name:</b> $name</p>
            <p><b>Email:</b> $email</p>
            <p><b>Message:</b><br>$message</p>
        ";

        $mail->send();
        $success = "Message sent successfully!";
    } catch (Exception $e) {
        $success = "Message failed to send.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Matchify</title>
    <link rel="stylesheet" href="css/contact.css">
</head>
<body>

<div class="hero">

    <nav>
        <img src="images/logo.png" class="logo">

        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="competition-list.php">Competition List</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>

        <a href="login.php" class="btn">Login</a>
    </nav>

    <div class="content">

        <div class="contact-container">

            <h1>Contact Us</h1>

            <?php if (!empty($success)) { ?>
                <p class="success-msg"><?php echo $success; ?></p>
            <?php } ?>

            <form method="POST" class="contact-form">
                <input type="text" name="name" placeholder="Your Name" required>
                <input type="email" name="email" placeholder="Your Email" required>
                <textarea name="message" placeholder="Your Message" rows="5" required></textarea>
                <button type="submit" class="btn">Send Message</button>
            </form>

            <div class="contact-info">
                <p>support@matchify.com</p>
                <p>+60 12-345 6789</p>
            </div>

        </div>

    </div>

</div>

</body>
</html>