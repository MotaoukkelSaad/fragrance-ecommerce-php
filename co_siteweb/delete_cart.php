<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

if (!isset($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Paramètre manquant']);
    exit();
}

$product_id = (int)$_POST['product_id'];

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Produit invalide']);
    exit();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$product_id])) {
    unset($_SESSION['cart'][$product_id]);
    echo json_encode(['success' => true, 'message' => 'Produit supprimé']);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
?>