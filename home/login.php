<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = mysqli_connect("localhost", "root", "", "php_shopping app");
/* 如果你之後改返自己 database，例如 php_shopping_app，就改上面呢行 */

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$message = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        $_SESSION['user_id'] = $row['hkmuid'];
        $_SESSION['name'] = $row['name'];
        $_SESSION['usertype'] = $row['usertype'];

        if ($row['usertype'] == "admin") {
            header("Location: ../admin/adminpage.php");
            exit();
        } else {
            header("Location: userpage.php");
            exit();
        }
    } else {
        $message = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../style.css">
</head>
<body>

<main class="login_page">
    <div class="login_box">
        <h1 class="login_title">Welcome Back, Please Sign In</h1>
        <p class="login_subtitle">Welcome to HKMU Shopping App</p>

        <?php if ($message != "") { ?>
            <div class="login_error"><?php echo $message; ?></div>
        <?php } ?>

        <form action="" method="POST">
            <div class="login_group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Email" required>
            </div>

            <div class="login_group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <div class="login_remember">
                <input type="checkbox" id="remember">
                <label for="remember">Remember me</label>
            </div>

            <button type="submit" name="login" class="login_btn_main">Login</button>

            <a href="register.php" class="login_btn_second">Create New Account</a>
        </form>
        <div class="login_links">
            <a href="http://localhost/Shopping-App-Update-main/forget.php">Forget password?</a>
        </div>
        <div class="login_links">
            <a href="chpw.php">Change password?</a>
            
        </div>
        
    </div>
</main>

</body>
</html>