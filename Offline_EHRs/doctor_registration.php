<?php
$host = "localhost";
$user = "root";
$pass = "sathwik@1804";
$dbname = "ehr_system";
$conn = new mysqli($host, $user, $pass, $dbname);
$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $doctorId = $_POST["doctorId"];
  $fullName = $_POST["fullName"];
  $hospitalName = $_POST["hospitalName"];
  $dob = $_POST["dob"];
  $gender = $_POST["gender"];
  $email = $_POST["email"];
  $specialization = $_POST["specialization"];
  $department = $_POST["department"];
  $designation = $_POST["designation"];
  $experience = $_POST["experience"];
  $password = $_POST["password"];
  $confirmPassword = $_POST["confirmPassword"];
  if ($password !== $confirmPassword) {
    $message = "<p style='color:red; text-align:center;'>❌ Passwords do not match.</p>";
  } else {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO doctors (doctor_id, full_name, hospital_name, dob, gender, email, specialization, department, designation, experience, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssis", $doctorId, $fullName, $hospitalName, $dob, $gender, $email, $specialization, $department, $designation, $experience, $hashedPassword);
    if ($stmt->execute()) {
      $message = "<p style='color:green; text-align:center;'>✅ Doctor registered successfully!</p>";
    } else {
      $message = "<p style='color:red; text-align:center;'>❌ Error: " . $conn->error . "</p>";
    }
  }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

  <meta charset="UTF-8" />

  <title>Doctor Registration</title>

  <style>

    body {

      font-family: Arial, sans-serif;

      background-color: #eef2f3;

    }

    .container {

      width: 450px;

      background-color: #fff;

      margin: 50px auto;

      padding: 30px;

      border-radius: 10px;

      box-shadow: 0 0 20px rgba(0,0,0,0.1);

    }

    h2 {

      text-align: center;

      color: #333;

      margin-bottom: 25px;

    }

    label {

      display: block;

      font-weight: bold;

      margin-top: 12px;

    }

    input, select {

      width: 100%;

      padding: 8px;

      margin-top: 5px;

      border-radius: 5px;

      border: 1px solid #ccc;

    }

    button {

      width: 100%;

      padding: 10px;

      margin-top: 20px;

      background-color: #007bff;

      color: white;

      font-size: 16px;

      border: none;

      border-radius: 5px;

    }

    button:hover {

      background-color: #0069d9;

    }

    .back-link {

      display: block;

      margin-top: 20px;

      text-align: center;

      font-weight: bold;

      text-decoration: none;

      color: #007bff;

    }

    .back-link:hover {

      color: #0056b3;

    }

  </style>

</head>

<body>

  <div class="container">

    <h2>Doctor Registration</h2>

    <?php echo $message; ?>

    <form method="post" action="">

      <label for="doctorId">Doctor ID</label>

      <input type="text" id="doctorId" name="doctorId" required>

      <label for="fullName">Full Name</label>

      <input type="text" id="fullName" name="fullName" required>

      <label for="hospitalName">Hospital Name</label>

      <input type="text" id="hospitalName" name="hospitalName" required>

      <label for="dob">Date of Birth</label>

      <input type="date" id="dob" name="dob" required>

      <label for="gender">Gender</label>

      <select id="gender" name="gender" required>

        <option value="" disabled selected>Select gender</option>

        <option value="Male">Male</option>

        <option value="Female">Female</option>

        <option value="Other">Other</option>

      </select>

      <label for="email">Email Address</label>

      <input type="email" id="email" name="email" required>

      <label for="specialization">Specialization</label>

      <input type="text" id="specialization" name="specialization" required>

      <label for="department">Department</label>

      <input type="text" id="department" name="department" required>

      <label for="designation">Designation</label>

      <input type="text" id="designation" name="designation" required>

      <label for="experience">Work Experience (in years)</label>

      <input type="number" id="experience" name="experience" min="0" required>

      <label for="password">Password</label>

      <input type="password" id="password" name="password" required>

      <label for="confirmPassword">Confirm Password</label>

      <input type="password" id="confirmPassword" name="confirmPassword" required>

      <button type="submit">Register</button>

    </form>

    <a href="register.php" class="back-link">&larr; Back to Registration Selection</a>

  </div>

</body>

</html>