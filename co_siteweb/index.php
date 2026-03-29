<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FragranceBoutique — Parfums Hommes & Femmes</title>
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

<!-- HERO SECTION -->
<main>
    <section class="hero">
        <div class="hero-content">
            <h1>Elegance & Raffinement</h1>
            <p>Découvrez notre collection exclusive de parfums pour hommes et femmes</p>
            <a href="catalog.php" class="btn btn-primary">Découvrir la Collection</a>
        </div>
    </section>

    <!-- CATEGORIES SECTION -->
    <section class="categories">
        <h2>Nos Catégories</h2>
        <div class="category-grid">
            <div class="category-card">
                <h3>Parfums Hommes</h3>
                <p>Énergie, puissance et sophistication</p>
                <a href="catalog.php?gender=homme" class="btn">Voir les produits</a>
            </div>
            <div class="category-card">
                <h3>Parfums Femmes</h3>
                <p>Douceur, sensualité et charme</p>
                <a href="catalog.php?gender=femme" class="btn">Voir les produits</a>
            </div>
        </div>
    </section>

    <!-- FEATURED PRODUCTS -->
    <section class="featured">
        <h2>Produits Vedettes</h2>
        <div class="product-grid">
            <?php
            require 'config.php';
            $result = $conn->query("SELECT id, name, price, image FROM products LIMIT 4");
            while ($product = $result->fetch_assoc()):
            ?>
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
        </div>
    </section>
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

<script src="assets/script.js"></script>
<!-- FAQ CHATBOT -->
<?php include 'faq_chatbot.php'; ?>
</body>
</html>