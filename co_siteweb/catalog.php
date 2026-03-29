<?php
session_start();
require 'config.php';

// Get filter from URL
$genderFilter = isset($_GET['gender']) ? htmlspecialchars($_GET['gender']) : '';

// Build query
$query = "SELECT id, name, price, gender, image FROM products WHERE 1=1";
if ($genderFilter) {
    $query .= " AND gender = '" . $conn->real_escape_string($genderFilter) . "'";
}
$query .= " ORDER BY name ASC";

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue — FragranceBoutique</title>
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
            <span class="nav-icon">🏠</span> Accueil
        </a>
        <a href="catalog.php" aria-label="Catalogue">
            <span class="nav-icon">📦</span> Catalogue
        </a>
        <a href="cart.php" aria-label="Panier">
            <span class="nav-icon">🛒</span> Panier <span id="cart-count">(0)</span>
        </a>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="#" title="Profile">
                <span class="nav-icon">👤</span> <?php echo htmlspecialchars($_SESSION['name']); ?>
            </a>
            <a href="logout.php" aria-label="Logout" class="nav-logout">
                <span class="nav-icon">🚪</span> Logout
            </a>
        <?php else: ?>
            <a href="login.php" aria-label="Login" class="nav-auth">
                <span class="nav-icon">🔐</span> Login
            </a>
            <a href="register.php" aria-label="Register" class="nav-auth">
                <span class="nav-icon">✍️</span> Register
            </a>
        <?php endif; ?>
    </nav>
</header>

<main>
    <h1>Nos Parfums</h1>
    
    <!-- FILTERS -->
    <div class="filters">
        <a href="catalog.php" class="filter-btn <?php echo $genderFilter === '' ? 'active' : ''; ?>">Tous les Parfums</a>
        <a href="catalog.php?gender=homme" class="filter-btn <?php echo $genderFilter === 'homme' ? 'active' : ''; ?>">👨 Hommes</a>
        <a href="catalog.php?gender=femme" class="filter-btn <?php echo $genderFilter === 'femme' ? 'active' : ''; ?>">👩 Femmes</a>
    </div>

    <!-- PRODUCTS GRID -->
    <div class="product-grid">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($product = $result->fetch_assoc()): ?>
            <div class="product-card">
                <img src="<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" 
                     onerror="this.src='https://via.placeholder.com/200x200?text=Parfum'">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p class="price">MAD <?php echo number_format($product['price'], 2); ?></p>
                <form method="POST" action="add_to_cart.php" class="add-form">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <button type="submit" class="btn btn-add">🛒 Ajouter au panier</button>
                </form>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center; grid-column: 1 / -1; padding: 2rem;">Aucun produit trouvé.</p>
        <?php endif; ?>
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
        © 2026 S & O FRAGRANCE BOUTIQUE. All rights reserved.
    </div>
</footer>

<script src="assets/script.js"></script>

</body>
</html>