<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("location:login.php");
    exit();
}

if ($_SESSION['usertype'] != "user") {
    header("location:../admin/adminpage.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>User Page</title>
    <link rel="stylesheet" type="text/css" href="../style.css">
</head>
<body>

<nav>
    <label class="my_logo">HKMU Shopping App</label>
    <ul>
        <li><a href="../index.php">Home</a></li>
        <li><a href="../cart.php">Cart</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</nav>

<div class="checkout_box">
    <h2>Welcome User</h2>
    <p style="text-align:center; margin-bottom:20px;">
        You are logged in successfully.
    </p>
    <div style="text-align:center;">
        <a class="normal_btn" href="../index.php">Go Shopping</a>
    </div>
</div>

</body>
</html>