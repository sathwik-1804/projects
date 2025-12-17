<?php
$host = "localhost";
$user = "root";
$pass = "2580";
$dbname = "ehr_system";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? "";

    if (!empty($email)) {
        // Generate a secure token
        $token = bin2hex(random_bytes(16));

        // Save token to patient table
        $stmt = $conn->prepare("UPDATE patient SET reset_token = ? WHERE email = ?");
        $stmt->bind_param("ss", $token, $email);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // ✅ Reset link format
            $resetLink = "http://localhost/ehr/patient_reset_password.php?token=" . urlencode($token);
            echo "<p style='color:green;'>✅ Reset link generated successfully!</p>";
            echo "<p><a href='$resetLink'>$resetLink</a></p>";
        } else {
            echo "<p style='color:red;'>❌ Email not found or token not saved.</p>";
        }

        $stmt->close();
    } else {
        echo "<p style='color:red;'>❌ Please enter an email.</p>";
    }
}

$conn->close();
?>

<!-- Simple form to enter email -->
<!DOCTYPE html>
<html>
<head>
    <title>Patient Reset Link</title>
</head>
<body>
    <h2>Request Password Reset</h2>
    <form method="POST" action="">
        <label for="email">Enter your email:</label><br>
        <input type="email" name="email" required>
        <br><br>
        <button type="submit">Get Reset Link</button>
    </form>
</body>
</html>