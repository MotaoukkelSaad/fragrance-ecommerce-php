<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit();
}

$product_id = (int)$_POST['product_id'];
$quantity = (int)$_POST['quantity'];

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Produit invalide']);
    exit();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($quantity <= 0) {
    unset($_SESSION['cart'][$product_id]);
    echo json_encode(['success' => true, 'message' => 'Produit retiré du panier']);
    exit();
}

$_SESSION['cart'][$product_id] = $quantity;
echo json_encode(['success' => true, 'message' => 'Panier mis �� jour']);
?>