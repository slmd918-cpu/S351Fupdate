<?php
session_start();

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$conn = mysqli_connect("localhost", "root", "", "php_shopping app");

function sendpwEmail($customerEmail)
{
     $mail = new PHPMailer(true);
     try {
        // DONT MODIFY !!!!!!!!!!!!
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        $mail->Username = 'tyteamwork2324@gmail.com';
        $mail->Password = 'hfiowkemjflayhsc';
        $mail->setFrom('tyteamwork2324@gmail.com', 'HKMU Shopping App');

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->Timeout = 30;

        $mail->addAddress($customerEmail);
         // DONT MODIFY !!!!!!!!!!!!

        $mail->isHTML(true);
        $mail->Subject = 'HKMU Shopping App - Reset Password Request';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; color:#222;'>
                <p>Dear customer</p>
                <p>Your request of reseting your Password was successful and here is the Password reset link.</p>
                <a href = 'http://localhost/Shopping-App-Update-main/home/reset.php'>Click This </a>
                <p>Thank you for using HKMU Shopping App.</p>
            </div>
        ";
        $mail->send();
            return true;
    }
    catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}

if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $emailResult = sendpwEmail($email);
     if ($emailResult === true) {
              header("Location: http://localhost/Shopping-App-Update-main/home/success.php"); 
            } 
    else {
                header("Location: http://localhost/Shopping-App-Update-main/forget.php");
    }
}            




?>
<form action="" method="POST">
<div class="my_form">
    <meta charset="utf-8">
    <h2>Forget Password</h2>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">

    <div class="input_deg">
        <label>Email</label>
        <input type="email" name="email" placeholder="Email"required>
    </div>
    <button type="submit" name="submit" class="login_btn_main">Submit</button>
</div>
</form>
