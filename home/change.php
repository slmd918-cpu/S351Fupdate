<?php
$conn = mysqli_connect("localhost", "root", "", "php_shopping app");

if (isset($_POST['submit'])) {

    $oldpw = mysqli_real_escape_string($conn, $_POST['old_password']);
    $newpw = mysqli_real_escape_string($conn, $_POST['new_password']);
    $verifypw = mysqli_real_escape_string($conn, $_POST['verify_password']);

    
    if ($newpw !== $verifypw) {
        header("Location: change.php?error=nomatch");
        exit();
    }

   
    $sql = "SELECT * FROM users WHERE password='$oldpw'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {

      
        $update = "UPDATE users SET password='$newpw' WHERE password='$oldpw'";
        if (mysqli_query($conn, $update)) {
            header("Location: success.php");
            exit();
        }

    } else {
        header("Location: change.php?error=wrongoldpw");
        exit();
    }
}
?>
<form action="" method="POST">
<div class="my_form">
    <meta charset="utf-8">
    <h2>Change Password</h2>
    <link rel="stylesheet" type="text/css" href="../style.css">
    <div class="input_deg">
        <label>Old Password</label>
        <input type="password" name="old_password" required>
    </div>

    <div class="input_deg">
        <label>New Password</label>
        <input type="password" name="new_password" required>
    </div>

    <div class="input_deg">
        <label>Verify New Password</label>
        <input type="password" name="verify_password" required>
    </div>

    <button type="submit" name="submit" class="login_btn_main">Submit</button>
</div>
</form>