<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background-color: #f7f7f7;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            display: inline-block;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        a.button {
            display: block;
            padding: 15px 25px;
            margin: 20px 0;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 18px;
        }
        a.button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <p>Select how you want to reset your password:</p>
        <a class="button" href="doctor_forgot_password.php">Reset via Email (Online)</a>
        <a class="button" href="doctor_forgot_password_sms.php">Reset via SMS (Offline)</a>
    </div>
</body>
</html>
