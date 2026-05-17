<?php
/**
 * ajouter-enfant.php — Ajout d'un enfant à un compte famille
 *
 * Simple formulaire nom / prénom / âge.
 * Un parent peut avoir plusieurs enfants (chacun peut être inscrit à des ateliers).
 */

require_once 'php/ajouter-enfant.php'; // Insère l'enfant en BDD et redirige si succès

$pageTitle  = 'Ajouter un enfant - Ateliers du Mercredi';
$activePage = 'espace';
$extraCSS   = ['ajouter-enfant.css'];

require_once 'includes/header.php';
?>

<div class="banner-yellow" style="padding:20px 0; margin-bottom:0;">
    <div class="container">
        <h1 style="font-family:'Baloo 2'; color:#3e2723;">Ajouter un enfant</h1>
    </div>
</div>

<div class="container" style="margin-top:30px; padding-bottom:60px;">
    <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <div class="add-child-container">
        <div class="form-part">
            <h2 style="font-family:'Baloo 2'; margin-top:0; color:#3e2723;">Informations de l'enfant</h2>
            <form method="POST" action="ajouter-enfant.php">
                <div class="form-group">
                    <label>Nom :</label>
                    <input type="text" name="nom" placeholder="ex: Dubois"
                           value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Prénom(s) :</label>
                    <input type="text" name="prenom" placeholder="ex: Marie Alice"
                           value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Âge :</label>
                    <input type="number" name="age" placeholder="ex: 8" min="3" max="17"
                           value="<?= htmlspecialchars($_POST['age'] ?? '') ?>" required>
                </div>
                <div style="display:flex; gap:15px; margin-top:25px;">
                    <button type="submit" class="btn btn-primary"
                            style="flex:1; padding:12px; font-size:1.1rem;">
                        Ajouter l'enfant
                    </button>
                    <a href="parent-enfants.php" class="btn"
                       style="flex:1; padding:12px; text-align:center; font-size:1.1rem; background:#eee; color:#333;">
                        Annuler
                    </a>
                </div>
            </form>
        </div>

        <!-- Illustration décorative -->
        <div class="image-part">
            <img src="images/create_acc.jpg" alt="Enfant poterie"
                 style="max-width:75%; max-height:290px;" onerror="this.style.display='none'">
            <div class="yellow-strip"><span>Ateliers</span></div>
        </div>
    </div>
</div>

</body>
</html>