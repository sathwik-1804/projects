<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Database connection
$host = "localhost";
$user = "root";
$pass = "sathwik@1804";
$dbname = "ehr_system";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function sendPasswordResetMail($toEmail, $toName, $token) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ehrsecure123@gmail.com';
        $mail->Password   = 'spcaqkvidufprkuk'; // App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Optional for XAMPP
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];

        $mail->setFrom('ehrsecure123@gmail.com', 'EHR System');
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'EHR System Password Reset';
        $mail->Body    = "
            Hi $toName,<br><br>
            Click below to reset your password:<br>
            <a href='http://localhost/ehr/reset_password.php?token=$token'>Reset Password</a><br><br>
            This link is valid for 1 hour.<br><br>
            Regards,<br>EHR System
        ";

        $mail->send();
        return "<p class='success'>✅ Reset link sent to your email!</p>";
    } catch (Exception $e) {
        return "<p class='error'>❌ Failed to send email. {$mail->ErrorInfo}</p>";
    }
}

// Handle form submission
if (isset($_POST['email'])) {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT doctor_id, full_name FROM doctors WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($doctor_id, $full_name);
        $stmt->fetch();

        $token = bin2hex(random_bytes(50));
        $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

        $insert = $conn->prepare("INSERT INTO password_resets (doctor_id, token, expiry) VALUES (?, ?, ?)");
        $insert->bind_param("sss", $doctor_id, $token, $expiry);
        $insert->execute();

        $message = sendPasswordResetMail($email, $full_name, $token);
    } else {
        $message = "<p class='error'>❌ Email not found!</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password - EHR System</title>
<style>
body { font-family: 'Segoe UI', sans-serif; background:#f4f7f8; display:flex; justify-content:center; align-items:center; height:100vh; margin:0;}
.forgot-password-form { background:#fff; padding:40px 35px; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.1); width:380px; text-align:center; }
.forgot-password-form h2 { margin-bottom:25px; color:#333; font-size:24px; }
.forgot-password-form label { display:block; text-align:left; font-weight:600; margin-bottom:5px; color:#555; }
.forgot-password-form input[type="email"] { width:100%; padding:12px 15px; margin-bottom:20px; border:1px solid #ccc; border-radius:8px; box-sizing:border-box; font-size:14px; }
.forgot-password-form input[type="email"]:focus { border-color:#4CAF50; outline:none; }
.forgot-password-form button { background-color:#4CAF50; color:white; padding:12px 25px; border:none; border-radius:8px; cursor:pointer; font-size:16px; }
.forgot-password-form button:hover { background-color:#45a049; }
.forgot-password-form .success { color:green; font-weight:bold; margin-bottom:15px; }
.forgot-password-form .error { color:red; font-weight:bold; margin-bottom:15px; }
@media(max-width:420px) { .forgot-password-form { width:90%; padding:30px 25px; } }
</style>
</head>
<body>

<div class="forgot-password-form">
    <h2>Forgot Password</h2>
    <?php if(isset($message)) echo $message; ?>
    <form method="POST">
        <label for="email">Enter your registered email:</label>
        <input type="email" name="email" id="email" required placeholder="ehrsecure123@gmail.com">
        <button type="submit">Send Reset Link</button>
    </form>
</div>

</body>
</html>
