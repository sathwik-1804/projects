<!DOCTYPE html>
<html>
<head>
  <title>EHR Dashboard</title>
  <style>
    body {
      font-family: Arial;
      background-color: #0d1117;
      color: #ffffff;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }

    h1 {
      margin-bottom: 40px;
    }

    a {
      background-color: #2ea043;
      color: white;
      padding: 15px 30px;
      margin: 10px;
      text-decoration: none;
      font-weight: bold;
      border-radius: 8px;
      transition: background-color 0.3s ease;
    }

    a:hover {
      background-color: #238636;
    }
  </style>
</head>
<body>

  <h1>Electronic Health Record System</h1>
  
  <!-- Patient Links -->
  <a href="add_patient_form.php">Add Patient Record</a>
  <a href="view_patients.php">Show Patient List</a>

  <!-- Doctor Links -->
  <a href="doctors_registration.php">Add New Doctor</a>

</body>
</html>
