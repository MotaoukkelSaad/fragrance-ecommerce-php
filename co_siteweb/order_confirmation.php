<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$order_id) {
    header('Location: index.php');
    exit();
}

// Get order
$result = $conn->query("SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id");
$order = $result->fetch_assoc();

if (!$order) {
    header('Location: index.php');
    exit();
}

// Get order items
$items_result = $conn->query("SELECT * FROM order_items WHERE order_id = $order_id");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de Commande — FragranceBoutique</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<!-- HEADER -->
<header>
    <div class="logo">
        <a href="index.php" aria-label="Accueil">
            <img src="images/logo.png" alt="FragranceBoutique Logo" 
                 onerror="this.src='https://via.placeholder.com/150x50?text=FragranceBoutique'">
        </a>
    </div>
    <nav>
        <a href="index.php" aria-label="Accueil">
            <span class="nav-icon">🏠</span> Accueil
        </a>
        <a href="catalog.php" aria-label="Catalogue">
            <span class="nav-icon">📦</span> Catalogue
        </a>
        <a href="cart.php" aria-label="Panier">
            <span class="nav-icon">🛒</span> Panier
        </a>
        <a href="logout.php" aria-label="Logout" class="nav-logout">
            <span class="nav-icon">🚪</span> Logout
        </a>
    </nav>
</header>

<main class="confirmation-page">
    <div class="confirmation-container">
        <div class="confirmation-box">
            <div class="success-icon">✅</div>
            <h1>Commande Confirmée!</h1>
            <p class="subtitle">Merci pour votre achat chez FragranceBoutique</p>

            <!-- ORDER INFO -->
            <div class="order-info">
                <p><strong>📌 Numéro de Commande:</strong> #<?php echo $order['id']; ?></p>
                <p><strong>📅 Date de Commande:</strong> <?php echo date('d/m/Y à H:i', strtotime($order['created_at'])); ?></p>
                <p><strong>📦 Statut:</strong> <span class="status-badge"><?php echo strtoupper($order['status']); ?></span></p>
                <p><strong>👤 Nom:</strong> <?php echo htmlspecialchars($order['name']); ?></p>
                <p><strong>📞 Téléphone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                <p><strong>📍 Adresse de Livraison:</strong> <?php echo htmlspecialchars($order['address']); ?>, <?php echo htmlspecialchars($order['city']); ?></p>
                <p><strong>🚚 Mode de Livraison:</strong> 
                    <?php echo $order['shipping_type'] === 'gratuite' ? 'Livraison Gratuite' : 'Livraison Express'; ?>
                </p>
            </div>

            <!-- ORDER ITEMS TABLE -->
            <h3>Détails de la Commande</h3>
            <table class="confirmation-table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Prix Unitaire</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $items_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td style="text-align: center;">x<?php echo $item['quantity']; ?></td>
                        <td>MAD <?php echo number_format($item['price_at_purchase'], 2); ?></td>
                        <td><strong>MAD <?php echo number_format($item['price_at_purchase'] * $item['quantity'], 2); ?></strong></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- TOTALS -->
            <div class="totals-section">
                <div class="total-row">
                    <span>Sous-total:</span>
                    <span>MAD <?php echo number_format($order['subtotal'], 2); ?></span>
                </div>
                <div class="total-row">
                    <span>Livraison:</span>
                    <span><?php echo $order['shipping_cost'] == 0 ? 'GRATUITE' : 'MAD ' . number_format($order['shipping_cost'], 2); ?></span>
                </div>
                <div class="total-row grand-total">
                    <span>Total Commande:</span>
                    <span>MAD <?php echo number_format($order['total'], 2); ?></span>
                </div>
            </div>

            <!-- MESSAGE -->
            <div class="confirmation-message">
                <p>✉️ Un email de confirmation a été envoyé à <strong><?php echo htmlspecialchars($order['email']); ?></strong></p>
                <p>Nous préparons votre commande pour l'expédition. Vous recevrez un numéro de suivi par email dès que votre colis sera expédié!</p>
            </div>

            <!-- ACTIONS -->
            <div class="confirmation-actions">
                <a href="catalog.php" class="btn btn-primary">Continuer vos achats</a>
                <a href="index.php" class="btn">Retour à l'accueil</a>
            </div>
        </div>
    </div>
</main>

<!-- FOOTER -->
<footer>
    <div class="footer-payments">
        <img src="images/visa.png" alt="Visa" onerror="this.src='https://via.placeholder.com/50x30?text=Visa'">
        <img src="images/mastercard.png" alt="MasterCard" onerror="this.src='https://via.placeholder.com/50x30?text=MC'">
        <img src="images/amex.png" alt="Amex" onerror="this.src='https://via.placeholder.com/50x30?text=Amex'">
        <img src="images/paypal.png" alt="PayPal" onerror="this.src='https://via.placeholder.com/50x30?text=PayPal'">
    </div>
    <div class="footer-links">
        <a href="#">Politique de confidentialité</a>
        <span>·</span>
        <a href="#">Politique de remboursement</a>
        <span>·</span>
        <a href="#">Conditions d'utilisation</a>
        <span>·</span>
        <a href="#">Politique d'expédition</a>
    </div>
    <div class="footer-copy">
        © 2026 S & O FRAGRANCE BOUTIQUE.
    </div>
</footer>

</body>
</html>