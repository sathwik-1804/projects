<?php
session_start();
if (!isset($_SESSION["doctor_id"])) {
    header("Location: doctor_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Doctor Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo $_SESSION["full_name"]; ?>!</h2>
    <p>Your Doctor ID: <?php echo $_SESSION["doctor_id"]; ?></p>
    <a href="logout.php">Logout</a>
</body>
</html>
