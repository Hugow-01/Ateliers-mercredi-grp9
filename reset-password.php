<?php
/**
 * reset-password.php — Réinitialisation du mot de passe via token
 */
require_once 'php/config.php';

$error       = '';
$success     = '';
$tokenValide = false;

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');

if (!$token) {
    header("Location: connexion.php");
    exit;
}

$db   = getDB();

// NOTE : la table s'appelle Reset_Token (avec majuscule) dans la BDD
$stmt = $db->prepare("
    SELECT rt.id, rt.id_famille, f.login AS email
    FROM Reset_Token rt
    JOIN Famille f ON f.id = rt.id_famille
    WHERE rt.token = ? AND rt.used = 0 AND rt.expire_at > NOW()
");
$stmt->execute([$token]);
$resetRow = $stmt->fetch();

if (!$resetRow) {
    $error = "Ce lien est invalide ou a expiré. Veuillez faire une nouvelle demande.";
} else {
    $tokenValide = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValide) {
    $mdp  = $_POST['mdp']  ?? '';
    $mdp2 = $_POST['mdp2'] ?? '';

    if (strlen($mdp) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($mdp !== $mdp2) {
        $error = "Les deux mots de passe ne correspondent pas.";
    } else {
        $hash = password_hash($mdp, PASSWORD_DEFAULT);
        $db->prepare("UPDATE Famille SET mdp = ? WHERE id = ?")
           ->execute([$hash, $resetRow['id_famille']]);
        // Invalider le token
        $db->prepare("UPDATE Reset_Token SET used = 1 WHERE id = ?")
           ->execute([$resetRow['id']]);

        $success     = "Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.";
        $tokenValide = false;
    }
}

$pageTitle  = 'Nouveau mot de passe - Ateliers du Mercredi';
$activePage = 'connexion';
$extraCSS   = ['connexion.css'];

require_once 'includes/header.php';
?>

<img src="images/dessin.jpg" alt="Enfants atelier" class="illustration-banner"
     onerror="this.style.height:'80px'; this.style.background='#fdd835'">

<div class="login-card-container">
    <?php if ($success): ?>
        <div class="alert alert-success" style="max-width:400px; width:100%; margin-bottom:15px;">
            <?= htmlspecialchars($success) ?>
        </div>
        <p style="text-align:center;">
            <a href="connexion.php" class="btn btn-primary" style="padding:12px 30px;">Se connecter</a>
        </p>

    <?php elseif ($error && !$tokenValide): ?>
        <div class="alert alert-error" style="max-width:400px; width:100%; margin-bottom:15px;">
            <?= htmlspecialchars($error) ?>
        </div>
        <p style="text-align:center;">
            <a href="mot-de-passe-oublie.php" style="color:#ff5e78; font-weight:bold;">
                Faire une nouvelle demande
            </a>
        </p>

    <?php else: ?>
        <div class="auth-box">
            <h2>Choisir un nouveau mot de passe</h2>
            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom:15px;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="reset-password.php">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="form-group">
                    <label>Nouveau mot de passe</label>
                    <input type="password" name="mdp" placeholder="Minimum 6 caractères" required>
                </div>
                <div class="form-group">
                    <label>Confirmer le mot de passe</label>
                    <input type="password" name="mdp2" placeholder="Répétez le mot de passe" required>
                </div>
                <button type="submit" class="btn-login">Enregistrer</button>
            </form>
        </div>
    <?php endif; ?>
</div>

</body>
</html>