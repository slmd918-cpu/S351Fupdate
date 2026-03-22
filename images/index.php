<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "php_shopping app");
/* 之後如果改 database，再一齊改 */

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['qty'];
    }
}

$products_result = mysqli_query($conn, "SELECT * FROM products ORDER BY id ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HKMU Shopping App</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>
<body>

<nav>
    <input type="checkbox" id="check">
    <label for="check" class="checkbtn"><i class="fa fa-bars"></i></label>
    <label class="my_logo">HKMU Shopping App</label>

    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="#products">Products</a></li>
        <li><a href="cart.php">Cart (<?php echo $count; ?>)</a></li>
        <li><a href="home/login.php">LogOut</a></li>
    </ul>
</nav>

<main>
    <div>
        <img class="my_cover" src="images/cover.jpeg" alt="cover">
    </div>

    <div id="products">
        <h3 class="p_title">Products</h3>
    </div>

    <div class="my_card">
        <?php if ($products_result && mysqli_num_rows($products_result) > 0) { ?>
            <?php while ($product = mysqli_fetch_assoc($products_result)) { ?>
                <div class="card">
                    <img class="p_image" src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                    <p><?php echo htmlspecialchars($product['description']); ?></p>
                    <p>Price : $<?php echo number_format($product['price'], 2); ?></p>

                    <form method="POST" action="cart.php" class="product_btns">
                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="name" value="<?php echo htmlspecialchars($product['name']); ?>">
                        <input type="hidden" name="price" value="<?php echo $product['price']; ?>">
                        <input type="hidden" name="image" value="<?php echo htmlspecialchars($product['image']); ?>">

                        <button type="submit" name="add_to_cart">Add to Cart</button>
                        <button type="submit" name="buy_now">Buy Now</button>
                    </form>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p style="text-align:center;">No products found.</p>
        <?php } ?>
    </div>
</main>

<div class="footer" id="footer">
    <div class="footer_title">
        <h3>HKMU Shopping App</h3>
    </div>

    <div class="footer_content">
        <div>
            <h4>Services</h4>
            <p><a href="#">Web Development</a></p>
            <p><a href="#">App Development</a></p>
            <p><a href="#">Digital Marketing</a></p>
        </div>

        <div>
            <h4>Social Links</h4>
            <p><a href="#">Facebook</a></p>
            <p><a href="#">Instagram</a></p>
            <p><a href="#">Twitter</a></p>
        </div>

        <div>
            <h4>Quick Links</h4>
            <p><a href="index.php">Home</a></p>
            <p><a href="#products">Products</a></p>
            <p><a href="cart.php">Cart</a></p>
            <p><a href="home/login.php">Login</a></p>
        </div>

        <div>
            <h4>Location</h4>
            <p>30 Good Shepherd Street, Ho Man Tin, Kowloon</p>
            <p>Email : info@hkmu.edu.hk</p>
            <p>Phone : 27112100</p>
        </div>
    </div>

    <footer>
        <hr>
        <h3>Copyright @ Hong Kong Metropolitan University 2026</h3>
    </footer>
</div>

</body>
</html>