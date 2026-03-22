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

/* database connection */
$conn = mysqli_connect("localhost", "root", "", "php_shopping app");
/* 如果你之後改返自己 database，例如 php_shopping_app，就改上面呢行 */

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$message = "";
$total_users = 0;
$total_admins = 0;
$total_customers = 0;
$total_products = 0;
$total_sales_amount = 0.00;

/* delete user */
if (isset($_GET['delete_user'])) {
    $delete_id = (int)$_GET['delete_user'];
    $current_admin_hkmuid = $_SESSION['user_id'];

    $check_sql = "SELECT * FROM users WHERE id = '$delete_id'";
    $check_result = mysqli_query($conn, $check_sql);

    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $user_row = mysqli_fetch_assoc($check_result);

        if ($user_row['hkmuid'] == $current_admin_hkmuid) {
            $message = "You cannot delete your own admin account.";
        } else {
            $delete_sql = "DELETE FROM users WHERE id = '$delete_id'";
            if (mysqli_query($conn, $delete_sql)) {
                $message = "User deleted successfully.";
            } else {
                $message = "Failed to delete user.";
            }
        }
    } else {
        $message = "User not found.";
    }
}

/* add product with image upload */
if (isset($_POST['add_product'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $price = (float)$_POST['price'];
    $category = mysqli_real_escape_string($conn, trim($_POST['category']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $image = "";

    if (
        $name != "" &&
        $category != "" &&
        $description != "" &&
        isset($_FILES['image_file']) &&
        $_FILES['image_file']['error'] == 0
    ) {
        $target_dir = "../images/";

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $original_name = basename($_FILES["image_file"]["name"]);
        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($extension, $allowed)) {
            $message = "Only JPG, JPEG, PNG, GIF, and WEBP files are allowed.";
        } else {
            $new_file_name = time() . "_" . preg_replace("/[^A-Za-z0-9._-]/", "_", $original_name);
            $target_file = $target_dir . $new_file_name;

            if (move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_file)) {
                $image = "images/" . $new_file_name;

                $sql = "INSERT INTO products (name, price, image, category, description)
                        VALUES ('$name', '$price', '$image', '$category', '$description')";

                if (mysqli_query($conn, $sql)) {
                    $message = "Product added successfully.";
                } else {
                    $message = "Failed to add product.";
                }
            } else {
                $message = "Image upload failed.";
            }
        }
    } else {
        $message = "Please fill in all product fields and choose an image.";
    }
}

/* inline update product text fields */
if (isset($_POST['update_product_inline'])) {
    $id = (int)$_POST['product_id'];
    $name = mysqli_real_escape_string($conn, trim($_POST['product_name']));
    $category = mysqli_real_escape_string($conn, trim($_POST['product_category']));
    $description = mysqli_real_escape_string($conn, trim($_POST['product_description']));
    $price = (float)$_POST['product_price'];
    $image = mysqli_real_escape_string($conn, trim($_POST['product_image']));

    if ($name != "" && $category != "" && $description != "" && $image != "") {
        $update_sql = "UPDATE products
                       SET name='$name',
                           category='$category',
                           description='$description',
                           price='$price',
                           image='$image'
                       WHERE id='$id'";

        if (mysqli_query($conn, $update_sql)) {
            $message = "Product updated successfully.";
        } else {
            $message = "Failed to update product.";
        }
    } else {
        $message = "Please fill in all product fields before saving.";
    }
}

/* replace product image */
if (isset($_POST['update_product_image'])) {
    $id = (int)$_POST['product_id'];

    if (isset($_FILES['new_image_file']) && $_FILES['new_image_file']['error'] == 0) {
        $target_dir = "../images/";

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $original_name = basename($_FILES["new_image_file"]["name"]);
        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($extension, $allowed)) {
            $message = "Only JPG, JPEG, PNG, GIF, and WEBP files are allowed.";
        } else {
            $new_file_name = time() . "_" . preg_replace("/[^A-Za-z0-9._-]/", "_", $original_name);
            $target_file = $target_dir . $new_file_name;

            if (move_uploaded_file($_FILES["new_image_file"]["tmp_name"], $target_file)) {
                $image_path = "images/" . $new_file_name;

                $update_img_sql = "UPDATE products SET image='$image_path' WHERE id='$id'";
                if (mysqli_query($conn, $update_img_sql)) {
                    $message = "Product image updated successfully.";
                } else {
                    $message = "Failed to update product image.";
                }
            } else {
                $message = "Image upload failed.";
            }
        }
    } else {
        $message = "Please choose an image file first.";
    }
}

/* delete product */
if (isset($_GET['delete_product'])) {
    $delete_product_id = (int)$_GET['delete_product'];

    $delete_sql = "DELETE FROM products WHERE id='$delete_product_id'";
    if (mysqli_query($conn, $delete_sql)) {
        $message = "Product deleted successfully.";
    } else {
        $message = "Failed to delete product.";
    }
}

/* counts */
$user_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
if ($user_count_result) {
    $row = mysqli_fetch_assoc($user_count_result);
    $total_users = (int)$row['total'];
}

$admin_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE usertype='admin'");
if ($admin_count_result) {
    $row = mysqli_fetch_assoc($admin_count_result);
    $total_admins = (int)$row['total'];
}

$customer_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE usertype='user'");
if ($customer_count_result) {
    $row = mysqli_fetch_assoc($customer_count_result);
    $total_customers = (int)$row['total'];
}

$product_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products");
if ($product_count_result) {
    $row = mysqli_fetch_assoc($product_count_result);
    $total_products = (int)$row['total'];
}

/* total overall sales */
$total_sales_result = mysqli_query($conn, "SELECT SUM(total_amount) AS total_sales FROM orders");
if ($total_sales_result) {
    $row = mysqli_fetch_assoc($total_sales_result);
    $total_sales_amount = $row['total_sales'] ? (float)$row['total_sales'] : 0.00;
}

/* sales by item */
$sql_sales = "
SELECT product_name,
       SUM(quantity) AS total_sold,
       SUM(price * quantity) AS total_revenue
FROM order_items
GROUP BY product_name
ORDER BY total_sold DESC, product_name ASC
";
$result_sales = mysqli_query($conn, $sql_sales);

$users_result = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
$products_result = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Panel</title>
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
        <li><a href="../cart.php">Cart</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</nav>

<main>
    <div class="admin_page">
        <div class="breadcrumb">
            <a href="../index.php">Home</a> <span>&gt;</span> <span>Admin Panel</span>
        </div>

        <h1 class="admin_title">Admin Dashboard</h1>

        <?php if ($message != "") { ?>
            <div class="admin_message"><?php echo htmlspecialchars($message); ?></div>
        <?php } ?>

        <div class="admin_cards">
            <div class="admin_card">
                <h3>Total Users</h3>
                <p><?php echo $total_users; ?></p>
            </div>

            <div class="admin_card">
                <h3>Total Admins</h3>
                <p><?php echo $total_admins; ?></p>
            </div>

            <div class="admin_card">
                <h3>Total Customers</h3>
                <p><?php echo $total_customers; ?></p>
            </div>

            <div class="admin_card">
                <h3>Total Products</h3>
                <p><?php echo $total_products; ?></p>
            </div>
        </div>

        <div class="admin_section">
            <div class="admin_section_head">
                <h2>Total Sales Overview</h2>
            </div>

            <div class="admin_note_box">
                <p><strong>Overall Sales:</strong> HKD <?php echo number_format($total_sales_amount, 2); ?></p>
            </div>

            <div class="admin_table_wrap">
                <table class="admin_table">
                    <tr>
                        <th>Product</th>
                        <th>Total Sold</th>
                        <th>Total Revenue (HKD)</th>
                    </tr>

                    <?php if ($result_sales && mysqli_num_rows($result_sales) > 0) { ?>
                        <?php while ($sale = mysqli_fetch_assoc($result_sales)) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sale['product_name']); ?></td>
                                <td><?php echo (int)$sale['total_sold']; ?></td>
                                <td><?php echo number_format((float)$sale['total_revenue'], 2); ?></td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="3">No sales data found.</td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>

        <div class="admin_section">
            <div class="admin_section_head">
                <h2>Add New Product</h2>
            </div>

            <div class="admin_note_box">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="input_deg">
                        <label>Product Name</label>
                        <input type="text" name="name" required>
                    </div>

                    <div class="input_deg">
                        <label>Price</label>
                        <input type="number" step="0.01" name="price" required>
                    </div>

                    <div class="input_deg">
                        <label>Choose Image</label>
                        <input type="file" name="image_file" accept="image/*" required>
                    </div>

                    <div class="input_deg">
                        <label>Category</label>
                        <input type="text" name="category" required>
                    </div>

                    <div class="input_deg">
                        <label>Description</label>
                        <textarea name="description" rows="4" required></textarea>
                    </div>

                    <div class="input_deg">
                        <button type="submit" name="add_product" class="cart_checkout_btn" style="border:none; cursor:pointer;">
                            Add Product
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="admin_section">
            <div class="admin_section_head">
                <h2>Product Catalog</h2>
            </div>

            <div class="admin_table_wrap">
                <table class="admin_table">
                    <tr>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Save edit</th>
                        <th>Change Image</th>
                        <th>Delete</th>
                    </tr>

                    <?php if ($products_result && mysqli_num_rows($products_result) > 0) { ?>
                        <?php while ($product = mysqli_fetch_assoc($products_result)) { ?>
                            <tr>
                                <td>
                                    <img src="../<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="admin_product_img">
                                </td>

                                <td>
                                    <form method="POST" action="">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($product['image']); ?>">
                                        <input type="text" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>" class="admin_inline_input" required>
                                </td>

                                <td>
                                        <input type="text" name="product_category" value="<?php echo htmlspecialchars($product['category']); ?>" class="admin_inline_input" required>
                                </td>

                                <td>
                                        <textarea name="product_description" rows="3" class="admin_inline_textarea" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                                </td>

                                <td>
                                        <input type="number" step="0.01" name="product_price" value="<?php echo htmlspecialchars($product['price']); ?>" class="admin_inline_input admin_price_input" required>
                                </td>

                                <td>
                                        <button type="submit" name="update_product_inline" class="normal_btn" style="border:none; cursor:pointer;">
                                            Save
                                        </button>
                                    </form>
                                </td>

                                <td>
                                    <form method="POST" action="" enctype="multipart/form-data">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="file" name="new_image_file" accept="image/*" class="admin_inline_input" style="padding:6px;" required>
                                        <br><br>
                                        <button type="submit" name="update_product_image" class="normal_btn" style="border:none; cursor:pointer;">
                                            Upload
                                        </button>
                                    </form>
                                </td>

                                <td>
                                    <a class="admin_delete_btn" href="adminpage.php?delete_product=<?php echo $product['id']; ?>" onclick="return confirm('Delete this product?');">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="8">No products found.</td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>

        <div class="admin_section">
            <div class="admin_section_head">
                <h2>Registered Users</h2>
            </div>

            <div class="admin_table_wrap">
                <table class="admin_table">
                    <tr>
                        <th>ID</th>
                        <th>Role</th>
                        <th>Name</th>
                        <th>HKMU ID</th>
                        <th>Email</th>
                        <th>User Type</th>
                        <th>Action</th>
                    </tr>

                    <?php if ($users_result && mysqli_num_rows($users_result) > 0) { ?>
                        <?php while ($user = mysqli_fetch_assoc($users_result)) { ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['hkmuid']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['usertype']); ?></td>
                                <td>
                                    <a class="admin_delete_btn"
                                       href="adminpage.php?delete_user=<?php echo $user['id']; ?>"
                                       onclick="return confirm('Are you sure you want to delete this user?');">
                                       Delete
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="7">No users found.</td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>
</main>

</body>
</html>