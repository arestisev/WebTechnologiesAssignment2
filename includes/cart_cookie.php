<?php
function getCartCookieData()
{
    $cart = [];

    if (isset($_COOKIE["cart"])) {
        // The cookie is just an id and quantity map so clean it on the way in
        $decoded = json_decode($_COOKIE["cart"], true);

        if (is_array($decoded)) {
            foreach ($decoded as $productId => $quantity) {
                $cleanProductId = (int) $productId;
                $cleanQuantity = (int) $quantity;

                if ($cleanProductId > 0 && $cleanQuantity > 0) {
                    $cart[$cleanProductId] = $cleanQuantity;
                }
            }
        }
    }

    return $cart;
}

function writeCartCookieData($cart)
{
    $cleanCart = [];

    // Clean ids and quantities before writing the cookie
    foreach ($cart as $productId => $quantity) {
        $cleanProductId = (int) $productId;
        $cleanQuantity = (int) $quantity;

        if ($cleanProductId > 0 && $cleanQuantity > 0) {
            $cleanCart[$cleanProductId] = $cleanQuantity;
        }
    }

    $cookieValue = json_encode($cleanCart);

    // Update $_COOKIE too so this request sees the change
    setcookie("cart", $cookieValue, time() + (60 * 60 * 24 * 7), "/");
    $_COOKIE["cart"] = $cookieValue;
}

function addProductToCartCookie($productId, $quantity)
{
    $cart = getCartCookieData();
    $cleanProductId = (int) $productId;
    $cleanQuantity = (int) $quantity;

    if ($cleanProductId <= 0 || $cleanQuantity <= 0) {
        return;
    }

    if (isset($cart[$cleanProductId])) {
        $cart[$cleanProductId] += $cleanQuantity;
    } else {
        $cart[$cleanProductId] = $cleanQuantity;
    }

    writeCartCookieData($cart);
}

function removeProductFromCartCookie($productId)
{
    $cart = getCartCookieData();
    $cleanProductId = (int) $productId;

    if (isset($cart[$cleanProductId])) {
        unset($cart[$cleanProductId]);
        writeCartCookieData($cart);
    }
}

function clearCartCookieData()
{
    setcookie("cart", "", time() - 3600, "/");
    unset($_COOKIE["cart"]);
}

function addToCart($conn)
{
    if (!isset($_SESSION["user_id"])) {
        return "Please log in first";
    }

    $productId = 0;
    if (isset($_POST["product_id"])) {
        $productId = (int) $_POST["product_id"];
    }

    $quantity = 1;
    if (isset($_POST["quantity"])) {
        $quantity = (int) $_POST["quantity"];
    }

    if ($productId <= 0 || $quantity <= 0) {
        return "Please choose a valid quantity";
    }

    $stock = mysqli_prepare($conn, "SELECT product_stock FROM tbl_products WHERE product_id = ? LIMIT 1");

    if (!$stock) {
        return "Unable to add to cart right now";
    }

    mysqli_stmt_bind_param($stock, "i", $productId);
    mysqli_stmt_execute($stock);
    $stockResult = mysqli_stmt_get_result($stock);
    $stockRow = mysqli_fetch_assoc($stockResult);
    mysqli_stmt_close($stock);

    if (!$stockRow || stripos($stockRow["product_stock"], "out") !== false) {
        return "That item is out of stock";
    }

    addProductToCartCookie($productId, $quantity);
    return "Product added to cart";
}
