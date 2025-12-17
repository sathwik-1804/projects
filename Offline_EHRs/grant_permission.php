<?php

session_start();

if (!isset($_SESSION['patient_id'])) {

  header("Location: patient_login.php");

  exit();

}

?>

<!DOCTYPE html>

<html>

<head>

 <title>Grant Record Access</title>

 <style>

  body { font-family: Arial; background-color: #eef; text-align: center; padding: 50px; }

  form { background: white; padding: 30px; display: inline-block; border-radius: 10px; }

  input, button { padding: 10px; margin: 10px; width: 80%; }

  button { background-color: #004aad; color: white; border: none; cursor: pointer; }

  button:hover { background-color: #00338d; }

 </style>

</head>

<body>

 <h2>Grant Record Access to Doctor</h2>

 <form method="POST" action="grant_permission_process.php">

  <input type="text" name="doctor_id" placeholder="Enter Doctor ID" required><br>

  <input type="text" name="hospital_name" placeholder="Enter Hospital Name" required><br>

  <button type="submit">Grant Permission</button>

 </form>

</body>

</html>



