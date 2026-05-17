<?php
/**
 * modifier-compte-parent.php — Modification du compte famille
 *
 * Permet au parent de changer son nom affiché, son email (= identifiant)
 * et éventuellement son mot de passe. Le mot de passe actuel est requis
 * pour valider toute modification (sécurité).
 */

require_once 'php/modifier-compte-parent.php'; // Charge $famille, $error, $success

$pageTitle  = 'Mon compte - Ateliers du Mercredi';
$activePage = 'compte';
$extraCSS   = ['ajouter-enfant.css'];

require_once 'includes/header.php';
?>

<div class="banner-yellow" style="padding:20px 0; margin-bottom:0;">
    <div class="container">
        <h1 style="font-family:'Baloo 2'; color:#3e2723;">Mon compte</h1>
    </div>
</div>

<div class="container" style="margin-top:35px; padding-bottom:60px;">
    <?php if ($error):   ?><div class="alert alert-error"   style="max-width:520px; margin:0 auto 20px;"><?= htmlspecialchars($error)   ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success" style="max-width:520px; margin:0 auto 20px;"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <div class="compte-card">
        <h2 style="font-family:'Baloo 2'; color:#3e2723; margin-top:0;">Modifier mon compte</h2>

        <form method="POST" action="modifier-compte-parent.php">
            <!-- Nom de la famille -->
            <div class="section-label">Nom de la famille</div>
            <div class="form-group">
                <input type="text" name="nom"
                       value="<?= htmlspecialchars($_POST['nom'] ?? $famille['nom']) ?>"
                       placeholder="ex: Famille Dubois" required>
            </div>

            <hr class="divider-section">

            <!-- Email (= identifiant de connexion) -->
            <div class="section-label">Adresse email (identifiant de connexion)</div>
            <div class="form-group">
                <input type="email" name="nouvel_email"
                       value="<?= htmlspecialchars($_POST['nouvel_email'] ?? $famille['login']) ?>"
                       placeholder="ex: marie@gmail.com" required>
                <div class="hint">Modifier votre email changera votre identifiant de connexion.</div>
            </div>

            <hr class="divider-section">

            <!-- Changement de mot de passe — le champ "actuel" est toujours obligatoire -->
            <div class="section-label">Changer le mot de passe (laisser vide si pas de changement)</div>
            <div class="form-group">
                <label>Mot de passe actuel <span style="color:#c0392b;">*</span></label>
                <input type="password" name="mdp_actuel" placeholder="Votre mot de passe actuel" required>
            </div>
            <div class="form-group">
                <label>Nouveau mot de passe</label>
                <input type="password" name="nouveau_mdp" placeholder="Laisser vide pour ne pas changer">
                <div class="hint">Au moins 6 caractères si vous souhaitez en choisir un nouveau.</div>
            </div>
            <div class="form-group">
                <label>Confirmer le nouveau mot de passe</label>
                <input type="password" name="confirmer_mdp" placeholder="Répétez le nouveau mot de passe">
            </div>

            <button type="submit" class="btn-save">Enregistrer les modifications</button>
        </form>
        <a href="parent-enfants.php"
           style="display:block; text-align:center; margin-top:14px; color:#888; font-size:0.9rem; text-decoration:none;">
            ← Retour à mon espace
        </a>
    </div>
</div>

</body>
</html>