<?php
/**
 * modifier-enfant.php — Modification des infos d'un enfant
 *
 * Reçoit l'id de l'enfant en GET (?id=X), vérifie qu'il appartient bien
 * à la famille connectée, puis permet de modifier nom / prénom / âge.
 */

require_once 'php/modifier-enfant.php'; // Charge $enfant, $error, $success

$pageTitle  = 'Modifier un enfant - Ateliers du Mercredi';
$activePage = 'espace';
$extraCSS   = ['ajouter-enfant.css'];

require_once 'includes/header.php';
?>

<div class="banner-yellow" style="padding:20px 0; margin-bottom:0;">
    <div class="container">
        <h1 style="font-family:'Baloo 2'; color:#3e2723;">Modifier un enfant</h1>
    </div>
</div>

<div class="container" style="margin-top:35px; padding-bottom:60px;">
    <div class="add-child-container">
        <div class="form-part">

            <!-- Badge + nom de l'enfant en cours de modification -->
            <div class="modif-badge">Modification du profil enfant</div>
            <h2 style="font-family:'Baloo 2'; margin-top:0; margin-bottom:6px; color:#3e2723;">
                <?= htmlspecialchars($enfant['prenom'].' '.$enfant['nom']) ?>
            </h2>

            <!-- Récapitulatif des valeurs actuelles (utile pour ne pas avoir à tout ressaisir) -->
            <div class="current-values">
                Valeurs actuelles —
                <strong>Nom :</strong> <?= htmlspecialchars($enfant['nom']) ?> &nbsp;·&nbsp;
                <strong>Prénom :</strong> <?= htmlspecialchars($enfant['prenom']) ?> &nbsp;·&nbsp;
                <strong>Âge :</strong> <?= htmlspecialchars($enfant['age']) ?> ans
            </div>

            <?php if ($error):   ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
            <?php if ($success): ?>
            <div class="success-box">
                <span style="font-size:1.2rem;">✔</span> <?= $success ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="modifier-enfant.php">
                <input type="hidden" name="id" value="<?= $enfant['id'] ?>">
                <div class="form-group">
                    <label>Nom :</label>
                    <input type="text" name="nom"
                           value="<?= htmlspecialchars($_POST['nom'] ?? $enfant['nom']) ?>"
                           placeholder="ex: Dubois" required>
                    <div class="field-hint">Nom de famille tel qu'il apparaît sur les documents officiels</div>
                </div>
                <div class="form-group">
                    <label>Prénom(s) :</label>
                    <input type="text" name="prenom"
                           value="<?= htmlspecialchars($_POST['prenom'] ?? $enfant['prenom']) ?>"
                           placeholder="ex: Marie Alice" required>
                </div>
                <div class="form-group">
                    <label>Âge :</label>
                    <input type="number" name="age" min="1" max="17"
                           value="<?= htmlspecialchars($_POST['age'] ?? $enfant['age']) ?>"
                           placeholder="ex: 8" required>
                    <div class="field-hint">Entre 1 et 17 ans</div>
                </div>
                <button type="submit" class="btn-save">✔ Enregistrer les modifications</button>
            </form>
            <a href="parent-enfants.php" class="btn-cancel">← Retour à mon espace</a>
        </div>

        <!-- Illustration identique à ajouter-enfant -->
        <div class="image-part">
            <img src="images/create_acc.jpg" alt="Modifier enfant"
                 style="max-width:75%; max-height:290px;" onerror="this.style.display='none'">
            <div class="yellow-strip"><span>Ateliers</span></div>
        </div>
    </div>
</div>

</body>
</html>