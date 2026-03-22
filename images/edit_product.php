<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../home/login.php");
    exit();
}

if ($_SESSION['usertype'] != "admin") {
    header("Location: ../home/login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "php_shopping app");
/* 之後如果改 database，再一齊改 */

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if (!isset($_GET['id'])) {
    header("Location: adminpage.php");
    exit();
}

$id = (int)$_GET['id'];
$message = "";

$result = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");
if (!$result || mysqli_num_rows($result) == 0) {
    die("Product not found.");
}

$product = mysqli_fetch_assoc($result);

if (isset($_POST['update_product'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $price = (float)$_POST['price'];
    $image = mysqli_real_escape_string($conn, trim($_POST['image']));
    $category = mysqli_real_escape_string($conn, trim($_POST['category']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));

    $sql = "UPDATE products 
            SET name='$name', price='$price', image='$image', category='$category', description='$description'
            WHERE id='$id'";

    if (mysqli_query($conn, $sql)) {
        $message = "Product updated successfully.";
        $result = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");
        $product = mysqli_fetch_assoc($result);
    } else {
        $message = "Failed to update product.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Edit Product</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../style.css">
</head>
<body>

<nav>
    <input type="checkbox" id="check">
    <label for="check" class="checkbtn">☰</label>
    <label class="my_logo">HKMU Shopping App</label>

    <ul>
        <li><a href="../index.php">Home</a></li>
        <li><a href="adminpage.php">Admin Panel</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</nav>

<main>
    <div class="checkout_box">
        <h2>Edit Product</h2>

        <?php if ($message != "") { ?>
            <p class="success_msg"><?php echo $message; ?></p>
        <?php } ?>

        <form method="POST" action="">
            <div class="input_deg">
                <label>Product Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>

            <div class="input_deg">
                <label>Price</label>
                <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
            </div>

            <div class="input_deg">
                <label>Image Path</label>
                <input type="text" name="image" value="<?php echo htmlspecialchars($product['image']); ?>" required>
            </div>

            <div class="input_deg">
                <label>Category</label>
                <input type="text" name="category" value="<?php echo htmlspecialchars($product['category']); ?>" required>
            </div>

            <div class="input_deg">
                <label>Description</label>
                <textarea name="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div class="input_deg">
                <img src="../<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="admin_product_img">
            </div>

            <div class="input_deg">
                <button type="submit" name="update_product" class="cart_checkout_btn" style="border:none; cursor:pointer;">
                    Update Product
                </button>
                <a href="adminpage.php" class="normal_btn">Back</a>
            </div>
        </form>
    </div>
</main>

</body>
</html>