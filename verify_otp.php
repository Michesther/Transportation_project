<?php
require_once 'db_connect.php';

// Get the OTP from the request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $otp = htmlspecialchars($_POST['otp']);

    // Query to fetch the stored OTP from the database
    $sql = "SELECT * FROM users WHERE otp = ?"; // assuming 'otp' is the column name
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if OTP matches
    if ($result->num_rows > 0) {
        // OTP matched
        $response = [
            'success' => true,
            'message' => 'OTP verified successfully!',
        ];
    } else {
        // OTP didn't match
        $response = [
            'success' => false,
            'message' => 'Invalid OTP. Please try again.',
        ];
    }

    // Return response in JSON format
    echo json_encode($response);
}
?>
