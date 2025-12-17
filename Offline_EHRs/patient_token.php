<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // PHPMailer autoload

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // TODO: Validate $email format and check if it exists in doctors table

    // Generate a secure random token
    $token = bin2hex(random_bytes(16));
    $expires_at = date("Y-m-d H:i:s", strtotime('+1 hour'));

    // Save token, email, and expiry in DB
    // Use prepared statements to avoid SQL injection
    $pdo = new PDO("mysql:host=localhost;dbname=your_db", "user", "password");
    $stmt = $pdo->prepare("INSERT INTO password_resets (doctor_email, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$email, $token, $expires_at]);

    // Prepare reset link
    $resetLink = "http://yourdomain.com/reset_password.php?token=$token";

    // Send email using PHPMailer
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com'; // your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email@example.com';
        $mail->Password = 'your_email_password';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        //Recipients
        $mail->setFrom('no-reply@yourdomain.com', 'EHR Support');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body = "Dear Doctor,<br><br>
            Please click the following link to reset your password:<br>
            <a href='$resetLink'>$resetLink</a><br><br>
            This link will expire in 1 hour.<br><br>
            If you did not request this, please ignore this email.";

        $mail->send();
        echo "Password reset email sent.";
    } catch (Exception $e) {
        echo "Mailer Error: " . $mail->ErrorInfo;
    }
}
?>
