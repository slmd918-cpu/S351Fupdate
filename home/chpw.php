<?php
$conn = mysqli_connect("localhost", "root", "", "php_shopping app");


if (isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $ccemail = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $ccemail);
    if (mysqli_num_rows($result) > 0) {
        header("Location: ../home/change.php");
    
    }
    else{
    	header("Location: ../home/forgetpw.php");
    	exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Change password</title>
    <link rel="stylesheet" type="text/css" href="../style.css">
</head>
<body>
	<form action="" method="POST">
	<div class="my_form">
    			<h2>Change Password</h2>
    		<div class="input_deg">
                <label>Email</label>
                <input type="text" name="email" placeholder="Email" required>
            </div>
            <button type="submit" name="submit" class="login_btn_main">submit</button>
</form>
</body>
<html>
