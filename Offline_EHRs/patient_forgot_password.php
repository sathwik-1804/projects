<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$host = "localhost";
$user = "root";
$pass = "sathwik@1804";
$dbname = "ehr_system";

$conn = new mysqli($host, $user, $pass, $dbname);
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $stmt = $conn->prepare("SELECT patient_id FROM patient WHERE email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $token = bin2hex(random_bytes(16));
        $expire = date("Y-m-d H:i:s", strtotime("+15 minutes"));
        $update = $conn->prepare("UPDATE patient SET reset_token = ?, token_expire = ? WHERE email = ?");
        if (!$update) {
            die("Prepare failed: " . $conn->error);
        }
        $update->bind_param("sss", $token, $expire, $email);
        $update->execute();

        $resetLink = "http://localhost/ehr/patient_reset_password.php?token=$token";

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ehrsecure@gmail.com';       // Your Gmail
            $mail->Password = 'ibat rxak rwup hkkn';        // Your Gmail App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('ehrsecure@gmail.com', 'EHR System');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Patient Password Reset Request';
            $mail->Body = "
                <p>Hello,</p>
                <p>You requested to reset your password. Click the link below:</p>
                <p><a href='$resetLink'>$resetLink</a></p>
                <p>This link will expire in 15 minutes. If you didn't request this, please ignore this email.</p>
            ";

            $mail->send();
            $message = "<p class='success'>✅ A password reset link has been sent to your email address.</p>";
        } catch (Exception $e) {
            $message = "<p class='error'>❌ Message could not be sent. Mailer Error: {$mail->ErrorInfo}</p>";
        }
    } else {
        $message = "<p class='error'>❌ Email not found.</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Patient Forgot Password</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f7f8;
      margin: 0;
      padding: 0;
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
      margin-bottom: 20px;
    }
    label {
      font-weight: bold;
      display: block;
      margin-top: 15px;
      color: #555;
    }
    input[type="email"] {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 5px;
      box-sizing: border-box;
      font-size: 14px;
    }
    button {
      width: 100%;
      padding: 12px;
      margin-top: 20px;
      background-color: #007bff;
      border: none;
      color: white;
      font-size: 16px;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: #0056b3;
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
    <h2>Forgot Password</h2>
    <?php echo $message; ?>
    <form method="POST" action="">
      <label for="email">Registered Email Address</label>
      <input type="email" name="email" id="email" required>
      <button type="submit">Send Reset Link</button>
    </form>
  </div>
</body>
</html>
