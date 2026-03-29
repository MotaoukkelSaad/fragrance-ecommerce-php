<?php
session_start();
require 'config.php';
require 'auth.php';

if (Auth::isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];

    $auth = new Auth($conn);
    $result = $auth->login($email, $password);

    if ($result['success']) {
        header('Location: index.php');
        exit();
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — FragranceBoutique</title>
    <link rel="stylesheet" href="assets/style.css">
     <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-container { max-width: 400px; width: 100%; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .auth-container h2 { text-align: center; color: #2c3e50; margin-bottom: 30px; font-size: 28px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #2c3e50; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; font-size: 14px; transition: border-color 0.3s; }
        .form-group input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 5px rgba(102, 126, 234, 0.3); }
        .btn-login { width: 100%; padding: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; transition: transform 0.3s; }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        .error { color: #e74c3c; margin-bottom: 15px; padding: 12px; background: #ffebee; border-radius: 4px; border-left: 4px solid #e74c3c; }
        .link-register { text-align: center; margin-top: 20px; color: #7f8c8d; }
        .link-register a { color: #667eea; text-decoration: none; font-weight: bold; }
        .link-register a:hover { text-decoration: underline; }
        .demo-info { background: #e8f5e9; padding: 12px; border-radius: 4px; margin-top: 20px; font-size: 12px; color: #27ae60; }
    </style>
</head>
<body class="auth-page">

<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-icon">🔐</div>
        <h1>Connexion</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <span>❌</span> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="auth-form">
            <div class="form-group">
                <label for="email">Adresse Email</label>
                <input type="email" id="email" name="email" placeholder="votre@email.com" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" placeholder="Votre mot de passe" required>
            </div>

            <button type="submit" class="btn btn-auth">Se connecter</button>
        </form>

        <div class="auth-footer">
            <p>Pas encore de compte? <a href="register.php">S'inscrire ici</a></p>
        </div>

        <div class="demo-box">
        </div>
    </div>
</div>

</body>
</html>