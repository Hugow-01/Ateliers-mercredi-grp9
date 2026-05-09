<?php require_once 'php/connexion.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Se connecter - Ateliers du Mercredi</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/connexion.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="connexion-header">
    <h1>Se connecter</h1>
    <nav class="connexion-nav">
        <a href="index.php">Accueil</a>
        <a href="activites.php">les activités</a>
        <a href="inscription.php">s'inscrire</a>
    </nav>
</div>

<img src="images/dessin.jpg" alt="Enfants atelier" class="illustration-banner"
     onerror="this.style.height='80px'; this.style.background='#fdd835'">

<div class="login-card-container">
    <?php if ($error): ?>
        <div class="alert alert-error" style="max-width:400px; width:100%; margin-bottom:15px;">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <div class="auth-box">
        <h2>se connecter</h2>
        <div class="role-tabs">
            <div class="role-tab active" onclick="setRole('famille', this)">Parent</div>
            <div class="role-tab" onclick="setRole('responsable', this)">Responsable</div>
        </div>
        <form method="POST" action="connexion.php">
            <input type="hidden" name="role" id="role-input" value="famille">
            <div class="form-group">
                <label>Email / Login</label>
                <input type="text" name="login"
                       placeholder="ex: marie.alice@gmail.com"
                       value="<?= htmlspecialchars($_POST['login'] ?? '') ?>"
                       required>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="mdp" placeholder="••••••••" required>
                <a href="mot-de-passe-oublie.php" class="forgot-link" id="forgot-link">
                    Mot de passe oublié ?
                </a>
            </div>
            <button type="submit" class="btn-login">Se connecter</button>
        </form>
        <div class="divider">Pas encore de compte ?</div>
        <p style="text-align:center;">
            <a href="inscription.php" style="color:#ff5e78; font-weight:bold;">Créer un compte famille</a>
        </p>
    </div>
</div>

<script>
function setRole(role, el) {
    document.getElementById('role-input').value = role;
    document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    // Cacher le lien mot de passe oublié pour les responsables
    document.getElementById('forgot-link').style.display = (role === 'responsable') ? 'none' : 'block';
}
</script>
</body>
</html>