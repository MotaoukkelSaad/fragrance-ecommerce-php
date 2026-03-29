-- db_fragrance - COMPLETE DATABASE SCHEMA
CREATE DATABASE db_fragrance;

USE db_fragrance;

-- Users table with authentication
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20),
  `role` ENUM('user', 'admin') DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products table
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `gender` ENUM('homme', 'femme', 'unisex') NOT NULL,
  `image` VARCHAR(255),
  `description` TEXT,
  `stock` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_gender (gender),
  INDEX idx_price (price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders table
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address` TEXT NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `subtotal` DECIMAL(10, 2) NOT NULL,
  `shipping_type` ENUM('gratuite', 'express') DEFAULT 'gratuite',
  `shipping_cost` DECIMAL(10, 2) DEFAULT 0,
  `total` DECIMAL(10, 2) NOT NULL,
  `status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX idx_user_id (user_id),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order items table
CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `product_name` VARCHAR(150) NOT NULL,
  `quantity` INT NOT NULL,
  `price_at_purchase` DECIMAL(10, 2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
  INDEX idx_order_id (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert admin user (password: admin123)
INSERT INTO `users` (name, email, password, role) VALUES 
('Admin User', 'admin@boutique.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/KFm', 'admin');

-- Insert sample products
INSERT INTO `products` (name, price, gender, image, description, stock) VALUES 
('Bleu de Chanel', 1200.00, 'homme', 'images/products/bleu-chanel.png', 'Elegant men perfume with fresh notes', 50),
('La Vie Est Belle', 950.00, 'femme', 'images/products/la-vie.png', 'Beautiful women perfume with floral notes', 40),
('Sauvage Dior', 1100.00, 'homme', 'images/products/sauvage.png', 'Fresh masculine scent', 35),
('Black Opium', 1050.00, 'femme', 'images/products/black-opium.png', 'Luxurious women fragrance', 45),
('One Million', 1000.00, 'homme', 'images/products/one-million.png', 'Sophisticated men cologne', 30),
('Good Girl', 1150.00, 'femme', 'images/products/good-girl.png', 'Intense and bold fragrance for women', 38),
('Acqua di Gio', 850.00, 'homme', 'images/products/acqua-gio.png', 'Fresh aquatic fragrance', 55),
('Libre', 1250.00, 'femme', 'images/products/libre.png', 'Exotic and sensual women perfume', 32),
('Aventus', 2000.00, 'homme', 'images/products/aventus.png', 'Premium men fragrance', 20),
('Baccarat Rouge', 2500.00, 'femme', 'images/products/baccarat-rouge.png', 'Luxury women perfume', 15),
('Invictus', 900.00, 'homme', 'images/products/invictus.png', 'Powerful men fragrance', 42),
('Miss Dior', 1000.00, 'femme', 'images/products/miss-dior.png', 'Elegant women perfume', 48);

-- =====================================================
-- FRAGRANCE BOUTIQUE - COMPLETE FAQ SYSTEM
-- =====================================================

-- CREATE FAQ TABLE
CREATE TABLE IF NOT EXISTS `faqs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category` VARCHAR(100) NOT NULL,
  `question` TEXT NOT NULL,
  `answer` TEXT NOT NULL,
  `keywords` VARCHAR(255),
  `order_num` INT DEFAULT 0,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_category (category),
  INDEX idx_active (is_active),
  FULLTEXT INDEX ft_search (question, answer, keywords)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- INSERT COMPREHENSIVE FAQs
-- =====================================================

-- ABOUT US & STORE
INSERT INTO `faqs` (category, question, answer, keywords, order_num) VALUES 
('À propos', 'Qui est FragranceBoutique?', 'FragranceBoutique est une boutique en ligne spécialisée dans la vente de parfums authentiques pour hommes et femmes. Nous sommes basés à Rabat, Maroc et nous nous engageons à offrir les meilleures marques de parfums à des prix compétitifs.', 'boutique, magasin, qui sommes-nous, histoire', 1),
('À propos', 'Depuis quand FragranceBoutique existe-t-il?', 'FragranceBoutique a été créé en 2024 avec la mission de rendre les parfums premium accessibles à tous les clients marocains. Nous nous efforçons de fournir un service client de qualité supérieure.', 'années, expérience, fondation, création', 2),
('À propos', 'Où est basée FragranceBoutique?', 'FragranceBoutique est basée à Rabat, Maroc. Notre équipe est disponible pour vous aider du lundi au vendredi de 9h à 18h.', 'adresse, localisation, siège, bureau', 3),
('À propos', 'Comment puis-je contacter FragranceBoutique?', 'Vous pouvez nous contacter par email à info@fragranceboutique.com ou par téléphone au +212 5XX-XXXXXX. Notre équipe de service client est disponible pour répondre à vos questions.', 'contact, email, téléphone, support, aide', 4),

-- PRODUCTS & INVENTORY
('Produits', 'D\'où proviennent vos parfums?', 'Tous nos parfums proviennent de distributeurs officiels et authentifiés. Nous travaillons directement avec les fournisseurs pour garantir l\'authenticité de tous nos produits.', 'authentique, original, fournisseur, provenance', 1),
('Produits', 'Tous vos parfums sont-ils 100% authentiques?', 'Oui! Nous garantissons 100% d\'authenticité pour tous les parfums que nous vendons. Si vous avez des doutes sur l\'authenticité d\'un produit, nous vous offrirons un remboursement complet.', 'authentique, vrai, réel, original, contrefaçon', 2),
('Produits', 'Quelle est la différence entre les concentrations de parfum?', 'Parfum (30-40% de concentration) est le plus fort et dure 8h+. Eau de Parfum (15-20%) dure 5-8h. Eau de Toilette (5-15%) dure 3-5h. Eau de Cologne (2-5%) dure 1-3h.', 'concentration, force, durée, eau de toilette, parfum', 3),
('Produits', 'Comment puis-je savoir si un parfum me plaira?', 'Nous recommandons de lire les descriptions détaillées et les avis des clients. Vous pouvez aussi nous contacter pour des conseils personnalisés ou demander des échantillons.', 'conseil, aide, choix, préférence, goût', 4),
('Produits', 'Avez-vous des parfums pour hommes et femmes?', 'Oui! Nous proposons une vaste gamme de parfums pour hommes, femmes et unisexe. Vous pouvez parcourir nos collections filtrées par genre.', 'hommes, femmes, unisexe, collections', 5),
('Produits', 'Avez-vous des nouveautés ou des collections limitées?', 'Oui, nous ajoutons régulièrement de nouveaux parfums et des éditions limitées. Inscrivez-vous à notre newsletter pour être informé des nouvelles arrivées.', 'nouveautés, édition limitée, collection, nouveau', 6),
('Produits', 'Quelle marques de parfums vendez-vous?', 'Nous vendons les meilleures marques internationales comme Dior, Chanel, Yves Saint Laurent, Tom Ford, Guerlain, Lancôme et bien d\'autres.', 'marques, dior, chanel, tom ford, designer', 7),
('Produits', 'Pouvez-vous vous procurer un parfum spécifique?', 'Si nous n\'avons pas un parfum en stock, veuillez nous contacter. Nous pouvons généralement nous le procurer dans un délai de 5-10 jours ouvrables.', 'commande spéciale, stock, rupture', 8),
('Produits', 'Quelle est la durée de vie d\'un parfum?', 'Les parfums ont généralement une durée de vie de 3-5 ans s\'ils sont correctement conservés à l\'abri du soleil et de la chaleur. Les concentrations plus élevées durent plus longtemps.', 'expiration, durée, conservation, stockage', 9),
('Produits', 'Quel est le volume des flacons?', 'La plupart de nos parfums sont disponibles en 50ml, 75ml ou 100ml. Certaines marques proposent aussi des formats 30ml ou 200ml. Vérifiez la fiche produit pour les détails.', 'ml, volume, taille, contenance', 10),

-- SHOPPING & CART
('Achat', 'Comment puis-je ajouter un produit à mon panier?', 'Cliquez sur le bouton "Ajouter au panier" sur la page du produit. Vous pouvez continuer à acheter ou accéder à votre panier pour vérifier vos articles.', 'panier, ajouter, acheter, sélectionner', 1),
('Achat', 'Puis-je modifier la quantité dans mon panier?', 'Oui, allez à votre panier, modifiez la quantité souhaitée et cliquez sur "Mettre à jour". Vous pouvez aussi supprimer des articles.', 'quantité, modifier, panier, supprimer', 2),
('Achat', 'Comment puis-je appliquer un code promo?', 'Malheureusement, nous n\'avons pas actuellement de système de code promo. Suivez-nous sur les réseaux sociaux pour les offres spéciales et les réductions.', 'code promo, réduction, rabais, offre', 3),
('Achat', 'Mon panier est vide, comment puis-je commencer?', 'Allez à notre page Catalogue, parcourez les parfums disponibles, et cliquez sur "Ajouter au panier" pour les produits que vous aimez.', 'panier vide, commencer, catalogue', 4),
('Achat', 'Pouvez-vous sauvegarder mon panier pour plus tard?', 'Votre panier est sauvegardé dans votre session. Créez un compte pour sauvegarder définitivement vos articles préférés.', 'sauvegarder, compte, préféré, liste', 5),

-- CHECKOUT & PAYMENT
('Paiement', 'Quels modes de paiement acceptez-vous?', 'Actuellement, nous acceptons le paiement à la livraison (PAL). Vous pouvez payer en liquide au moment de recevoir votre commande.', 'paiement, carte bancaire, virement, cash', 1),
('Paiement', 'Est-ce que je dois créer un compte pour acheter?', 'Oui, vous devez créer un compte et être connecté pour passer une commande. C\'est rapide et gratuit!', 'compte, inscription, connexion, authentification', 2),
('Paiement', 'Comment puis-je voir le total de ma commande?', 'Dans le panier, vous verrez le sous-total, les frais de livraison et le total. La page de commande affiche aussi un résumé complet.', 'total, prix, somme, frais, calcul', 3),
('Paiement', 'Mes données de paiement sont-elles en sécurité?', 'Oui, nous utilisons le protocole HTTPS et le paiement à la livraison pour sécuriser vos transactions. Vos données personnelles sont protégées.', 'sécurité, chiffrement, protection, données', 4),
('Paiement', 'Que faire si mon paiement est refusé?', 'Comme nous utilisons le paiement à la livraison, il n\'y a pas de refus. Vous payerez directement au livreur.', 'refusé, erreur, problème, paiement', 5),
('Paiement', 'Recevrai-je une facture?', 'Oui, une facture sera incluse avec votre colis et vous recevrez aussi un reçu par email après le paiement.', 'facture, reçu, preuve, achat', 6),

-- SHIPPING & DELIVERY
('Livraison', 'Quel est le délai de livraison?', 'Livraison Gratuite: 5-7 jours ouvrables. Livraison Express: 2-3 jours ouvrables. Les délais commencent après la confirmation de la commande.', 'délai, jours, rapidité, expédition', 1),
('Livraison', 'Livrez-vous partout au Maroc?', 'Oui, nous livrons à toutes les villes principales du Maroc. Les frais de livraison peuvent varier selon votre localisation.', 'maroc, région, ville, couverture, zones', 2),
('Livraison', 'Quels sont les frais de livraison?', 'Livraison Gratuite: offerte (délai 5-7 jours). Livraison Express: 35 MAD (délai 2-3 jours). Les frais dépendent aussi de votre localisation.', 'frais, coût, tarif, prix', 2),
('Livraison', 'Puis-je suivre ma commande?', 'Oui! Un numéro de suivi vous sera envoyé par email dès que votre commande sera expédiée. Vous pouvez suivre en temps réel.', 'suivi, numéro, tracker, localisation', 4),
('Livraison', 'Puis-je changer mon adresse de livraison?', 'Veuillez nous contacter immédiatement si vous souhaitez modifier votre adresse. Nous ferons de notre mieux si la commande n\'a pas encore été expédiée.', 'adresse, modification, changement, livraison', 5),
('Livraison', 'Que se passe-t-il si je ne suis pas chez moi à la livraison?', 'Le livreur vous contactera. Vous pouvez arranger une nouvelle date/heure de livraison ou utiliser un point de retrait partenaire.', 'absent, home, relivraison, point retrait', 6),
('Livraison', 'Les parfums sont-ils emballés de manière sécurisée?', 'Oui, tous les parfums sont emballés avec soin dans du papier bulle et des boîtes de carton solides pour éviter les dommages pendant le transport.', 'emballage, protection, dommage, sécurité', 7),
('Livraison', 'Offrez-vous la livraison internationale?', 'Actuellement, nous ne livrons que au Maroc. Pour les livraisons internationales, veuillez nous contacter directement.', 'international, étranger, abroad, export', 8),
('Livraison', 'Puis-je récupérer ma commande en point de retrait?', 'Non, nous livrons uniquement à domicile. Mais vous pouvez nous contacter pour discuter des options.', 'retrait, magasin, pickup, lockers', 9),

-- RETURNS & REFUNDS
('Retours', 'Quelle est votre politique de retour?', 'Vous avez 14 jours après réception pour retourner un produit inutilisé dans son emballage original. Les articles endommagés ou utilisés ne peuvent pas être retournés.', 'retour, délai, conditions, remboursement', 1),
('Retours', 'Comment puis-je retourner un produit?', 'Contactez-nous à info@fragranceboutique.com avec votre numéro de commande. Nous vous fournirons les instructions de retour et un numéro de suivi.', 'retour, procédure, étapes, renvoi', 2),
('Retours', 'Qui paie les frais de retour?', 'Si le retour est dû à notre erreur (produit endommagé, article incorrect), nous payons. Pour les autres retours, le client paie les frais.', 'frais retour, coût, prise en charge', 3),
('Retours', 'Combien de temps prend le remboursement?', 'Une fois que nous recevons votre retour, nous le traitons dans les 5-7 jours ouvrables. Le remboursement sera effectué sur votre compte.', 'remboursement, délai, traitement, argent', 4),
('Retours', 'Puis-je retourner un produit ouvert?', 'Non, les produits ouverts ou utilisés ne peuvent pas être retournés. Ils doivent être dans un état neuf et inutilisé.', 'ouvert, utilisé, neuf, état', 5),
('Retours', 'Et si je reçois un produit endommagé?', 'Contactez-nous immédiatement avec des photos. Nous vous remplacerons le produit gratuitement ou vous rembourserons.', 'endommagé, cassé, défectueux, problème', 6),
('Retours', 'Puis-je retourner un produit sans raison?', 'Oui, tant qu\'il est inutilisé et dans son emballage original dans les 14 jours. Les frais de retour vous incomberont.', 'retour sans raison, remorse, annulation', 7),
('Retours', 'Quel est le processus exact de retour?', '1) Contactez-nous. 2) Recevez les instructions. 3) Préparez le colis. 4) Envoyez-le. 5) Nous recevons et vérifions. 6) Remboursement traité.', 'étapes, processus, comment retourner', 8),

-- ACCOUNT & PROFILE
('Compte', 'Comment créer un compte?', 'Cliquez sur "Register" en haut à droite, remplissez votre nom, email et mot de passe. Confirmez et vous êtes inscrit!', 'inscription, nouveau compte, enregistrement', 1),
('Compte', 'Comment me connecter?', 'Cliquez sur "Login", entrez votre email et mot de passe, puis cliquez "Se connecter".', 'connexion, login, authentification', 2),
('Compte', 'Comment réinitialiser mon mot de passe?', 'Sur la page de connexion, cliquez sur "Mot de passe oublié". Suivez les instructions envoyées à votre email pour réinitialiser.', 'mot de passe oublié, réinitialiser, reset', 3),
('Compte', 'Comment modifier mon profil?', 'Connectez-vous à votre compte et allez à la section "Mon profil" pour modifier vos informations personnelles.', 'profil, modifier, éditer, information', 4),
('Compte', 'Puis-je avoir plusieurs comptes?', 'Non, vous ne pouvez avoir qu\'un seul compte par email. Utilisez votre compte existant ou créez-en un nouveau avec un email différent.', 'comptes multiples, double, second', 5),
('Compte', 'Mes données personnelles sont-elles en sécurité?', 'Oui, nous protégeons vos données avec les normes de sécurité les plus élevées. Nous ne partageons jamais vos informations.', 'sécurité, confidentialité, protection, données', 6),
('Compte', 'Comment supprimer mon compte?', 'Contactez-nous à info@fragranceboutique.com si vous souhaitez supprimer votre compte. Nous traiterons votre demande dans les 5 jours ouvrables.', 'supprimer, suppression, désactiver, fermer', 7),
('Compte', 'Puis-je voir l\'historique de mes commandes?', 'Oui, connectez-vous à votre compte et allez à "Mes commandes" pour voir tous vos achats précédents.', 'historique, commandes, anciens achats', 8),

-- SEARCH & BROWSING
('Recherche', 'Comment puis-je trouver un parfum spécifique?', 'Utilisez la barre de recherche en haut du site ou parcourez notre catalogue en filtrant par genre (Hommes/Femmes). Vous pouvez aussi filtrer par prix.', 'recherche, trouver, filtre, catalogue', 1),
('Recherche', 'Pouvez-vous m\'aider à choisir un parfum?', 'Oui! Vous pouvez nous contacter à info@fragranceboutique.com ou par téléphone. Notre équipe vous donnera des conseils personnalisés.', 'conseil, aide, choix, recommandation', 2),
('Recherche', 'Comment fonctionnent les filtres de recherche?', 'Les filtres vous permettent de trier par genre (Hommes/Femmes), prix et marque. Sélectionnez vos critères et les résultats s\'affichent automatiquement.', 'filtre, tri, recherche, catégories', 3),
('Recherche', 'Pourquoi un produit n\'apparaît pas dans les résultats?', 'Le produit peut être en rupture de stock. Vérifiez les filtres appliqués ou contactez-nous pour vérifier la disponibilité.', 'rupture, stock, indisponible, absent', 4),

-- REVIEWS & RATINGS
('Avis', 'Puis-je laisser un avis sur un produit?', 'Oui, après avoir acheté un produit, vous pouvez laisser un avis et une note (1-5 étoiles). Cela aide les autres clients.', 'avis, évaluation, note, commentaire', 1),
('Avis', 'Vais-je recevoir un email pour noter mes achats?', 'Oui, après la livraison, vous recevrez un email vous demandant de noter votre expérience et le produit.', 'email, note, avis, demande', 2),
('Avis', 'Les avis des clients sont-ils authentiques?', 'Oui, tous les avis sont de clients vérifiés qui ont réellement acheté le produit. Nous supprimons les avis faux ou abusifs.', 'authentique, vrai, vérifié, faux', 3),

-- TECHNICAL ISSUES
('Technique', 'Le site ne charge pas correctement. Que faire?', 'Actualisez la page (Ctrl+F5), videz le cache de votre navigateur, ou essayez un autre navigateur. Si le problème persiste, contactez-nous.', 'site, charge, erreur, bug, problème', 1),
('Technique', 'Je reçois une erreur lors de la commande. Que faire?', 'Vérifiez votre connexion Internet, videz le cache, et réessayez. Si le problème persiste, contactez-nous.', 'erreur, commande, problème, technique', 2),
('Technique', 'Comment puis-je signaler un bug?', 'Envoyez un email détaillé à support@fragranceboutique.com avec une description du problème et des captures d\'écran.', 'bug, problème, signaler, erreur', 3),
('Technique', 'Le site est-il disponible sur mobile?', 'Oui, notre site est complètement responsive et fonctionne parfaitement sur tous les appareils (téléphone, tablette, ordinateur).', 'mobile, application, téléphone, responsive', 4),

-- SPECIAL REQUESTS
('Demandes spéciales', 'Pouvez-vous livrer pour une occasion spéciale?', 'Oui! Pour les cadeaux, nous pouvons ajouter un emballage spécial ou un message personnalisé. Contactez-nous.', 'cadeau, occasion, emballage, message', 1),
('Demandes spéciales', 'Offrez-vous un service de gift wrapping?', 'Oui, nous proposons un emballage cadeau élégant. Cochez l\'option lors de la commande.', 'cadeau, emballage, présent, gift', 2),
('Demandes spéciales', 'Puis-je commander pour quelqu\'un d\'autre?', 'Oui, lors de la commande, vous pouvez entrer une adresse de livraison différente. Vérifiez les détails correctement.', 'cadeau, autre personne, destinataire', 3),
('Demandes spéciales', 'Offrez-vous des commandes en gros?', 'Oui, pour les commandes en gros (revendeurs, petits magasins), contactez-nous pour des tarifs spéciaux.', 'gros, revendeur, entreprise, tarif', 4),

-- LOYALTY & REWARDS
('Fidélité', 'Y a-t-il un programme de fidélité?', 'Nous travaillons actuellement sur un programme de fidélité. Inscrivez-vous à notre newsletter pour être informé du lancement.', 'fidélité, programme, points, récompense', 1),
('Fidélité', 'Comment puis-je obtenir des réductions?', 'Suivez-nous sur les réseaux sociaux, inscrivez-vous à notre newsletter et vérifiez notre page "Promotions".', 'réduction, discount, offre, rabais', 2),
('Fidélité', 'Y a-t-il des offres pour les clients réguliers?', 'Oui, nous offrons des surprises spéciales à nos clients fidèles. Continuez à acheter et vous serez récompensé!', 'régulier, fidèle, client, récompense', 3),

-- NEWSLETTER & NOTIFICATIONS
('Newsletter', 'Comment m\'inscrire à la newsletter?', 'Allez à la section "Newsletter" en bas du site et entrez votre email. Vous recevrez nos meilleures offres directement.', 'newsletter, email, inscription, offres', 1),
('Newsletter', 'Combien de fois recevrai-je des emails?', 'Nous envoyons 1-2 emails par semaine avec les meilleures offres, nouveautés et conseils. Vous pouvez vous désabonner à tout moment.', 'fréquence, emails, désabonnement', 2),
('Newsletter', 'Comment puis-je me désabonner?', 'Cliquez sur le lien "Désabonnement" en bas de chaque email de newsletter.', 'désabonnement, arrêter, stop', 3);