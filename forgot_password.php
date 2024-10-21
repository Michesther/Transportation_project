<?php
require_once 'db_connect.php';
require 'vendor/autoload.php'; // For PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;  

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $otp = mt_rand(1111, 9999); 

        // Update OTP in the users table
        $stmt = $conn->prepare("UPDATE users SET otp = ? WHERE email = ?");
        $stmt->bind_param("ss", $otp, $email);
        $stmt->execute();

        // Send OTP via email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'emekaisaacisreal@gmail.com'; 
            $mail->Password = 'oxekwyjnkgpodzgb';
            $mail->Port = 587;

            $mail->setFrom('emekaisaacisreal@gmail.com', 'GEO SOLUTION');
            $mail->addAddress($email);
            $mail->Subject = 'OTP for Password Reset';
            $mail->Body = "Your OTP for password reset is: " . $otp;

            $mail->send();
            echo "OTP has been sent to your email.";

            // Redirect to Reset Password form with email as a query parameter
            header("Location: reset_password.php?email=" . urlencode($email));
            exit;
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
     body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f4f4;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

.form-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    padding: 30px;
    width: 90%;
    max-width: 400px;
}

h2 {
    text-align: center;
    color: #333;
    margin-bottom: 20px;
}

input {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: 1px solid #ddd;
    border-radius: 4px;
    transition: border-color 0.3s;
}

input:focus {
    border-color: #007bff;
    outline: none;
}

button {
    width: 100%;
    padding: 12px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
}

button:hover {
    background-color: #0056b3;
}

.footer-text {
    text-align: center;
    margin-top: 15px;
}

.footer-text a {
    color: #007bff;
    text-decoration: none;
}

    </style>
</head>
<body>
    <div class="form-container">
        <h2>Forgot Password</h2>
        <form action="" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <button type="submit">Send OTP</button>
        </form>
    </div>
</body>
</html>
