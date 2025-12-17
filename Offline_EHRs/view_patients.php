<?php
// MySQL database connection information
$servername = "localhost";
$username = "root";
$password = "sathwik@1804";  // Your MySQL password
$dbname = "ehr_system";

// Create a new connection to the MySQL database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search input
$search = "";
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $sql = "SELECT * FROM patients WHERE name LIKE '%$search%' OR disease LIKE '%$search%'";
} else {
    $sql = "SELECT * FROM patients";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Records</title>
    <style>
        body {
            background-color: #0d1117;
            color: #c9d1d9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px;
        }
        h1 {
            color: #ffffff;
            margin-bottom: 20px;
        }
        form {
            margin-bottom: 20px;
        }
        input[type="text"] {
            padding: 5px 10px;
            font-size: 16px;
        }
        input[type="submit"] {
            padding: 5px 15px;
            font-size: 16px;
            background-color: #58a6ff;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        table {
            border-collapse: collapse;
            width: 90%;
            max-width: 1000px;
            background-color: #161b22;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #30363d;
        }
        th {
            background-color: #21262d;
            color: #58a6ff;
        }
        tr:hover {
            background-color: #21262d;
        }
        td {
            color: #f0f6fc;
        }
        .actions a {
            margin-right: 10px;
            color: #58a6ff;
            text-decoration: none;
        }
        .actions a:hover {
            text-decoration: underline;
        }
        .dashboard-link {
            margin-top: 20px;
            background-color: #58a6ff;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
        .dashboard-link:hover {
            background-color: #0077cc;
        }
    </style>
</head>
<body>
    <h1>Patient Records</h1>
    <form method="GET" action="">
        <input type="text" name="search" placeholder="Search by name or disease" value="<?php echo htmlspecialchars($search); ?>">
        <input type="submit" value="Search">
    </form>

<?php
if ($result->num_rows > 0) {
    echo "<table>
    <tr>
        <th>Patient ID</th>
        <th>Name</th>
        <th>Age</th>
        <th>Gender</th>
        <th>Disease</th>
        <th>Prescription</th>
        <th>Actions</th>
    </tr>";

    while($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>" . htmlspecialchars($row["patient_id"]) . "</td>
            <td>" . htmlspecialchars($row["name"]) . "</td>
            <td>" . htmlspecialchars($row["age"]) . "</td>
            <td>" . htmlspecialchars($row["gender"]) . "</td>
            <td>" . htmlspecialchars($row["disease"]) . "</td>
            <td>" . htmlspecialchars($row["prescription"]) . "</td>
            <td class='actions'>
                <a href='edit_patient.php?patient_id=" . urlencode($row["patient_id"]) . "'>Edit</a>
                <a href='delete_patient.php?patient_id=" . urlencode($row["patient_id"]) . "' onclick=\"return confirm('Are you sure you want to delete this record?');\">Delete</a>
            </td>
        </tr>";
    }

    echo "</table>";
} else {
    echo "<p>No records found.</p>";
}
?>

<a href='dashboard.php' class='dashboard-link'>← Go to Dashboard</a>
</body>
</html>
