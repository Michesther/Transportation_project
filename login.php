<?php
session_start();

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $session_id = session_id();
    $user_id = bin2hex(random_bytes(6));

    // Check if the user exists and fetch user details
    $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Start session and store session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];

            // Store IP, user agent, session ID in the sessions table
            $stmt = $conn->prepare("INSERT INTO sessions (ip_address, browser_agent, session_id, user_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $ip_address, $user_agent, $session_id, $user['id']);
            $stmt->execute();

            echo "Login successful! Redirecting...";
            header("Location: ./goat/index.htm"); // Redirect to a dashboard or home page
            exit;
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with that email.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body { font-family: 'Arial', sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .form-container { background: white; border-radius: 8px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); padding: 30px; width: 90%; max-width: 400px; }
        h2 { text-align: center; color: #333; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; transition: border-color 0.3s; }
        input:focus { border-color: #007bff; outline: none; }
        button { width: 100%; margin-top : 20px; padding: 12px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.3s; }
        button:hover { background-color: #0056b3; }
        .footer-text { text-align: center; margin-top: 15px; }
        .footer-text a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Login</h2>
        <form action="" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p class="footer-text">Don't have an account? <a href="register.php">Register here</a></p>
        <p class="footer-text"><a href="forgot_password.php">Forgot your password?</a></p>
    </div>
</body>
</html>
