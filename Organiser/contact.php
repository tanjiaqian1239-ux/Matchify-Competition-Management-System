<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name    = htmlspecialchars($_POST['name']);
    $email   = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    try {
        $mail = require __DIR__ . '/../mailer.php';

        $mail->setFrom('tanjiaqian1239@gmail.com', 'Matchify Competition Management Platform');
        $mail->addAddress('tanjiaqian1239@gmail.com');
        $mail->addReplyTo($email, $name);

        $mail->isHTML(true);
        $mail->Subject = 'New Contact Message from ' . $name;

        $mail->Body = "
            <!DOCTYPE html>
            <html>
            <body style='font-family:Arial,sans-serif; background:#f5f7fb; padding:30px;'>
              <div style='max-width:520px; margin:auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 8px 30px rgba(0,0,0,0.08);'>
                
                <div style='background:linear-gradient(135deg,#6d5dfc,#8a7dff); padding:30px; text-align:center;'>
                  <h1 style='color:#fff; margin:0; font-size:22px;'>Matchify</h1>
                  <p style='color:rgba(255,255,255,0.85); margin:6px 0 0; font-size:14px;'>Competition Management Platform</p>
                </div>

                <div style='padding:30px;'>
                  <h2 style='color:#111827; margin:0 0 10px;'>New Contact Message</h2>
                  <div style='background:#f5f3ff; border:1px solid #ddd6fe; border-radius:12px; padding:20px; margin:20px 0;'>
                    <table style='width:100%; font-size:14px; color:#111827;'>
                      <tr>
                        <td style='padding:6px 0; width:80px; color:#6b7280;'>Name</td>
                        <td style='padding:6px 0;'><b>$name</b></td>
                      </tr>
                      <tr>
                        <td style='padding:6px 0; color:#6b7280;'>Email</td>
                        <td style='padding:6px 0;'><b>$email</b></td>
                      </tr>
                      <tr>
                        <td style='padding:6px 0; color:#6b7280; vertical-align:top;'>Message</td>
                        <td style='padding:6px 0;'>$message</td>
                      </tr>
                    </table>
                  </div>
                </div>

                <div style='background:#f9fafb; padding:16px; text-align:center; border-top:1px solid #f0f0f0;'>
                  <p style='margin:0; font-size:12px; color:#9ca3af;'>This is an automated email from Matchify Competition Management Platform.</p>
                </div>

              </div>
            </body>
            </html>
        ";

        $mail->AltBody = "Name: $name\nEmail: $email\nMessage: $message";

        $mail->send();
        $success = "Message sent successfully!";

    } catch (Exception $e) {
        $success = "Message failed to send.";
        error_log("[Matchify] Contact mail failed - " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../images/logo.png">
    <title>Contact - Matchify</title>
    <link rel="stylesheet" href="../css/contact.css">
</head>
<body>

<div class="hero">

    <nav>
        <img src="../images/logo.png" class="logo">

        <ul>
            <li><a href="index-organiser.php">Home</a></li>
            <li><a href="competition-list.php">Competition List</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>

        <div class="nav-right">
            <?php if (!isset($_SESSION['user_id'])): ?>
            <?php else: ?>
                <div class="profile-dropdown">
                    <img src="<?php echo $profile_image; ?>" class="profile-icon" onclick="toggleMenu()">

                    <div class="dropdown-menu" id="dropdownMenu">
                        <a href="profile.php">My Profile</a>
                        <a href="../login.php">Logout</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

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