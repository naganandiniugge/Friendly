<?php
if (!isset($_GET['token'])) {
    die("No token provided.");
}

$token = $_GET['token'];

$conn = new mysqli("localhost", "root", "", "friendly_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT email, reset_expiry FROM users WHERE reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $email = $row['email'];
    $expiry = strtotime($row['reset_expiry']); // ✅

    if (time() > $expiry) {
        die("Token has expired.");
    }
} else {
    die("Invalid token.");
}

$stmt->close();
$conn->close();
?>

<!-- Show reset password form -->
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Your Password</h2>
    <form action="update_password.php" method="POST">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
        <label>New Password:</label>
        <input type="password" name="password" required>
        <br><br>
        <button type="submit">Update Password</button>
    </form>
</body>
</html>
