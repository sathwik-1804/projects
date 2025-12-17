<?php
session_start();
// Assume doctor is logged in and $doctor_id is in session
if (!isset($_SESSION['doctor_id'])) {
    header("Location: login.php");
    exit();
}

$doctor_id = $_SESSION['doctor_id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Doctor Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f4f8;
            padding: 40px;
            text-align: center;
        }
        h1 {
            color: #004080;
            margin-bottom: 30px;
        }
        .nav-buttons {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            max-width: 300px;
            margin: 0 auto;
        }
        .btn {
            background-color: #004080;
            color: white;
            border: none;
            padding: 15px 0;
            font-size: 20px;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: background-color 0.3s ease, transform 0.2s ease;
            text-decoration: none;
            width: 100%;
            max-width: 300px;
            display: inline-block;
        }
        .btn:hover {
            background-color: #0066cc;
            transform: translateY(-3px);
        }
        .btn:active {
            transform: translateY(0);
            box-shadow: none;
        }
        .btn.logout {
            background-color: #cc3300;
        }
        .btn.logout:hover {
            background-color: #e64c33;
        }
        @media(max-width: 400px) {
            .nav-buttons {
                max-width: 90%;
            }
            .btn {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <h1>Welcome to Your Doctor Dashboard</h1>
    <div class="nav-buttons">
        <a href="profile.php" class="btn">View Profile</a>
        <a href="messages.php" class="btn">Patient Messages</a>
        <a href="prescriptions_list.php" class="btn">View Prescriptions</a>
        <a href="Consultancy prescriptions.php" class="btn"> Consultancy prescription</a>
        <a href="logout.php" class="btn logout">Logout</a>
    </div>
</body>
</html>



