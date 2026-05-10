<?php require_once 'php/inscription.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S'inscrire - Ateliers du Mercredi</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/inscription.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="banner-yellow">
    <div class="container" style="display:flex; justify-content:space-between; align-items:center;">
        <h1 style="font-family:'Baloo 2'; color:#3e2723;">S'inscrire</h1>
        <nav>
            <a href="index.php">Accueil</a>
            <a href="activites.php">les activités</a>
            <a href="connexion.php">se connecter</a>
        </nav>
    </div>
</div>

<div class="container" style="padding-bottom:60px;">

    <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <div class="inscription-wrapper">
        <div class="white-card">
            <h2 class="form-title">Créer un compte famille</h2>

            <form method="POST" action="inscription.php">
                <div class="form-group">
                    <label>Nom de la famille :</label>
                    <input type="text" name="nom" placeholder="ex: Famille Dubois"
                           value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Email (servira de login) :</label>
                    <input type="email" name="email" placeholder="ex: marie.alice@gmail.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Mot de passe :</label>
                    <input type="password" name="mdp" placeholder="Minimum 6 caractères" required>
                </div>
                <div class="form-group">
                    <label>Confirmer le mot de passe :</label>
                    <input type="password" name="mdp2" placeholder="Répétez le mot de passe" required>
                </div>

                <div style="text-align:center; margin-top:25px;">
                    <button type="submit" class="btn-submit">S'inscrire</button>
                </div>
            </form>

            <p style="text-align:center; margin-top:20px; color:#666;">
                Déjà un compte ? <a href="connexion.php" style="color:#ff5e78; font-weight:bold;">Se connecter</a>
            </p>
        </div>

        <div class="image-container">
            <img src="images/create_acc.jpg" alt="Enfants ateliers" onerror="this.style.display='none'">
        </div>
    </div>
</div>

</body>
</html>
