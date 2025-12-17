<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "sathwik@1804";
$dbname = "ehr_system";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_input = trim($_POST["login"]); // can be email or patient_id
    $password = $_POST["password"];

    // Query to find by email OR patient_id
    $stmt = $conn->prepare("SELECT patient_id, password FROM patient WHERE email = ? OR patient_id = ?");
    $stmt->bind_param("ss", $login_input, $login_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Verify password (assuming hashed)
        if (password_verify($password, $row['password'])) {
            $_SESSION['patient_id'] = $row['patient_id'];
            header("Location: patient_dashboard.php");
            exit();
        } else {
            $message = "Incorrect password.";
        }
    } else {
        $message = "Email or Patient ID not found.";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Patient Login</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f8fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: white;
            padding: 40px 50px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            width: 350px;
            text-align: center;
        }
        h2 {
            margin-bottom: 30px;
            color: #2c3e50;
        }
        label {
            display: block;
            text-align: left;
            margin-bottom: 8px;
            font-weight: 600;
            color: #34495e;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 1.5px solid #d1d9e6;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #2980b9;
            outline: none;
        }
        button {
            width: 100%;
            padding: 14px;
            background-color: #2980b9;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-weight: bold;
        }
        button:hover {
            background-color: #1c5980;
        }
        p.message {
            margin-top: 15px;
            font-weight: 600;
        }
        p.error {
            color: #e74c3c;
        }
        p.success {
            color: #27ae60;
        }
        /* Forgot password link */
        .forgot-password {
            margin-top: 15px;
            text-align: center;
        }
        .forgot-password a {
            color: #2980b9;
            text-decoration: none;
            font-weight: 600;
        }
        .forgot-password a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Patient Login</h2>
        <?php 
            if (!empty($message)) {
                $class = strpos($message, 'Incorrect') !== false || strpos($message, 'not found') !== false ? 'error' : 'success';
                echo "<p class='message $class'>$message</p>";
            }
        ?>
        <form method="POST" action="">
            <label for="login">Email or Patient ID:</label>
            <input type="text" id="login" name="login" required autocomplete="username">

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">

            <button type="submit">Login</button>
        </form>
        <div class="forgot-password">
            <a href="patient_forgot_password.php">Forgot Password?</a>
        </div>
    </div>
</body>
</html>
