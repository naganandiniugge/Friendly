<?php
session_start();

// 1. Connect to database
$conn = new mysqli("localhost", "root", "", "friendly_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Get form data
$email = $_POST['email'];
$password = $_POST['password'];

// Check if email or password is missing
if (empty($email) || empty($password)) {
    die("Missing email or password.");
}

// 3. Hash the new password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 4. Update password in database
$sql = "UPDATE users SET password=? WHERE email=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $hashed_password, $email);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "Password updated successfully.";
    } else {
        echo "No account found with that email, or the password was already set to this value.";
    }
} else {
    echo "Error updating password: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
