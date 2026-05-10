<?php require_once 'php/profile-info.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile famille</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/ajouter-enfant.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="banner-yellow">
    <div class="container" style="display:flex; justify-content:space-between; align-items:center;">
        <h1 style="font-family:'Baloo 2'; color:#3e2723;">Profile famille</h1>
        <nav>
            <a href="index.php">Accueil</a>
            <a href="parent-enfants.php">Mon espace</a>
            <a href="deconnexion.php" style="color:#c0392b;">se déconnecter</a>
        </nav>
    </div>
</div>

<div class="container" style="margin-top:30px; padding-bottom:60px;">

    <?php
    $message = $_SESSION['message'] ?? '';
    $messageType = $_SESSION['messageType'] ?? '';
    // Clear the message so it doesn't show again on refresh
    unset($_SESSION['message'], $_SESSION['messageType']);?>

    <!-- <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?> -->

    <div class="add-child-container">
        <div class="form-part">
            <h2 style="font-family:'Baloo 2'; margin-top:0; color:#3e2723;">Informations de l'enfant</h2>

            <form method="POST" action="php/profile-info.php">
                <input type="hidden" name="action" value="changement_profile">
                <div class="form-group">
                    <label>Nom :</label>
                    <input type="text" name="nouveau_nom" 
                           value="<?= htmlspecialchars($_SESSION['nom'] ?? 'invalide') ?>" required>
                </div>
                <div class="form-group">
                    <label>Login/email :</label>
                    <input type="text" name="nouveau_login"
                           value="<?= htmlspecialchars($_SESSION['user'] ?? '') ?>" required readonly>
                </div>
                <div class="form-group">
                    <label>Nouveau mot de passe :</label>
                    <input type="password" name="nouveau_mdp" placeholder="Minimum 6 caractères">
                </div>
                <div class="form-group">
                    <label>Confirmer le mot de passe :</label>
                    <input type="password" name="nouveau_mdp2" placeholder="Répétez le mot de passe">
                </div>

                <div style="display:flex; gap:15px; margin-top:25px;">
                    <button type="submit" class="btn btn-primary" style="flex:1; padding:12px; font-size:1.1rem;">Sauvegarder</button>
                    <!-- <a href="parent-enfants.php" class="btn" style="flex:1; padding:12px; text-align:center; font-size:1.1rem; background:#eee; color:#333;">Annuler</a> -->
                </div>
            </form>
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
            <?php endif; ?>
        </div>

        <div class="image-part">
            <img src="images/famille.jpg" alt="Famille" style="max-width:75%; max-height:290px;" onerror="this.style.display='none'">
            <div class="yellow-strip">
                <span>Famille</span>
            </div>
        </div>
    </div>
</div>

</body>
</html>
