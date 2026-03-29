<?php
session_start();
require 'config.php';

// Get cart items from session
$cartItems = $_SESSION['cart'] ?? [];
$cartProducts = [];
$subtotal = 0;

if (!empty($cartItems)) {
    $ids = implode(',', array_keys($cartItems));
    $result = $conn->query("SELECT id, name, price, image FROM products WHERE id IN ($ids)");
    
    while ($product = $result->fetch_assoc()) {
        $qty = $cartItems[$product['id']];
        $product['quantity'] = $qty;
        $product['lineTotal'] = $product['price'] * $qty;
        $subtotal += $product['lineTotal'];
        $cartProducts[] = $product;
    }
}

$cartCount = array_sum($cartItems);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier — FragranceBoutique</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<!-- HEADER/NAVBAR -->
<header>
    <div class="logo">
        <a href="index.php" aria-label="Accueil">
            <img src="images/logo.png" alt="FragranceBoutique Logo" 
                 onerror="this.src='https://via.placeholder.com/150x50?text=FragranceBoutique'">
        </a>
    </div>
    <nav>
        <a href="index.php" aria-label="Accueil">
            <span class="nav-icon">🏠</span> <span class="nav-text">Accueil</span>
        </a>
        <a href="catalog.php" aria-label="Catalogue">
            <span class="nav-icon">📦</span> <span class="nav-text">Catalogue</span>
        </a>
        <a href="cart.php" aria-label="Panier">
            <span class="nav-icon">🛒</span> <span class="nav-text">Panier</span> <span id="cart-count">(<?php echo $cartCount; ?>)</span>
        </a>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="#" title="Profile">
                <span class="nav-icon">👤</span> <span class="nav-text"><?php echo htmlspecialchars(substr($_SESSION['name'], 0, 10)); ?></span>
            </a>
            <a href="logout.php" aria-label="Logout" class="nav-logout">
                <span class="nav-icon">🚪</span> <span class="nav-text">Logout</span>
            </a>
        <?php else: ?>
            <a href="login.php" aria-label="Login" class="nav-auth">
                <span class="nav-icon">🔐</span> <span class="nav-text">Login</span>
            </a>
            <a href="register.php" aria-label="Register" class="nav-auth">
                <span class="nav-icon">✍️</span> <span class="nav-text">Register</span>
            </a>
        <?php endif; ?>
    </nav>
</header>

<main>
    <div style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
        <h1>Votre Panier</h1>
        
        <?php if (empty($cartProducts)): ?>
            <!-- EMPTY CART -->
            <div class="empty-cart">
                <p>🛒 Votre panier est vide.</p>
                <a href="catalog.php" class="btn btn-primary">Retour au catalogue</a>
            </div>
        <?php else: ?>
            <!-- CART TABLE -->
            <div class="cart-container">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Prix Unit.</th>
                            <th>Quantité</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartProducts as $product): ?>
                        <tr data-id="<?php echo $product['id']; ?>">
                            <td class="product-cell">
                                <img src="<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="cart-img" onerror="this.src='https://via.placeholder.com/60x60?text=Parfum'">
                                <span><?php echo htmlspecialchars($product['name']); ?></span>
                            </td>
                            <td class="price-cell">MAD <?php echo number_format($product['price'], 2); ?></td>
                            <td class="qty-cell">
                                <input type="number" class="qty-input" min="1" value="<?php echo $product['quantity']; ?>" 
                                       onchange="updateQty(<?php echo $product['id']; ?>, this.value)">
                            </td>
                            <td class="line-total">MAD <span class="amount"><?php echo number_format($product['lineTotal'], 2); ?></span></td>
                            <td>
                                <button class="btn-delete" onclick="deleteItem(<?php echo $product['id']; ?>)">🗑️ Supprimer</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- CART SUMMARY (CENTERED) -->
                <div class="cart-summary">
                    <div class="summary-row">
                        <span>Sous-total:</span>
                        <span>MAD <strong id="subtotal"><?php echo number_format($subtotal, 2); ?></strong></span>
                    </div>
                    <div class="summary-row shipping-row">
                        <span>Livraison:</span>
                        <span id="shipping-cost">GRATUITE</span>
                    </div>
                    <div class="summary-row total-row">
                        <span>Total:</span>
                        <span>MAD <strong id="total"><?php echo number_format($subtotal, 2); ?></strong></span>
                    </div>
                </div>

                <!-- ACTIONS (SIDE BY SIDE) -->
                <div class="cart-actions">
                    <a href="catalog.php" class="btn">Continuer mes achats</a>
                    <button class="btn btn-primary" onclick="openCheckoutModal(<?php echo $subtotal; ?>)">💳 Acheter maintenant</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- CHECKOUT MODAL -->
