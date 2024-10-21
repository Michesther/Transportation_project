<?php
session_start();
require_once 'db_connect.php';

require 'vendor/autoload.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;  

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $unique_id = bin2hex(random_bytes(10)); 
    $otp = mt_rand(1111, 9999); 
    



   


if (!empty($firstname) && !empty($lastname) && !empty($email) && !empty($password)) {
    // Check if the email already exists
    $sql = "SELECT email FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
    } else {
        // Prepare the SQL statement
        $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, password, otp) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            die('Error: ' . $conn->error);  // Error if the preparation failed
        }
        $stmt->bind_param("sssss", $firstname, $lastname, $email, $password, $otp);

        if ($stmt->execute()) {
            // Redirect to verification page
            setcookie("users_firstname", $firstname, time() + (86400 * 30), "/");
            setcookie("users_email", $email, time() + (86400 * 30), "/");
            setcookie("user_unique_id", $unique_id, time() + (86400 * 30), "/");

            $mail = new PHPMailer(true);

            try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com'; 
                    $mail->SMTPAuth = true;
                    $mail->Username = 'emekaisaacisreal@gmail.com'; // Replace with environment variable if possible
                    $mail->Password = 'oxekwyjnkgpodzgb'; // Replace with environment variable if possible
                    // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('emekaisaacisreal@gmail.com', 'GEO SOLUTION');
                    $mail->addAddress($email, $firstname);
                    $mail->Subject = 'OTP Verification';
                    $mail->Body = 'Dear ' . $firstname . ', Your OTP code is: ' . $otp . '. Do not share it with anyone. If you did not request this code, please ignore this email.';

                   
                    $mail->send();

                    header('Location: verification.htm'); // Change 'verification.php' to your actual verification page
                    exit(); 

                    echo "Email has been sent";
                } catch (Exception $e) {
                    echo "Email could not be sent. Error: {$mail->ErrorInfo}";
                }
        } else {
            echo json_encode(['success' => false, 'message' => "Error: " . $stmt->error]);
        }

        $stmt->close();
        $conn->close();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'All input fields are required']);
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <style>
        body { font-family: 'Arial', sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .form-container { background: white; border-radius: 8px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); padding: 30px; width: 90%; max-width: 400px; }
        h2 { text-align: center; color: #333; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; transition: border-color 0.3s; }
        input:focus { border-color: #007bff; outline: none; }
        button { width: 100%; padding: 12px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.3s; }
        button:hover { background-color: #0056b3; }
        .footer-text { text-align: center; margin-top: 15px; }
        .footer-text a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Register</h2>
        <form action="" method="POST">
            <input type="text" name="firstname" placeholder="First Name" required>
            <input type="text" name="lastname" placeholder="Last Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
        </form>
        <p class="footer-text">Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>
