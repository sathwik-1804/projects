<?php
$host="localhost"; $user="root"; $pass="sathwik@1804"; $dbname="ehr_system";
$conn=new mysqli($host,$user,$pass,$dbname);
$message="";

if(isset($_POST['send_otp'])){
    $phone=$_POST['phone'];
    $check=$conn->query("SELECT * FROM doctors WHERE phone='$phone'");
    if($check->num_rows>0){
        $otp=rand(100000,999999);
        $expiry=date("Y-m-d H:i:s",strtotime("+5 minutes"));
        $conn->query("INSERT INTO otp_verification(phone_number,otp,expiry_time) VALUES('$phone','$otp','$expiry')");
        $message="<p class='success'>OTP Sent to your phone (Simulated): $otp <br><a href='doctor_verify_otp.php?phone=$phone'>Verify OTP</a></p>";
    }else{
        $message="<p class='error'>Phone number not registered.</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password via SMS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h2 {
            color: #004aad;
            margin-bottom: 25px;
        }
        label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
            font-weight: bold;
            color: #004aad;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #004aad;
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #00338d;
        }
        .success {
            color: green;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
        }
        a {
            color: #004aad;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        p.back-link {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Forgot Password via SMS</h2>
        <?php echo $message; ?>
        <form method="POST">
            <label>Enter Registered Phone Number:</label>
            <input type="text" name="phone" required>
            <button type="submit" name="send_otp">Send OTP</button>
        </form>
        <p class="back-link"><a href="doctor_forgot_password_choice.php">← Back</a></p>
    </div>
</body>
</html>
