<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Application Submitted</title>
<link rel="stylesheet" href="../css/style.css">
<style>
.success-box{
    max-width:600px;
    margin:100px auto;
    padding:40px;
    background:white;
    border-radius:15px;
    text-align:center;
    box-shadow:0 10px 30px rgba(0,0,0,0.1);
}

.success-box h1{
    color:#28a745;
    margin-bottom:20px;
}

.success-box p{
    color:#555;
    font-size:16px;
    line-height:1.6;
}

.badge{
    display:inline-block;
    margin-top:20px;
    padding:10px 20px;
    background:#f3f3f3;
    border-radius:20px;
    font-size:14px;
    color:#333;
}
</style>
</head>

<body>

<div class="success-box">

    <h1>✅ Application Submitted Successfully!</h1>

    <p>
        Your competition application has been submitted successfully.<br><br>

        📌 Please wait for admin review and approval.<br>
        📅 Estimated processing time: <b>3 - 5 working days</b><br><br>

        You will be notified once your application is reviewed.
    </p>

    <div class="badge">
        Status: Pending Approval
    </div>

    <br><br>

    <a href="competition-list.php" class="btn">Back to Competition List</a>

</div>

</body>
</html>