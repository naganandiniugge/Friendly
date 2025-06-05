<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Make sure Composer's autoload is available

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    $conn = new mysqli("localhost", "root", "", "friendly_db");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the email exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // Update token and expiry
        $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE email = ?");
        $stmt->bind_param("sss", $token, $expiry, $email);
        $stmt->execute();

        $reset_link = "http://localhost/Nandini/reset_password.php?token=$token";
        // ✅ Now we know $email is valid — proceed to send mail
        $mail = new PHPMailer(true);

        try {
            // SMTP settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'nandininaga21@gmail.com';      // Replace with your email
            $mail->Password   = 'gvvl utut mqux vkql';         // Use App Password (not your real password)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Email setup
            $mail->setFrom('your-email@gmail.com', 'Your App Name');
            $mail->addAddress($email); // ✅ Now $email is defined and valid
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "Click the link to reset your password:\n\n$reset_link";

            $mail->send();
            echo "Password reset email sent to $email.";
        } catch (Exception $e) {
            echo "Error sending email: {$mail->ErrorInfo}";
        }
    } else {
        echo "Email not found in our records.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!-- HTML Form -->
<form method="POST" action="forgot_password.php">
    <label for="email">Enter your email:</label>
    <input type="email" name="email" required>
    <button type="submit">Send Reset Link</button>
</form>
