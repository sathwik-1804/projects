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

if (!isset($_SESSION['patient_id'])) {
    header("Location: patient_login.php");
    exit();
}

$patient_id = $_SESSION['patient_id'];
$doctor_id = '';
$hospital_name = '';
$errors = [];
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = $_POST['doctor_id'] ?? '';
    $hospital_name = $_POST['hospital_name'] ?? '';

    // Server-side validation
    if (empty($doctor_id)) {
        $errors[] = "Doctor ID is required.";
    } elseif (!ctype_digit($doctor_id)) {
        $errors[] = "Doctor ID must contain digits only.";
    }

    if (empty($hospital_name)) {
        $errors[] = "Hospital Name is required.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $hospital_name)) {
        $errors[] = "Hospital Name must contain letters and spaces only.";
    }

    // Check if doctor exists in doctors table
    if (empty($errors)) {
        $check_doctor = $conn->prepare("SELECT doctor_id FROM doctors WHERE doctor_id = ?");
        $check_doctor->bind_param("s", $doctor_id);
        $check_doctor->execute();
        $check_doctor->store_result();
        if ($check_doctor->num_rows === 0) {
            $errors[] = "Doctor ID not found in database.";
        }
        $check_doctor->close();
    }

    if (empty($errors)) {
        // Insert permission
        $stmt = $conn->prepare("INSERT INTO record_permissions (patient_id, doctor_id, hospital_name) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $patient_id, $doctor_id, $hospital_name);

        if ($stmt->execute()) {
            // Insert message for doctor
            $link = "view_patient_profile.php?patient_id=" . urlencode($patient_id);
            $message = "You have been granted access to patient records. Patient ID: <a href='$link'>" . htmlspecialchars($patient_id) . "</a>.";

            $stmt_msg = $conn->prepare("INSERT INTO doctor_messages (doctor_id, message) VALUES (?, ?)");
            $stmt_msg->bind_param("ss", $doctor_id, $message);
            $stmt_msg->execute();
            $stmt_msg->close();

            $success_msg = "Permission granted successfully.";
            $doctor_id = '';
            $hospital_name = '';
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Grant Record Permission</title>
<style>
 body {
  font-family: Arial, sans-serif;
  background-color: #f0f4f8;
  padding: 20px;
 }
 .container {
  max-width: 420px;
  margin: 40px auto;
  background: white;
  padding: 25px 30px;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
 }
 label {
  display: block;
  color: #004080;
  font-weight: bold;
  margin-bottom: 6px;
  font-size: 15px;
 }
 input[type="text"] {
  width: 100%;
  padding: 9px 10px;
  margin-bottom: 18px;
  border: 2px solid #004080;
  border-radius: 6px;
  background-color: white;
  color: #004080;
  font-size: 16px;
  box-sizing: border-box;
  transition: border-color 0.3s ease;
 }
 input[type="text"]:focus {
  border-color: #007bff;
  outline: none;
 }
 button {
  background-color: #004080;
  color: white;
  border: none;
  padding: 11px 22px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 16px;
  width: 100%;
 }
 button:hover {
  background-color: #0066cc;
 }
 .errors {
  background-color: #d9534f;
  color: white;
  padding: 15px 20px;
  border-radius: 6px;
  margin-bottom: 20px;
 }
 .success {
  background-color: #5cb85c;
  color: white;
  padding: 15px 20px;
  border-radius: 6px;
  margin-bottom: 20px;
  text-align: center;
  font-weight: bold;
 }
 .go-back {
  text-align: center;
  margin-top: 15px;
 }
 .go-back a {
  color: #007BFF;
  text-decoration: none;
  font-weight: bold;
 }
 .go-back a:hover {
  text-decoration: underline;
 }
</style>
</head>
<body>
 <div class="container">
  <h2 style="color:#004080; text-align:center; margin-bottom: 25px;">Grant Record Permission</h2>

  <?php if (!empty($errors)): ?>
   <div class="errors">
    <?php foreach ($errors as $error): ?>
     <p><?php echo htmlspecialchars($error); ?></p>
    <?php endforeach; ?>
   </div>
  <?php endif; ?>

  <?php if ($success_msg): ?>
   <div class="success">
    <?php echo htmlspecialchars($success_msg); ?>
   </div>
   <div class="go-back">
    <a href="patient_dashboard.php">Go Back to Dashboard</a>
   </div>
  <?php endif; ?>

  <form method="POST" action="">
   <label for="doctor_id">Doctor ID:</label>
   <input
    type="text"
    id="doctor_id"
    name="doctor_id"
    required
    pattern="\d+"
    title="Doctor ID must contain digits only"
    value="<?php echo htmlspecialchars($doctor_id); ?>"
   >
   <label for="hospital_name">Hospital Name:</label>
   <input
    type="text"
    id="hospital_name"
    name="hospital_name"
    required
    pattern="[A-Za-z\s]+"
    title="Hospital Name must contain letters and spaces only"
    value="<?php echo htmlspecialchars($hospital_name); ?>"
   >
   <button type="submit">Grant Permission</button>
  </form>
 </div>
</body>
</html>
