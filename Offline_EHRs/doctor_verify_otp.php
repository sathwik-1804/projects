<?php
$host="localhost"; $user="root"; $pass="sathwik@1804"; $dbname="ehr_system";
$conn=new mysqli($host,$user,$pass,$dbname);
$phone=$_GET['phone']; $message="";

if(isset($_POST['verify_otp'])){
    $entered_otp=$_POST['otp'];
    $res=$conn->query("SELECT * FROM otp_verification WHERE phone_number='$phone' AND otp='$entered_otp' ORDER BY id DESC LIMIT 1");
    if($res->num_rows>0){
        $row=$res->fetch_assoc();
        if(strtotime($row['expiry_time'])>=time()){
            header("Location: doctor_reset_password_sms.php?phone=$phone"); exit();
        }else{ $message="OTP expired. Request new one."; }
    }else{ $message="Invalid OTP."; }
}
?>

<h2>Verify OTP</h2>
<?php echo $message; ?>
<form method="POST">
<label>Enter OTP:</label>
<input type="text" name="otp" required>
<button type="submit" name="verify_otp">Verify OTP</button>
