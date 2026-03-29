<?php
session_start();
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get cart from session
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header('Location: cart.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = htmlspecialchars(trim($_POST['fullname']));
    $email = $_SESSION['email'];
    $phone = htmlspecialchars(trim($_POST['phone']));
    $address = htmlspecialchars(trim($_POST['address']));
    $city = htmlspecialchars(trim($_POST['city']));
    $shipping_type = $_POST['shipping_type'];
    $subtotal = (float)$_POST['subtotal'];
    
    // Calculate shipping cost
    $shipping_cost = ($shipping_type === 'express') ? SHIPPING_EXPRESS : SHIPPING_FREE;
    $total = $subtotal + $shipping_cost;

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, name, email, phone, address, city, subtotal, shipping_type, shipping_cost, total) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssddd", $user_id, $name, $email, $phone, $address, $city, $subtotal, $shipping_type, $shipping_cost, $total);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert order");
        }

        $order_id = $conn->insert_id;

        // Insert order items
        foreach ($cart as $product_id => $quantity) {
            // Get product info
            $result = $conn->query("SELECT name, price FROM products WHERE id = $product_id");
            $product = $result->fetch_assoc();

            if (!$product) {
                throw new Exception("Product not found");
            }

            $price_at_purchase = $product['price'];
            $product_name = $product['name'];

            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price_at_purchase) 
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisid", $order_id, $product_id, $product_name, $quantity, $price_at_purchase);

            if (!$stmt->execute()) {
                throw new Exception("Failed to insert order item");
            }
        }

        // Commit transaction
        $conn->commit();

        // Clear cart
        unset($_SESSION['cart']);

        // Redirect to confirmation
        header('Location: order_confirmation.php?order_id=' . $order_id);
        exit();

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $_SESSION['error'] = "Erreur lors de la création de la commande: " . $e->getMessage();
        header('Location: cart.php');
        exit();
    }
} else {
    header('Location: cart.php');
    exit();
}
?>