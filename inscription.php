<?php
/**
 * inscription.php — Création d'un compte famille
 *
 * Formulaire d'inscription : nom de la famille, email (= identifiant), mot de passe.
 * Après soumission valide, redirige vers l'espace parent.
 */

require_once 'php/inscription.php'; // Valide le formulaire et insère en BDD

$pageTitle  = "S'inscrire - Ateliers du Mercredi";
$activePage = 'inscription';
$extraCSS   = ['inscription.css'];

require_once 'includes/header.php';
?>

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
                Déjà un compte ?
                <a href="connexion.php" style="color:#ff5e78; font-weight:bold;">Se connecter</a>
            </p>
        </div>
        <div class="image-container">
            <img src="images/create_acc.jpg" alt="Enfants ateliers" onerror="this.style.display='none'">
        </div>
    </div>
</div>

</body>
</html>