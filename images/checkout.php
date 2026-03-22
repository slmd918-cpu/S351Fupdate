<?php
session_start();

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$conn = mysqli_connect("localhost", "root", "", "php_shopping app");

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$total = 0;
$count = 0;

foreach ($_SESSION['cart'] as $item) {
    $count += $item['qty'];
    $total += $item['price'] * $item['qty'];
}

$shipping = 0.00;
$discount = 0.00;
$grand_total = $total + $shipping - $discount;

$message = "";

function sendOrderEmail($customerEmail, $customerName, $cartItems, $grandTotal)
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

        $itemRowsHtml = "";
        $itemRowsText = "";

        foreach ($cartItems as $item) {
            $subtotal = $item['price'] * $item['qty'];

            $itemName = htmlspecialchars($item['name']);
            $qty = (int)$item['qty'];
            $unitPrice = number_format((float)$item['price'], 2);
            $subtotalFormatted = number_format($subtotal, 2);

            $itemRowsHtml .= "
                <tr>
                    <td style='padding:8px; border:1px solid #ddd;'>{$itemName}</td>
                    <td style='padding:8px; border:1px solid #ddd; text-align:center;'>{$qty}</td>
                    <td style='padding:8px; border:1px solid #ddd; text-align:right;'>HKD {$unitPrice}</td>
                    <td style='padding:8px; border:1px solid #ddd; text-align:right;'>HKD {$subtotalFormatted}</td>
                </tr>
            ";

            $itemRowsText .= "{$item['name']} | Qty: {$qty} | Unit Price: HKD {$unitPrice} | Subtotal: HKD {$subtotalFormatted}\n";
        }

        $totalFormatted = number_format($grandTotal, 2);

        $mail->isHTML(true);
        $mail->Subject = 'HKMU Shopping App - Order Confirmation';

        $mail->Body = "
            <div style='font-family: Arial, sans-serif; color:#222;'>
                <h2>Thank you for your purchase, " . htmlspecialchars($customerName) . "!</h2>
                <p>Your payment was successful and your order has been received.</p>

                <h3>Order Summary</h3>
                <table style='border-collapse:collapse; width:100%; max-width:700px;'>
                    <thead>
                        <tr style='background:#f5f5f5;'>
                            <th style='padding:8px; border:1px solid #ddd; text-align:left;'>Item</th>
                            <th style='padding:8px; border:1px solid #ddd;'>Quantity</th>
                            <th style='padding:8px; border:1px solid #ddd; text-align:right;'>Unit Price</th>
                            <th style='padding:8px; border:1px solid #ddd; text-align:right;'>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$itemRowsHtml}
                    </tbody>
                </table>

                <p style='margin-top:16px; font-size:18px;'>
                    <strong>Total Payment: HKD {$totalFormatted}</strong>
                </p>

                <p>Thank you for buying from HKMU Shopping App.</p>
            </div>
        ";

        $mail->AltBody =
            "Thank you for your purchase, {$customerName}!\n\n" .
            "Your payment was successful and your order has been received.\n\n" .
            "Order Summary:\n" .
            $itemRowsText . "\n" .
            "Total Payment: HKD {$totalFormatted}\n\n" .
            "Thank you for buying from HKMU Shopping App.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}

