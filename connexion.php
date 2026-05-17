<?php
/**
 * connexion.php — Page de connexion (parent et responsable)
 *
 * Deux rôles sont gérés via des onglets JS :
 *  - "Famille"      : login = email + mdp hashé en BDD table Famille
 *  - "Responsable"  : login = email + mdp hashé en BDD table Responsable
 *
 * Après authentification, redirige vers l'espace correspondant.
 */

require_once 'php/connexion.php'; // Gère le POST de connexion et démarre la session

$pageTitle  = 'Se connecter - Ateliers du Mercredi';
$activePage = 'connexion';
$extraCSS   = ['connexion.css'];

require_once 'includes/header.php';
?>

<!-- Image bannière décorative -->
<img src="images/dessin.jpg" alt="Enfants atelier" class="illustration-banner"
     onerror="this.style.height:'80px'; this.style.background='#fdd835'">

<div class="login-card-container">

    <!-- Affichage erreur de connexion -->
    <?php if ($error): ?>
        <div class="alert alert-error" style="max-width:400px; width:100%; margin-bottom:15px;">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <div class="auth-box">
        <h2>Se connecter</h2>

        <!-- Onglets de sélection du rôle (bascule JS, pas de rechargement) -->
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
                <!-- Lien visible uniquement pour les familles (pas pour les responsables) -->
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
/** Change le rôle sélectionné et adapte l'interface (onglet actif + lien MDP oublié) */
function setRole(role, el){
    document.getElementById('role-input').value = role;
    document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    // Le lien "Mot de passe oublié" n'existe que pour les comptes famille
    document.getElementById('forgot-link').style.display = (role === 'responsable') ? 'none' : 'block';
}
</script>

</body>
</html>