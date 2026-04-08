<?php
require __DIR__ . "/config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = $_POST["email"];

    $token = bin2hex(random_bytes(16));
    $token_hash = hash("sha256", $token);
    $expiry = date("Y-m-d H:i:s", time() + 60 * 30); 

    $sql = "UPDATE users
            SET reset_token_hash = ?,
                reset_token_expires_at = ?
            WHERE email = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL error: " . $conn->error);
    }

    $stmt->bind_param("sss", $token_hash, $expiry, $email);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {

        $mail = require __DIR__ . "/mailer.php";

        $mail->setFrom("yourgmail@gmail.com", "Matchify Competition Management Platform");
        $mail->addAddress($email);
        $mail->isHTML(true);

        $mail->Subject = "Reset Your Password - Matchify";

        $resetLink = "http://localhost/Matchify%20Competition%20Management%20System/reset-password.php?token=$token";

        $mail->Body = "
        <h2>Password Reset</h2>
        <p>Hello,</p>
        <p>Click the button below to reset your password:</p>

        <p>
        <a href='$resetLink' 
           style='display:inline-block;
                  padding:10px 20px;
                  background:#007bff;
                  color:#fff;
                  text-decoration:none;
                  border-radius:5px;'>
        Reset Password
        </a>
        </p>

        <p>This link expires in 30 minutes.</p>
        ";

        try {
            $mail->send();
            $message = "✅ Reset link sent!";
            $redirect = "login.php";
        } catch (Exception $e) {
            $message = "❌ Email failed: " . $mail->ErrorInfo;
            $redirect = "forgot-password.php";
        }

    } else {
        $message = "❌ Email not found!";
        $redirect = "forgot-password.php";
    }

} else {
    $message = "❌ Invalid request!";
    $redirect = "forgot-password.php";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Processing</title>
</head>
<body>
<script>
alert("<?php echo $message; ?>");
window.location.href = "<?php echo $redirect; ?>";
</script>
</body>
</html>