if (isset($_POST['place_order'])) {
    if (!empty($_SESSION['cart'])) {
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Please enter a valid email address.";
            return;
        }
        $phone = trim($_POST['phone']);
        $region = trim($_POST['region']);
        $method = trim($_POST['method']);
        $address = trim($_POST['address']);
        $remark = trim($_POST['remark']);
        $payment = isset($_POST['payment']) ? trim($_POST['payment']) : '';

        if (
            $fullname != "" &&
            $email != "" &&
            $phone != "" &&
            $region != "" &&
            $method != "" &&
            $payment != ""
        ) {
            $cartCopy = $_SESSION['cart'];
            $conn = mysqli_connect("localhost", "root", "", "php_shopping app");

            $sql = "INSERT INTO orders (customer_name, customer_email, total_amount)
                    VALUES ('$fullname', '$email', '$grand_total')";
            mysqli_query($conn, $sql);

            $order_id = mysqli_insert_id($conn);

            foreach ($cartCopy as $item) {
                $name = $item['name'];
                $price = $item['price'];
                $qty = $item['qty'];

             $sql = "INSERT INTO order_items (order_id, product_name, price, quantity)
                        VALUES ('$order_id', '$name', '$price', '$qty')";
             mysqli_query($conn, $sql);
            }


            $emailResult = sendOrderEmail($email, $fullname, $cartCopy, $grand_total);

            if ($emailResult === true) {
                $message = "Order placed successfully! A confirmation email has been sent to your email address.";
            } else {
                $message = "Order placed successfully, but the confirmation email could not be sent. Error: " . $emailResult;
            }

            $_SESSION['cart'] = [];
            $count = 0;
            $total = 0;
            $shipping = 0.00;
            $discount = 0.00;
            $grand_total = 0.00;
        } else {
            $message = "Please complete all required fields.";
        }
    } else {
        $message = "Your cart is empty.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

<nav>
    <input type="checkbox" id="check">

    <label for="check" class="checkbtn">
        <i class="fa fa-bars"></i>
    </label>

    <label class="my_logo">HKMU Shopping App</label>

    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="index.php#products">Products</a></li>
        <li><a href="cart.php">Cart (<?php echo $count; ?>)</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<main>
    <div class="checkout_page">
        <div class="breadcrumb">
            <a href="cart.php">Shopping Cart</a> <span>&gt;</span> <span>Checkout</span>
        </div>

        <h1 class="checkout_title">Checkout</h1>

        <?php if ($message != "") { ?>
            <div class="checkout_message_box">
                <p class="success_msg"><?php echo htmlspecialchars($message); ?></p>
                <a class="cart_checkout_btn" href="index.php">Back to Home</a>
            </div>
        <?php } else { ?>

            <?php if (empty($_SESSION['cart'])) { ?>
                <div class="checkout_message_box">
                    <p>Your cart is empty.</p>
                    <br>
                    <a class="cart_checkout_btn" href="index.php">Go Shopping</a>
                </div>
            <?php } else { ?>

            <form method="POST" action="">
                <div class="checkout_layout">

                    <div class="checkout_left">
                        <div class="checkout_card">

                            <?php foreach ($_SESSION['cart'] as $item) {
                                $subtotal = $item['price'] * $item['qty'];
                            ?>
                            <div class="checkout_item">
                                <div class="checkout_item_left">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="checkout_item_img">
                                </div>

                                <div class="checkout_item_center">
                                    <div class="checkout_item_name"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="checkout_item_tags">
                                        <span class="tag_box">HKMU</span>
                                        <span class="tag_new">NEW</span>
                                    </div>
                                </div>

                                <div class="checkout_item_qty">
                                    × <?php echo (int)$item['qty']; ?>
                                </div>

                                <div class="checkout_item_price">
                                    <span>HKD</span>
                                    <strong><?php echo number_format($subtotal, 2); ?></strong>
                                </div>
                            </div>
                            <?php } ?>

                            <div class="checkout_total_box">
                                <div class="checkout_total_row">
                                    <span>Subtotal:</span>
                                    <span>HKD <?php echo number_format($total, 2); ?></span>
                                </div>

                                <div class="checkout_total_row">
                                    <span>Shipping Fee:</span>
                                    <span>HKD <?php echo number_format($shipping, 2); ?></span>
                                </div>

                                <div class="checkout_total_row">
                                    <span>Discount:</span>
                                    <span>- HKD <?php echo number_format($discount, 2); ?></span>
                                </div>

                                <div class="checkout_total_row total_main">
                                    <span>Total:</span>
                                    <span>HKD <?php echo number_format($grand_total, 2); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="checkout_right">
                        <div class="checkout_card">
                            <div class="billing_title">Billing Information</div>

                            <div class="billing_form_group">
                                <label><span class="required_star">*</span> Full Name</label>
                                <input type="text" name="fullname" required>
                            </div>

                            <div class="billing_form_group">
                                <label><span class="required_star">*</span> Email</label>
                                <input type="email" name="email" required>
                            </div>

                            <div class="billing_form_group">
                                <label><span class="required_star">*</span> Contact Number</label>
                                <input type="text" name="phone" placeholder="Required" required>
                            </div>

                            <div class="billing_form_group">
                                <label><span class="required_star">*</span> Delivery Information</label>
                                <select name="region" required>
                                    <option value="">Select Region</option>
                                    <option value="Hong Kong Island">Hong Kong Island</option>
                                    <option value="Kowloon">Kowloon</option>
                                    <option value="New Territories">New Territories</option>
                                </select>
                            </div>

                            <div class="billing_form_group">
                                <select name="method" required>
                                    <option value="">Please select a delivery method</option>
                                    <option value="Self Pick-up">Self Pick-up</option>
                                    <option value="Standard Delivery">Standard Delivery</option>
                                    <option value="Express Delivery">Express Delivery</option>
                                </select>
                            </div>

                            <div class="billing_form_group">
                                <label>Address (Optional)</label>
                                <input type="text" name="address" placeholder="If applicable">
                            </div>

                            <div class="billing_form_group">
                                <label>Remark</label>
                                <input type="text" name="remark" placeholder="If applicable">
                            </div>

                            <div class="billing_form_group">
                                <label>Payment Information</label>

                                <div class="payment_grid">
                                    <label class="payment_option">
                                        <input type="radio" name="payment" value="Octopus" required>
                                        <span>Octopus</span>
                                    </label>

                                    <label class="payment_option">
                                        <input type="radio" name="payment" value="AlipayHK" required>
                                        <span>AlipayHK</span>
                                    </label>

                                    <label class="payment_option">
                                        <input type="radio" name="payment" value="PayMe" required>
                                        <span>PayMe</span>
                                    </label>

                                    <label class="payment_option">
                                        <input type="radio" name="payment" value="FPS" required>
                                        <span>FPS Transfer</span>
                                    </label>

                                    <label class="payment_option">
                                        <input type="radio" name="payment" value="Cash" required>
                                        <span>Cash</span>
                                    </label>

                                    <label class="payment_option">
                                        <input type="radio" name="payment" value="Credit Card" required>
                                        <span>Visa / Master Card</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" name="place_order" class="place_order_btn">
                            Place Order
                        </button>
                    </div>

                </div>
            </form>

            <?php } ?>
        <?php } ?>
    </div>
</main>

</body>
</html>