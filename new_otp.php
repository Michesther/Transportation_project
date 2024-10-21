<?php
session_start();
require_once 'db_connect.php';
require 'vendor/autoload.php'; // Include Composer's autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_COOKIE['users_email'])) {
        $email = $_COOKIE['users_email'];

        // Generate a new OTP
        $new_otp = mt_rand(1111, 9999);

        // Update the OTP in the database
        $sql = "UPDATE users SET otp = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $new_otp, $email);

        if ($stmt->execute()) {
            // Send the new OTP to the user's email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; 
                $mail->SMTPAuth = true;
                $mail->Username = 'emekaisaacisreal@gmail.com'; // Replace with your email
                $mail->Password = 'oxekwyjnkgpodzgb'; // Replace with your app password
                $mail->Port = 587;

                $mail->setFrom('emekaisaacisreal@gmail.com', 'GEO SOLUTION');
                $mail->addAddress($email);
                $mail->Subject = 'New OTP Verification';
                $mail->Body = 'Your new OTP code is: ' . $new_otp . '. Please use this code to verify your email.';

                $mail->send();

                // Respond with a success message
                echo json_encode(['success' => true, 'message' => 'A new OTP has been sent to your email.']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => "Email could not be sent. Error: {$mail->ErrorInfo}"]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating OTP.']);
        }

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'No email found for the current session.']);
    }
}
?>
