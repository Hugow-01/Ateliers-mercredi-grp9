<?php
require_once 'config.php';

// Redirection si déjà connecté
if (isLoggedIn()) {
    header("Location: " . (isAdmin() ? 'admin-dashboard.php' : 'parent-enfants.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $mdp   = $_POST['mdp'] ?? '';
    $role  = $_POST['role'] ?? 'famille';

    if (!$login || !$mdp) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        try {
            $db = getDB();
            $table = ($role === 'responsable') ? 'Responsable' : 'Famille';
            $stmt  = $db->prepare("SELECT * FROM `$table` WHERE login = ?");
            $stmt->execute([$login]);
            $user  = $stmt->fetch();

            if ($user && password_verify($mdp, $user['mdp'])) {
                $_SESSION['user'] = $user['login'];
                $_SESSION['nom']  = $user['nom'];
                $_SESSION['role'] = $role;
                header("Location: " . ($role === 'responsable' ? 'admin-dashboard.php' : 'parent-enfants.php'));
                exit;
            } else {
                $error = "Identifiant ou mot de passe incorrect.";
            }
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Se connecter - Ateliers du Mercredi</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .connexion-header { background-color: #fdd835; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        .connexion-header h1 { margin: 0; font-family: 'Baloo 2'; color: #3e2723; font-size: 2.2rem; }
        .connexion-nav a { margin-left: 15px; text-decoration: underline; font-size: 0.9rem; font-weight: bold; color: #333; }
        .illustration-banner { width: 100%; height: 300px; object-fit: cover; object-position: center top; }
        .login-card-container { margin-top: -110px; display: flex; justify-content: center; flex-direction: column; align-items: center; padding-bottom: 50px; position: relative; z-index: 10; }
        .auth-box { background: white; padding: 35px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.12); width: 100%; max-width: 400px; }
        .auth-box h2 { font-family: 'Baloo 2'; font-size: 1.8rem; margin-bottom: 22px; color: #3e2723; }
        .role-tabs { display: flex; gap: 10px; margin-bottom: 20px; }
        .role-tab { flex: 1; text-align: center; padding: 10px; border-radius: 10px; border: 2px solid #eee; cursor: pointer; font-weight: bold; font-size: 0.9rem; transition: 0.2s; }
        .role-tab.active { border-color: #ff5e78; background: #fff0f3; color: #ff5e78; }
        .btn-login { background: #ff5e78; color: white; border: none; padding: 14px; width: 100%; font-family: 'Baloo 2'; font-size: 1.2rem; border-radius: 10px; cursor: pointer; margin-top: 10px; box-shadow: 0 4px 10px rgba(255,94,120,0.3); transition: transform 0.2s; }
        .btn-login:hover { transform: scale(1.02); }
        .divider { text-align: center; color: #999; margin: 18px 0 5px; font-size: 0.9rem; }
    </style>
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

<img src="dessin.jpg" alt="Enfants atelier" class="illustration-banner" onerror="this.style.height='80px'; this.style.background='#fdd835'">

<div class="login-card-container">

    <?php if ($error): ?>
        <div class="alert alert-error" style="max-width:400px; width:100%; margin-bottom:15px;"><?= $error ?></div>
    <?php endif; ?>

    <div class="auth-box">
        <h2>se connecter</h2>

        <!-- Onglets rôle -->
        <div class="role-tabs">
            <div class="role-tab active" onclick="setRole('famille', this)" id="tab-famille">Parent</div>
            <div class="role-tab" onclick="setRole('responsable', this)" id="tab-responsable">Responsable</div>
        </div>

        <form method="POST" action="connexion.php">
            <input type="hidden" name="role" id="role-input" value="famille">

            <div class="form-group">
                <label>Email / Login</label>
                <input type="text" name="login" placeholder="ex: marie.alice@gmail.com" value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="mdp" placeholder="••••••••" required>
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
}
</script>
</body>
</html>
