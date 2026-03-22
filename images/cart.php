<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* Add to cart / Buy now */
if (isset($_POST['add_to_cart']) || isset($_POST['buy_now'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = (float)$_POST['price'];
    $image = $_POST['image'];

    $found = false;

    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $id) {
            $_SESSION['cart'][$key]['qty'] += 1;
            $found = true;
            break;
        }
    }

    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $id,
            'name' => $name,
            'price' => $price,
            'image' => $image,
            'qty' => 1
        ];
    }

    if (isset($_POST['buy_now'])) {
        header("Location: checkout.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

/* Increase quantity */
if (isset($_GET['increase'])) {
    $index = (int)$_GET['increase'];

    if (isset($_SESSION['cart'][$index])) {
        $_SESSION['cart'][$index]['qty'] += 1;
    }

    header("Location: cart.php");
    exit();
}

/* Decrease quantity */
if (isset($_GET['decrease'])) {
    $index = (int)$_GET['decrease'];

    if (isset($_SESSION['cart'][$index])) {
        $_SESSION['cart'][$index]['qty'] -= 1;

        if ($_SESSION['cart'][$index]['qty'] <= 0) {
            unset($_SESSION['cart'][$index]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }
    }

    header("Location: cart.php");
    exit();
}

/* Remove item */
if (isset($_GET['remove'])) {
    $index = (int)$_GET['remove'];

    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }

    header("Location: cart.php");
    exit();
}

/* Clear cart */
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    header("Location: cart.php");
    exit();
}

$count = 0;
$total = 0;

foreach ($_SESSION['cart'] as $item) {
    $count += $item['qty'];
    $total += $item['price'] * $item['qty'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shopping Cart</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
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
        <li><a href="home/login.php">LogOut</a></li>
    </ul>
</nav>

<div class="cart_page">
    <div class="breadcrumb">
        <a href="index.php">Home</a> <span>&gt;</span> <span>Shopping Cart</span>
    </div>

    <h1 class="cart_main_title">Shopping Cart</h1>

    <div class="cart_top_action">
        <?php if (!empty($_SESSION['cart'])) { ?>
            <a href="cart.php?clear=1" class="cart_clear_top">Remove All</a>
        <?php } ?>
    </div>

    <?php if (empty($_SESSION['cart'])) { ?>
        <div class="cart_empty_box">
            <h3>Your cart is empty</h3>
            <p>Add some items to your shopping cart.</p>
            <a href="index.php" class="cart_checkout_btn">Continue Shopping</a>
        </div>
    <?php } else { ?>

    <div class="cart_table_wrap">
        <table class="cart_modern_table">
            <thead>
                <tr>
                    <th class="col-product">Product</th>
                    <th class="col-description">Product Description</th>
                    <th class="col-qty">Quantity</th>
                    <th class="col-price">Price</th>
                    <th class="col-action">Action</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($_SESSION['cart'] as $key => $item) { 
                    $subtotal = $item['price'] * $item['qty'];
                ?>
                <tr>
                    <td class="product_img_cell">
                        <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="cart_modern_img">
                    </td>

                    <td class="product_desc_cell">
                        <a href="#" class="cart_product_name"><?php echo $item['name']; ?></a>
                        <div class="cart_product_tags">
                            <span class="tag_box">HKMU</span>
                            <span class="tag_new">NEW</span>
                        </div>
                    </td>

                    <td>
                        <div class="qty_control">
                            <a href="cart.php?decrease=<?php echo $key; ?>" class="qty_square">-</a>
                            <span class="qty_value"><?php echo $item['qty']; ?></span>
                            <a href="cart.php?increase=<?php echo $key; ?>" class="qty_square">+</a>
                        </div>
                    </td>

                    <td class="cart_price_text">
                        HKD <?php echo number_format($subtotal, 2); ?>
                    </td>

                    <td>
                        <a href="cart.php?remove=<?php echo $key; ?>" class="cart_remove_link">Remove</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>

            <tfoot>
                <tr>
                    <td colspan="5">
                        <div class="cart_footer_summary">
                            <div class="summary_text">
                                <p><span>Total Quantity:</span> <?php echo $count; ?></p>
                                <p><span>Subtotal:</span> HKD <?php echo number_format($total, 2); ?></p>
                                <p class="summary_small">(HKD <?php echo number_format($total, 2); ?>)</p>
                            </div>

                            <div class="summary_btn_box">
                                <a href="checkout.php" class="cart_checkout_btn">Proceed to Checkout</a>
                            </div>
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <?php } ?>
</div>

</body>
</html>