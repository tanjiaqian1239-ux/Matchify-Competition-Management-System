
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="images/logo.png">
    <title>Login - Mathchify</title>
    <link rel="stylesheet" href="css/forgetpassword.css">
</head>
<body>

<div class="hero">
    <nav>
        <img src="images/logo.png" class="logo">
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="#">Competition List</a></li>
            <li><a href="#">About</a></li>
            <li><a href="#">Contact</a></li>
        </ul>
        <a href="login.php" class="btn">Login</a>
    </nav>

    <div class="container">
        <div class="title">Forgot Password</div>

        <form method="POST" action="send-password-reset.php">

            <div class="user-details">

                <div class="input-box">
                    <span class="details">Email</span>
                    <input type="email" name="email"
                        placeholder="Enter your email"
                        required>
                </div>
                
            <div class="button">
                <input type="submit" value="Send" class="btn">
            </div>

        </form>
    </div>
</div>



</body>
</html>