<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add New Patient</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #0d1117;
      color: #ffffff;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      min-height: 100vh;
      padding-top: 40px;
    }

    h1 {
      color: #ffffff;
      margin-bottom: 20px;
      text-align: center;
    }

    form {
      background-color: #161b22;
      color: #ffffff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.4);
      width: 90%;
      max-width: 500px;
    }

    label {
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      color: #c9d1d9;
    }

    input, select, textarea {
      width: 100%;
      padding: 10px;
      margin-bottom: 20px;
      background-color: #21262d;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      color: #ffffff;
    }

    input::placeholder, textarea::placeholder {
      color: #8b949e;
    }

    input[type="submit"] {
      background-color: #2ea043;
      color: #ffffff;
      border: none;
      cursor: pointer;
      font-size: 16px;
      font-weight: bold;
      transition: background-color 0.3s ease;
    }

    input[type="submit"]:hover {
      background-color: #238636;
    }

    .back-link {
      margin-top: 20px;
      display: inline-block;
      text-decoration: none;
      color: #58a6ff;
      font-weight: bold;
    }

    .back-link:hover {
      text-decoration: underline;
    }
  </style>

  <script>
    function validateForm() {
      const name = document.getElementById("name").value;
      const age = document.getElementById("age").value;
      const gender = document.getElementById("gender").value;
      const disease = document.getElementById("disease").value;
      const prescription = document.getElementById("prescription").value;
      const password = document.getElementById("password").value;
      const confirmPassword = document.getElementById("confirm_password").value;

      if (!name || !age || !gender || !disease || !prescription || !password || !confirmPassword) {
        alert("All fields must be filled out!");
        return false;
      }

      if (password !== confirmPassword) {
        alert("Passwords do not match!");
        return false;
      }

      return true;
    }
  </script>
</head>
<body>

  <h1>Patient Records</h1>

  <form action="submit_patient.php" method="POST" onsubmit="return validateForm()">
    <label for="patient_id">Patient ID:</label>
    <input type="text" id="patient_id" name="patient_id" placeholder="e.g., 123456" required />

    <label for="reg_date">Date of Registration:</label>
    <input type="date" id="reg_date" name="reg_date" value="<?php echo date('Y-m-d'); ?>" readonly />

    <label for="name">Name:</label>
    <input type="text" id="name" name="name" placeholder="Enter patient name" required />

    <label for="age">Age:</label>
    <input type="number" id="age" name="age" placeholder="Enter age" required />

    <label for="gender">Gender:</label>
    <select id="gender" name="gender" required>
      <option value="">Select</option>
      <option value="Male">Male</option>
      <option value="Female">Female</option>
      <option value="Other">Other</option>
    </select>

    <label for="disease">Disease:</label>
    <input type="text" id="disease" name="disease" placeholder="Enter disease" required />

    <label for="prescription">Prescription:</label>
    <textarea id="prescription" name="prescription" rows="4" placeholder="Enter prescription" required></textarea>

    <input type="submit" value="Submit" />
  </form>

  <a class="back-link" href="dashboard.php">← Back to Dashboard</a>

</body>
</html>
