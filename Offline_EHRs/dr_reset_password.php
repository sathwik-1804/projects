<?php
$host = "localhost";
$user = "root";
$pass = "sathwik@1804";
$dbname = "ehr_system";

$conn = new mysqli($host, $user, $pass, $dbname);
$message = "";

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// When form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["token"])) {
    $token = $_POST["token"];
    $newPassword = $_POST["new_password"];
    $confirmPassword = $_POST["confirm_password"];

    if ($newPassword !== $confirmPassword) {
        $message = "<p class='error'>❌ Passwords do not match.</p>";
    } else {
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update password and clear reset_token
        $stmt = $conn->prepare("UPDATE doctors SET password = ?, reset_token = NULL WHERE reset_token = ?");
        $stmt->bind_param("ss", $hashedPassword, $token);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $message = "<p class='success'>✅ Password has been reset successfully. You can now <a href='doctor_login.php'>login</a>.</p>";
            $token = null; // to hide the form after success
        } else {
            $message = "<p class='error'>❌ Invalid or expired reset link.</p>";
        }

        $stmt->close();
    }
}
// When token is clicked from email link
elseif (isset($_GET["token"])) {
    $token = $_GET["token"];

    // Verify token exists and is valid
    $stmt = $conn->prepare("SELECT doctor_id FROM doctors WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        $message = "<p class='error'>❌ Invalid or expired reset link.</p>";
        $token = null;
    }

    $stmt->close();
} else {
    $message = "<p class='error'>❌ No reset token provided.</p>";
    $token = null;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f8;
        }
        .container {
            width: 400px;
            margin: 80px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background-color: #28a745;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
        }
        button:hover {
            background-color: #218838;
        }
        .success {
            color: green;
            font-size: 14px;
            margin-top: 15px;
            text-align: center;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-top: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Reset Password</h2>
    <?php echo $message; ?>

    <?php if (!empty($token)) : ?>
    <form method="POST" action="">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

        <label for="new_password">New Password</label>
        <input type="password" name="new_password" id="new_password" required>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required>

        <button type="submit">Reset Password</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
