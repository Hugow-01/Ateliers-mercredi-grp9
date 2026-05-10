<?php require_once 'php/modifier-compte-parent.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon compte - Ateliers du Mercredi</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/ajouter-enfant.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="banner-yellow">
    <div class="container" style="display:flex; justify-content:space-between; align-items:center;">
        <h1 style="font-family:'Baloo 2'; color:#3e2723;">Mon compte</h1>
        <nav>
            <a href="index.php">Accueil</a>
            <a href="parent-enfants.php">Mon espace</a>
            <a href="deconnexion.php" style="color:#c0392b;">se déconnecter</a>
        </nav>
    </div>
</div>

<div class="container" style="margin-top:30px; padding-bottom:60px;">

    <?php if ($error): ?>
        <div class="alert alert-error" style="max-width:520px; margin:0 auto 20px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success" style="max-width:520px; margin:0 auto 20px;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="add-child-container">
        <div class="form-part">
            <h2 style="font-family:'Baloo 2'; color:#3e2723; margin-top:0;">Modifier mon compte</h2>

            <form method="POST" action="modifier-compte-parent.php">
                <input type="hidden" name="action" value="changement_profile">
                <div class="form-group">
                    <label>Nom de la famille :</label>
                    <input type="text" name="nom" 
                           value="<?= htmlspecialchars($_SESSION['nom'] ?? 'invalide') ?>" required>
                </div>

                <div class="form-group">
                    <label>Adresse email (identifiant de connexion) :</label>
                    <input type="text" name="nouvel_email" placeholder="ex: marie@gmail.com"
                           value="<?= htmlspecialchars($_SESSION['user'] ?? '') ?>" required>
                    <div class="hint">Modifier votre email changera votre identifiant de connexion.</div>
                </div>

                <hr class="divider-section">
                
                <div class="form-group">
                    <label>Mot de passe actuel <span style="color:#c0392b;">*</span></label>
                    <input type="password" name="mdp_actuel" placeholder="Votre mot de passe actuel" required>
                </div>
                <div class="section-label">Changer le mot de passe (laisser vide si pas de changement)</div>
                <div class="form-group">
                    <label>Nouveau mot de passe :</label>
                    <input type="password" name="nouveau_mdp" placeholder="Laisser vide pour ne pas changer">
                </div>
                <div class="form-group">
                    <label>Confirmer le mot nouveau de passe :</label>
                    <input type="password" name="confirmer_mdp" placeholder="Répétez le nouveau mot de passe">
                </div>

                <div style="display:flex; gap:15px; margin-top:25px;">
                    <button type="submit" class="btn btn-primary" style="flex:1; padding:12px; font-size:1.1rem;">Enregistrer les modifications</button>
                    <a href="parent-enfants.php" class="btn" style="flex:1; padding:12px; text-align:center; font-size:1.1rem; background:#eee; color:#333;">Retour à mon espace</a>
                </div>
            </form>
        </div>

        <div class="image-part">
            <img src="images/famille.jpg" alt="Famille" style="max-width:50%; max-height:290px;" onerror="this.style.display='none'">
            <div class="yellow-strip">
                <span>Famille</span>
            </div>
        </div>
    </div>

    <!-- <div class="add-child-container">
        <div class="form-part">
            <h2 style="font-family:'Baloo 2'; color:#3e2723; margin-top:0;">Liste Attente</h2>
            <hr class="divider-section">
        </div>
    </div> -->
</div>

</body>
</html>
