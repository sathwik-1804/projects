<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = "localhost";
$user = "root";
$pass = "sathwik@1804";
$dbname = "ehr_system";

$conn = new mysqli($host, $user, $pass, $dbname);
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // Sanitize and fetch POST data
  $patientId = trim($_POST["patientId"] ?? '');
  $fullName = trim($_POST["fullName"] ?? '');
  $dob = $_POST["dob"] ?? '';
  $gender = $_POST["gender"] ?? '';
  $email = trim($_POST["email"] ?? '');
  $address = trim($_POST["address"] ?? '');
  $phone = trim($_POST["phone"] ?? '');
  $bloodGroup = $_POST["bloodGroup"] ?? '';
  $password = $_POST["password"] ?? '';
  $confirmPassword = $_POST["confirmPassword"] ?? '';

  // Basic validation
  if (
    empty($patientId) || empty($fullName) || empty($dob) || empty($gender) ||
    empty($email) || empty($address) || empty($phone) || empty($bloodGroup) ||
    empty($password) || empty($confirmPassword)
  ) {
    $message = "<p style='color:red; text-align:center;'>❌ Please fill in all required fields.</p>";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $message = "<p style='color:red; text-align:center;'>❌ Invalid email format.</p>";
  } elseif ($password !== $confirmPassword) {
    $message = "<p style='color:red; text-align:center;'>❌ Passwords do not match.</p>";
  } elseif (strlen($password) < 8) {
    $message = "<p style='color:red; text-align:center;'>❌ Password must be at least 8 characters.</p>";
  } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
    $message = "<p style='color:red; text-align:center;'>❌ Phone number must be exactly 10 digits.</p>";
  } else {
    $stmt_check = $conn->prepare("SELECT patient_id FROM patients WHERE patient_id = ? OR email = ?");
    $stmt_check->bind_param("ss", $patientId, $email);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
      $message = "<p style='color:red; text-align:center;'>❌ Patient ID or Email already registered.</p>";
    } else {
      $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

      $stmt = $conn->prepare("INSERT INTO patients (patient_id, full_name, dob, gender, email, address, phone, blood_group, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("sssssssss", $patientId, $fullName, $dob, $gender, $email, $address, $phone, $bloodGroup, $hashedPassword);

      if ($stmt->execute()) {
        $message = "<p style='color:green; text-align:center;'>✅ Patient registered successfully!</p>";
      } else {
        $message = "<p style='color:red; text-align:center;'>❌ Registration failed. Please try again later.</p>";
      }

      $stmt->close();
    }

    $stmt_check->close();
  }

  $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Patient Registration</title>
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
    input, select, textarea {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
      border-radius: 5px;
      border: 1px solid #ccc;
      box-sizing: border-box;
    }
    textarea {
      resize: vertical;
    }
    button {
      width: 100%;
      padding: 10px;
      margin-top: 20px;
      background-color: #28a745;
      color: white;
      font-size: 16px;
      border: none;
      border-radius: 5px;
    }
    button:hover {
      background-color: #218838;
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
    <h2>Patient Registration</h2>
    <?php echo $message; ?>
    <form method="post" action="">
      <label for="patientId">Patient ID</label>
      <input type="text" id="patientId" name="patientId" required>

      <label for="fullName">Full Name</label>
      <input type="text" id="fullName" name="fullName" required>

      <label for="dob">Date of Birth</label>
      <input type="date" id="dob" name="dob" required>

      <label for="gender">Gender</label>
      <select id="gender" name="gender" required>
        <option value="">-- Select Gender --</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
        <option value="Other">Other</option>
      </select>

      <label for="email">Email Address</label>
      <input type="email" id="email" name="email" required>

      <label for="address">Address</label>
      <textarea id="address" name="address" rows="3" required></textarea>

      <label for="phone">Phone Number</label>
      <input type="tel" id="phone" name="phone" required pattern="[0-9]{10}" title="Enter 10 digit phone number">

      <label for="bloodGroup">Blood Group</label>
      <select id="bloodGroup" name="bloodGroup" required>
        <option value="">-- Select Blood Group --</option>
        <option value="A+">A+</option>
        <option value="A-">A-</option>
        <option value="B+">B+</option>
        <option value="B-">B-</option>
        <option value="AB+">AB+</option>
        <option value="AB-">AB-</option>
        <option value="O+">O+</option>
        <option value="O-">O-</option>
      </select>

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
