<?php
/**
 * mot-de-passe-oublie.php — Demande de réinitialisation de mot de passe
 */
require_once 'php/config.php';
require_once 'php/mail.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(strtolower($_POST['email'] ?? ''));

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Veuillez saisir une adresse email valide.";
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, login FROM Famille WHERE login = ?");
        $stmt->execute([$email]);
        $famille = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($famille) {
            $idFamille = (int)$famille['id'];

            // Invalider les anciens tokens non utilisés
            // NOTE : la table s'appelle Reset_Token (avec majuscules) dans la BDD
            $db->prepare("UPDATE Reset_Token SET used = 1 WHERE id_famille = ? AND used = 0")
               ->execute([$idFamille]);

            // Créer un nouveau token valable 1 heure
            $token = bin2hex(random_bytes(32));
            $expireAt = date('Y-m-d H:i:s', time() + 3600);

            $db->prepare("INSERT INTO Reset_Token (id_famille, token, expire_at) VALUES (?, ?, ?)")
               ->execute([$idFamille, $token, $expireAt]);

            envoyerMailMotDePasseOublie($email, $token);
        }

        // Message générique (ne révèle pas si le compte existe)
        $success = "Si cette adresse email est associée à un compte, vous recevrez un lien de réinitialisation dans quelques minutes.";
    }
}

$pageTitle = 'Mot de passe oublié - Ateliers du Mercredi';
$activePage = 'connexion';
$extraCSS = ['connexion.css'];

require_once 'includes/header.php';
?>
<img src="images/dessin.jpg" alt="Enfants atelier" class="illustration-banner"
     onerror="this.style.height:'80px'; this.style.background='#fdd835'">

<div class="login-card-container">
    <?php if ($success): ?>
        <div class="alert alert-success"
             style="max-width:420px; width:100%; margin-bottom:20px; text-align:center;">
            <?= htmlspecialchars($success) ?>
        </div>
        <p style="text-align:center;">
            <a href="connexion.php" style="color:#ff5e78; font-weight:bold;">← Retour à la connexion</a>
        </p>
    <?php else: ?>
        <div class="auth-box">
            <h2>Réinitialiser mon mot de passe</h2>
            <p style="color:#666; font-size:.9rem; margin-bottom:20px;">
                Saisissez votre adresse email. Si elle correspond à un compte,
                vous recevrez un lien de réinitialisation.
            </p>
            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom:15px;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="mot-de-passe-oublie.php">
                <div class="form-group">
                    <label>Adresse email</label>
                    <input type="email" name="email"
                           placeholder="ex: marie@gmail.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           required>
                </div>
                <button type="submit" class="btn-login">Envoyer le lien</button>
            </form>
            <div style="text-align:center; margin-top:15px;">
                <a href="connexion.php" style="color:#888; font-size:.9rem;">← Retour à la connexion</a>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>