<div id="checkoutModal" class="modal" style="display: none;">
    <div class="modal-content">
        <!-- Close Button -->
        <button class="modal-close" onclick="closeCheckoutModal()">&times;</button>

        <h2>Commande avec paiement à la livraison</h2>

        <!-- SHIPPING MODE -->
        <div class="modal-section">
            <h3>Mode de livraison</h3>
            <div class="shipping-options">
                <label class="shipping-option">
                    <input type="radio" name="shipping_type" value="gratuite" checked onchange="updateShipping()">
                    <div class="option-content">
                        <span>LIVRAISON GRATUITE</span>
                        <span class="cost">GRATUITE</span>
                    </div>
                </label>
                <label class="shipping-option">
                    <input type="radio" name="shipping_type" value="express" onchange="updateShipping()">
                    <div class="option-content">
                        <span>LIVRAISON EXPRESS</span>
                        <span class="cost">35.00 dh</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- SHIPPING ADDRESS FORM -->
        <div class="modal-section">
            <h3>Insérez votre adresse de livraison</h3>
            <form id="checkoutForm" method="POST" action="process_order.php">
                <div class="form-group">
                    <label for="fullname">Nom Complet *</label>
                    <div class="input-icon-group">
                        <span class="input-icon">👤</span>
                        <input type="text" id="fullname" name="fullname" placeholder="Nom Complet" required 
                               value="<?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone">Téléphone *</label>
                    <div class="input-icon-group">
                        <span class="input-icon">📞</span>
                        <input type="tel" id="phone" name="phone" placeholder="Téléphone" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Adresse *</label>
                    <div class="input-icon-group">
                        <span class="input-icon">📍</span>
                        <input type="text" id="address" name="address" placeholder="Adresse" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="city">Ville *</label>
                    <div class="input-icon-group">
                        <span class="input-icon">🏙️</span>
                        <input type="text" id="city" name="city" placeholder="Ville" required>
                    </div>
                </div>

                <input type="hidden" name="shipping_type" id="hidden_shipping_type" value="gratuite">
                <input type="hidden" name="subtotal" id="hidden_subtotal" value="<?php echo $subtotal; ?>">

                <!-- SUMMARY IN MODAL -->
                <div class="modal-summary">
                    <div class="summary-row">
                        <span>Sous-total</span>
                        <span id="modal-subtotal">MAD <?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Livraison</span>
                        <span id="modal-shipping">GRATUITE</span>
                    </div>
                    <div class="summary-row total-row">
                        <span>Total</span>
                        <span id="modal-total">MAD <?php echo number_format($subtotal, 2); ?></span>
                    </div>
                </div>

                <button type="submit" class="btn-terminate">Terminez votre achat - MAD <span id="btn-total"><?php echo number_format($subtotal, 2); ?></span></button>
            </form>
        </div>
    </div>
</div>

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
        © 2026 S & O FRAGRANCE BOUTIQUE. All rights reserved.
    </div>
</footer>

<script src="assets/script.js"></script>
<script>
    const shippingCosts = { gratuite: 0, express: 35.00 };
    let currentSubtotal = <?php echo $subtotal; ?>;

    function updateQty(productId, qty) {
        qty = parseInt(qty);
        if (qty < 1) qty = 1;
        
        fetch('update_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'product_id=' + productId + '&quantity=' + qty
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }

    function deleteItem(productId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) {
            fetch('delete_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'product_id=' + productId
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
    }

    function openCheckoutModal(subtotal) {
        <?php if (!isset($_SESSION['user_id'])): ?>
            alert('Veuillez vous connecter pour continuer.');
            window.location.href = 'login.php';
            return;
        <?php endif; ?>
        
        document.getElementById('checkoutModal').style.display = 'flex';
        updateShipping();
    }

    function closeCheckoutModal() {
        document.getElementById('checkoutModal').style.display = 'none';
    }

    function updateShipping() {
        const shippingType = document.querySelector('input[name="shipping_type"]:checked').value;
        const cost = shippingCosts[shippingType];
        const total = currentSubtotal + cost;

        document.getElementById('hidden_shipping_type').value = shippingType;
        document.getElementById('modal-shipping').textContent = cost === 0 ? 'GRATUITE' : 'MAD ' + cost.toFixed(2);
        document.getElementById('modal-subtotal').textContent = 'MAD ' + currentSubtotal.toFixed(2);
        document.getElementById('modal-total').textContent = 'MAD ' + total.toFixed(2);
        document.getElementById('btn-total').textContent = total.toFixed(2);
    }

    window.onclick = function(event) {
        const modal = document.getElementById('checkoutModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }
</script>

</body>
</html>