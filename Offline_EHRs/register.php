<?php

// You can add PHP code here if needed for session or auth

?>

<!DOCTYPE html>

<html lang="en">

<head>

  <meta charset="UTF-8" />

  <title>Register - EHR System</title>

  <style>

    /* Navbar styling */

    nav {

      background-color: #007bff;

      padding: 12px 30px;

      font-family: Arial, sans-serif;

      font-weight: bold;

    }

    nav a {

      color: white;

      margin-right: 25px;

      text-decoration: none;

      font-size: 18px;

    }

    nav a:hover {

      text-decoration: underline;

    }

    nav a.active {

      text-decoration: underline;

    }

    /* Container */

    .container {

      width: 600px;

      background-color: #fff;

      margin: 40px auto 60px auto;

      padding: 30px 40px 40px 40px;

      border-radius: 10px;

      box-shadow: 0 0 20px rgba(0,0,0,0.1);

      font-family: Arial, sans-serif;

    }

    h2 {

      text-align: center;

      color: #333;

      margin-bottom: 30px;

    }

    /* Buttons to toggle forms */

    .toggle-buttons {

      text-align: center;

      margin-bottom: 30px;

    }

    .toggle-buttons button {

      padding: 12px 25px;

      margin: 0 15px;

      font-size: 16px;

      cursor: pointer;

      background-color: #007bff;

      color: white;

      border: none;

      border-radius: 6px;

      transition: background-color 0.3s;

    }

    .toggle-buttons button:hover {

      background-color: #0056b3;

    }

    /* Form styling */

    form label {

      display: block;

      font-weight: bold;

      margin-top: 15px;

    }

    form input, form select {

      width: 100%;

      padding: 9px;

      margin-top: 6px;

      border-radius: 5px;

      border: 1px solid #ccc;

      font-size: 15px;

    }

    form input[type="number"] {

      -moz-appearance: textfield; /* To remove arrows in Firefox */

    }

    form input[type=number]::-webkit-inner-spin-button,

    form input[type=number]::-webkit-outer-spin-button {

      -webkit-appearance: none;

      margin: 0;

    }

    form button.submit-btn {

      margin-top: 25px;

      width: 100%;

      padding: 12px;

      background-color: #007bff;

      color: white;

      font-size: 17px;

      border: none;

      border-radius: 7px;

      cursor: pointer;

    }

    form button.submit-btn:hover {

      background-color: #0056b3;

    }

    /* Hide forms initially */

    #doctorForm, #patientForm {

      display: none;

    }

  </style>

</head>

<body>

<!-- Navbar -->

<nav>

  <a href="dashboard.html">Home</a>

  <a href="about.php">About Us</a>

  <a href="register.php" class="active">Register</a>

  <a href="login.php">Login</a>

</nav>

<!-- Main container -->

<div class="container">

  <h2>Register</h2>

  <div class="toggle-buttons">

    <button id="showDoctorFormBtn">Doctor Registration</button>

    <button id="showPatientFormBtn">Patient Registration</button>

  </div>

  <!-- Doctor Registration Form -->

  <form id="doctorForm" method="post" action="doctor_register.php">

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

    <button type="submit" class="submit-btn">Register Doctor</button>

  </form>

  <!-- Patient Registration Form -->

 <form id="patientForm" method="post" action="patient_register.php">

  <label for="patientId">Patient ID</label>
  <input type="text" id="patientId" name="patientId" required>

  <label for="patientName">Full Name</label>
  <input type="text" id="patientName" name="patientName" required>

  <label for="patientDob">Date of Birth</label>
  <input type="date" id="patientDob" name="patientDob" required>

  <label for="patientGender">Gender</label>
  <select id="patientGender" name="patientGender" required>
    <option value="" disabled selected>Select gender</option>
    <option value="Male">Male</option>
    <option value="Female">Female</option>
    <option value="Other">Other</option>
  </select>

  <!-- Added Blood Group field -->
  <label for="bloodGroup">Blood Group</label>
  <select id="bloodGroup" name="bloodGroup" required>
    <option value="" disabled selected>Select blood group</option>
    <option value="A+">A+</option>
    <option value="A-">A-</option>
    <option value="B+">B+</option>
    <option value="B-">B-</option>
    <option value="AB+">AB+</option>
    <option value="AB-">AB-</option>
    <option value="O+">O+</option>
    <option value="O-">O-</option>
  </select>

  <label for="contactNumber">Contact Number</label>
  <input type="tel" id="contactNumber" name="contactNumber" required>

  <label for="emailPatient">Email Address</label>
  <input type="email" id="emailPatient" name="emailPatient" required>

  <label for="address">Address</label>
  <input type="text" id="address" name="address" required>

  <label for="passwordPatient">Password</label>
  <input type="password" id="passwordPatient" name="passwordPatient" required>

  <label for="confirmPasswordPatient">Confirm Password</label>
  <input type="password" id="confirmPasswordPatient" name="confirmPasswordPatient" required>

  <button type="submit" class="submit-btn">Register Patient</button>

</form>

</div>

<script>

  const doctorBtn = document.getElementById('showDoctorFormBtn');

  const patientBtn = document.getElementById('showPatientFormBtn');

  const doctorForm = document.getElementById('doctorForm');

  const patientForm = document.getElementById('patientForm');

  // Show doctor form by default

  doctorBtn.style.backgroundColor = '#0056b3'; // active button color

  doctorForm.style.display = 'block';

  doctorBtn.addEventListener('click', () => {

    doctorForm.style.display = 'block';

    patientForm.style.display = 'none';

    doctorBtn.style.backgroundColor = '#0056b3';

    patientBtn.style.backgroundColor = '#007bff';

  });

  patientBtn.addEventListener('click', () => {

    patientForm.style.display = 'block';

    doctorForm.style.display = 'none';

    patientBtn.style.backgroundColor = '#0056b3';

    doctorBtn.style.backgroundColor = '#007bff';

  });

</script>

</body>

</html>

