<!DOCTYPE html>
<html>
<head>
    <title>Doctor Login - Secure EHR</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f8ff;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background-color: #ffffff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 74, 173, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h2 {
            color: #004aad;
            margin-bottom: 30px;
        }

        label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
            font-weight: bold;
            color: #004aad;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            background-color: #004aad;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #00338d;
        }

        .forgot-link {
            margin-top: 10px;
            display: block;
            text-align: right;
        }

        .forgot-link a {
            color: #004aad;
            text-decoration: none;
            font-size: 14px;
        }

        .forgot-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Doctor Login</h2>
        <form method="POST" action="doctor_login_process.php">
            <label>Email or Doctor ID:</label>
            <input type="text" name="identifier" required>

            <label>Password:</label>
            <input type="password" name="password" required>

           <button type="submit">Login</button>

            <div class="forgot-link">
                <a href="doctor_forgot_password_choice.php">Forgot Password?</a>
            </div>
    </form>
    </div>
</body>
</html>