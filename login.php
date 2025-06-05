<?php
session_start();

// Connect to DB
$conn = new mysqli("localhost", "root", "", "friendly_db");

// Check DB connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get and sanitize input
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$password = $_POST['password'];

// Validate inputs
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Please enter a valid email address.");
}

if (empty($password)) {
    die("Please enter your password.");
}

// Look up user by email
$stmt = $conn->prepare("SELECT id, password FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($user_id, $hashed_password);
    $stmt->fetch();

    // Verify password
    if (password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $user_id;
        header("Location: webpage.html");
        exit;
    } else {
        // More user-friendly error message
        die("The email or password you entered is incorrect. Please try again or <a href='forgot_password.php'>reset your password</a>.");
    }
} else {
    // Generic message to prevent email enumeration
    die("The email or password you entered is incorrect. Please try again or <a href='forgot_password.php'>reset your password</a>.");
}

$stmt->close();
$conn->close();
?>