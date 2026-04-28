<?php
session_start();
require_once __DIR__ . "/includes/conn.php";
require_once __DIR__ . "/includes/cart_cookie.php";

$userName = "";
if (isset($_SESSION["user_name"])) {
    $userName = $_SESSION["user_name"];
}

$cartMessage = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_to_cart"])) {
    $cartMessage = addToCart($conn);
}

$sql = "SELECT * FROM tbl_products";
$result = mysqli_query($conn, $sql);

if ($result === false) {
    die("Query failed: " . mysqli_error($conn));
}

echo '<!DOCTYPE html>';
echo '<html lang="en">';
echo '<head>';
echo '    <meta charset="UTF-8">';
echo '    <meta name="viewport" content="width=device-width, initial-scale=1.0">';
echo '    <title>UCLan Website</title>';
echo '    <link rel="stylesheet" href="styles.css">';
echo '</head>';
echo '<body>';
echo '<header>';
echo '    <div class="header-section">';
echo '        <nav>';
echo '            <ul class="nav">';
echo '                <li><a href="index.php">Home</a></li>';
echo '                <li><a href="Products.php">Products</a></li>';
echo '                <li><a href="cart.php">Cart</a></li>';
if ($userName !== "") {
    echo '                <li><a href="logout.php">Log Out</a></li>';
} else {
    echo '                <li><a href="login.php">Log In</a></li>';
}
echo '            </ul>';
echo '        </nav>';
echo '        <div class="header-left">';
echo '            <button class="hamburger">&#9776;</button>';
echo '        </div>';
echo '        <div class="header-center">';
echo '            <h1>The Products Page</h1>';
echo '        </div>';
echo '        <div class="header-right">';
echo '            <a href="index.php">';
echo '                <img class="logo" src="logo_reverse.png" alt="Uclan Logo">';
echo '            </a>';
echo '        </div>';
echo '    </div>';
echo '</header>';
echo '<div class="stock-filter">';
echo '    <label for="filter-stock">Filter by stock:</label>';
echo '    <select id="filter-stock">';
echo '        <option value="all">All</option>';
echo '        <option value="in">In stock</option>';
echo '        <option value="out">Out of stock</option>';
echo '    </select>';
echo '</div>';
echo '<main class="products">';
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $id = htmlspecialchars($row['product_id']);
        $title = htmlspecialchars($row['product_title']);
        $desc = htmlspecialchars($row['product_desc']);
        $price = htmlspecialchars($row['product_price']);
        $image = 'tshirts/tshirt' . $id . '.jpg';
        $stock = htmlspecialchars($row['product_stock']);
        $stockLower = strtolower($stock);

        // Turn loose stock text into the few states the UI uses
        $isOutOfStock = false;

        if (strpos($stockLower, 'out') !== false) {
            $dataStock = 'out';
            $cardClass = 'product no-stock';
            $isOutOfStock = true;
        } elseif (strpos($stockLower, 'last') !== false || strpos($stockLower, 'low') !== false) {
            $dataStock = 'in';
            $cardClass = 'product last';
        } else {
            $dataStock = 'in';
            $cardClass = 'product good';
        }

        $buttonClass = 'add';
        $buttonText = 'Add to Cart';
        $buttonDisabled = '';

        if ($isOutOfStock) {
            // Show the card but block the button
            $buttonClass = 'add add-out-of-stock';
            $buttonText = 'Out Of Stock';
            $buttonDisabled = ' disabled';
        }

        echo '<div class="' . $cardClass . '" data-id="' . $id . '" data-stock="' . $dataStock . '">';
        echo '    <img src="' . $image . '" alt="' . $title . '">';
        echo '    <h2>' . $title . '</h2>';
        echo '    <p>' . $desc . '</p>';
        echo '    <p>Price: £' . $price . '</p>';
        echo '    <p>Status: ' . $stock . '</p>';
        echo '    <p><a class="read-more-link" href="Item.php?id=' . $id . '">Read more</a></p>';
        echo '    <form action="Products.php" method="POST" class="add-to-cart-form">';
        echo '        <input type="hidden" name="product_id" value="' . $id . '">';
        echo '        <label for="stock' . $id . '">Quantity:</label>';
        echo '        <input type="number" id="stock' . $id . '" name="quantity" min="1" value="1">';
        echo '        <button type="submit" name="add_to_cart" class="' . $buttonClass . '"' . $buttonDisabled . '>' . $buttonText . '</button>';
        echo '    </form>';
        echo '</div>';
    }
} else {
    echo '    <p>No products found.</p>';
}
echo '</main>';
echo '<footer>';
echo '    <div>';
echo "        <button onclick=\"window.location.href='Products.php'\">&#8593;</button>";
echo '    </div>';
echo '    <div>';
echo '        <h4>Contact Us</h4>';
echo '        <p><a href="https://maps.app.goo.gl/TbRMAgcJkC2mjEpm7">12 – 14 University Avenue Pyla, 7080 Larnaka, Cyprus</a></p>';
echo '        <p><a href="mailto:info@uclancyprus.ac.cy">Email: info@uclancyprus.ac.cy</a></p>';
echo '        <p><a href="tel:+357246940000">Tel: +357 24694000</a></p>';
echo '    </div>';
echo '    <div>';
echo '        <p>&copy; 2025 Uclan WebSite. All rights reserved.</p>';
echo '    </div>';
echo '</footer>';
echo '<script src="script.js?v=20260418"></script>';
if ($cartMessage !== "") {
    echo '<script>window.addEventListener("load", function () { setTimeout(function () { alert(' . json_encode($cartMessage) . '); }, 0); });</script>';
}
echo '</body>';
echo '</html>';
