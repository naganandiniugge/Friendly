<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = new mysqli("localhost", "root", "", "friendly_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check required fields
    $required_fields = ['surname', 'firstname', 'dob', 'gender', 'email', 'password'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            die("❌ Missing: $field");
        }
    }

    // Sanitize inputs
    $surname = $conn->real_escape_string(trim($_POST['surname']));
    $firstname = $conn->real_escape_string(trim($_POST['firstname']));
    $lastname = isset($_POST['lastname']) ? $conn->real_escape_string(trim($_POST['lastname'])) : null;
    $dob = $conn->real_escape_string($_POST['dob']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Validate age
    $birthdate = new DateTime($dob);
    $today = new DateTime();
    $age = $today->diff($birthdate)->y;
    if ($age < 15) {
        die("❌ You must be at least 15 years old.");
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        die("❌ Email already registered.");
    }
    $stmt->close();

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (surname, firstname, lastname, dob, gender, email, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $surname, $firstname, $lastname, $dob, $gender, $email, $password);

    if ($stmt->execute()) {
        header("Location: webpage.html");
        exit();
    } else {
        die("❌ DB Error: " . $stmt->error);
    }

    $stmt->close();
} else {
    die("❌ Invalid request method.");
}

$conn->close();
?>
