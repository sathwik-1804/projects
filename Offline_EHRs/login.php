<!DOCTYPE html>
<html>
<head>
    <title>Login Selection - Secure EHR</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f8ff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        h1 {
            color: #004aad;
            margin-bottom: 40px;
        }

        .button {
            display: block;
            width: 200px;
            text-align: center;
            background-color: #004aad;
            color: white;
            padding: 14px;
            margin: 10px 0;
            text-decoration: none;
            font-size: 16px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #00338d;
        }
    </style>
</head>
<body>
    <h1>Login as</h1>
    <a href="doctor_login.php" class="button">Doctor</a>
    <a href="patient_login.php" class="button">Patient</a>
</body>
</html>