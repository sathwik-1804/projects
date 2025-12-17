<?php
session_start();

// Check if user is logged in and role is set
if (!isset($_SESSION['role'])) {
    header("Location: login.php"); // Or whichever login page you use
    exit();
}

$role = $_SESSION['role'];
$username = ''; // For display, doctor_id or patient_id

if ($role === 'doctor' && isset($_SESSION['doctor_id'])) {
    $username = $_SESSION['doctor_id'];
} elseif ($role === 'patient' && isset($_SESSION['patient_id'])) {
    $username = $_SESSION['patient_id'];
} else {
    // Session invalid or missing info
    header("Location: login.php");
    exit();
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $option = $_POST['verification_option'] ?? '';

    if ($option === 'email') {
        // Set a session var or flag to know user chose email OTP
        $_SESSION['verify_method'] = 'email';

        // Redirect to email OTP send page or process here
        header("Location: send_email_otp.php");
        exit();

    } elseif ($option === 'totp') {
        $_SESSION['verify_method'] = 'totp';

        // Redirect to TOTP verification page
        header("Location: verify_totp.php");
        exit();
    } else {
        $message = "Please select a verification method.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Choose Verification Method</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef6ff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 350px;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            color: #004aad;
        }
        .message {
            color: red;
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin: 15px 0 5px;
            font-weight: bold;
            color: #333;
            text-align: left;
        }
        input[type="radio"] {
            margin-right: 10px;
        }
        button {
            margin-top: 25px;
            background-color: #004aad;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            width: 100%;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #00338d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Hello, <?php echo htmlspecialchars($username); ?></h2>
        <p>Please choose a verification method to continue:</p>
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <label>
                <input type="radio" name="verification_option" value="email">
                Verify via Email OTP (online)
            </label>
            <label>
                <input type="radio" name="verification_option" value="totp">
                Verify via Authenticator App (TOTP - offline)
            </label>
            <button type="submit">Continue</button>
        </form>
    </div>
</body>
</html>
