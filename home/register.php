<?php
$conn = mysqli_connect("localhost", "root", "", "php_shopping app");

if (!$conn) {
    die("Database connection failed.");
}

$message = "";

if (isset($_POST['register'])) {
    $u_role = $_POST['role'];
    $u_name = $_POST['name'];
    $u_id = $_POST['hkmuid'];
    $u_email = $_POST['email'];
    $u_password = $_POST['password'];
    $usertype = "user";

    $check = "SELECT * FROM users WHERE hkmuid='$u_id' OR email='$u_email'";
    $check_result = mysqli_query($conn, $check);

    if (mysqli_num_rows($check_result) > 0) {
        $message = "This ID or email already exists.";
    } else {
        $sql = "INSERT INTO users (role, name, hkmuid, email, password, usertype)
                VALUES ('$u_role','$u_name','$u_id','$u_email','$u_password','$usertype')";

        $data = mysqli_query($conn, $sql);

        if ($data) {
            $message = "Register success.";
        } else {
            $message = "Register failed.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Register Form</title>
    <link rel="stylesheet" type="text/css" href="../style.css">
</head>
<body>

<div class="my_form">
    <h2>Register Form</h2>

    <?php if ($message != "") { ?>
        <div class="input_deg">
            <p><?php echo $message; ?></p>
        </div>
    <?php } ?>

    <form action="" method="POST">
        <div class="input_deg">
            <label>I am a...</label>
            <select name="role" required>
                <option value="" disabled selected>Select your role</option>
                <option value="student">Student</option>
                <option value="staff">Staff / Faculty</option>
                <option value="alumni">Alumni</option>
            </select>
        </div>

        <div class="input_deg">
            <label>Name :</label>
            <input type="text" name="name" required>
        </div>

        <div class="input_deg">
            <label>ID :</label>
            <input type="number" name="hkmuid" required>
        </div>

        <div class="input_deg">
            <label>Email :</label>
            <input type="email" name="email" required>
        </div>

        <div class="input_deg">
            <label>Password :</label>
            <input type="password" name="password" required>
        </div>

        <div class="input_deg">
            <input type="submit" name="register" value="Register">
        </div>
    </form>

    <div class="input_deg">
        <a href="login.php">Go to Login</a>
    </div>
</div>

</body>
</html>