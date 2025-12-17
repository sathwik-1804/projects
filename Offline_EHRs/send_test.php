<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function trySMTP($secure, $port) {
    $mail = new PHPMailer(true);
    echo "<h3>Testing $secure on port $port</h3>";
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ehrsecure123@gmail.com';   // your Gmail
        $mail->Password   = 'spcaqkvidufprkuk';        // your Gmail App Password
        $mail->SMTPSecure = $secure;
        $mail->Port       = $port;

        $mail->SMTPDebug  = 0;

        // Optional (XAMPP SSL bypass)
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];

        $mail->setFrom('ehrsecure123@gmail.com', 'EHR System Test');
        $mail->addAddress('yourpersonalemail@gmail.com', 'Test Receiver'); // change to your real test inbox

        $mail->isHTML(true);
        $mail->Subject = "PHPMailer Test ($secure on $port)";
        $mail->Body    = "This is a test email using <b>$secure</b> on port <b>$port</b>.";

        $mail->send();
        echo "<p style='color:green;'>✅ Success! Email sent using $secure on port $port</p><hr>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>❌ Failed: {$mail->ErrorInfo}</p><hr>";
    }
}

// Try TLS on 587
trySMTP(PHPMailer::ENCRYPTION_STARTTLS, 587);

// Try SSL on 465
trySMTP(PHPMailer::ENCRYPTION_SMTPS, 465);
