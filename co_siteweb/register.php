<?php
session_start();
require 'config.php';
require 'auth.php';

if (Auth::isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($name) || empty($email) || empty($password)) {
        $error = '❌ Tous les champs sont requis';
    } elseif ($password !== $confirm_password) {
        $error = '❌ Les mots de passe ne correspondent pas';
    } elseif (strlen($password) < 6) {
        $error = '❌ Le mot de passe doit contenir au moins 6 caractères';
    } else {
        $auth = new Auth($conn);
        $result = $auth->register($name, $email, $password);
        
        if ($result['success']) {
            $success = $result['message'];
            echo '<script>setTimeout(() => window.location.href="login.php", 2500);</script>';
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — FragranceBoutique</title>
    <link rel="stylesheet" href="assets/style.css">
     <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-container { max-width: 400px; width: 100%; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .auth-container h2 { text-align: center; color: #2c3e50; margin-bottom: 30px; font-size: 28px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #2c3e50; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        .form-group input:focus { outline: none; border-color: #27ae60; }
        .btn-register { width: 100%; padding: 12px; background: linear-gradient(135deg, #27ae60 0%, #229954 100%); color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; }
        .btn-register:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(39, 174, 96, 0.4); }
        .error { color: #e74c3c; margin-bottom: 15px; padding: 12px; background: #ffebee; border-radius: 4px; border-left: 4px solid #e74c3c; }
        .success { color: #27ae60; margin-bottom: 15px; padding: 12px; background: #e8f5e9; border-radius: 4px; border-left: 4px solid #27ae60; }
        .link-login { text-align: center; margin-top: 20px; }
        .link-login a { color: #667eea; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body class="auth-page">

<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-icon">✍️</div>
        <h1>Créer un Compte</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <span>❌</span> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">
                <span>✅</span> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="name">Nom Complet</label>
                <input type="text" id="name" name="name" placeholder="Votre nom complet" required>
            </div>

            <div class="form-group">
                <label for="email">Adresse Email</label>
                <input type="email" id="email" name="email" placeholder="votre@email.com" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" placeholder="Minimum 6 caractères" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirmer le mot de passe" required>
            </div>

            <button type="submit" class="btn btn-auth">Créer un Compte</button>
        </form>

        <div class="auth-footer">
            <p>Vous avez déjà un compte? <a href="login.php">Se connecter ici</a></p>
        </div>
    </div>
</div>

</body>
</html>