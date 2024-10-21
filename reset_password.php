<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $otp = $_POST['otp'];
    $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Verify the OTP
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND otp = ?");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Update the user's password and reset the OTP
        $stmt = $conn->prepare("UPDATE users SET password = ?, otp = NULL WHERE id = ?");
        $stmt->bind_param("si", $new_password, $user['id']);
        $stmt->execute();

        echo "Password has been reset successfully!";

        header('Location: login.php');
        exit;
    } else {
        echo "Invalid OTP.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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
        <h2>Reset Password</h2>
        <form action="" method="POST">
            <!-- Pre-fill the email input with the email from the query string -->
            <input type="email" name="email" value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>" placeholder="Email" required>
            <input type="text" name="otp" placeholder="Enter OTP" required>
            <input type="password" name="password" placeholder="New Password" required>
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>
