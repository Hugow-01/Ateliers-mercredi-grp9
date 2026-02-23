<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom    = trim($_POST['nom'] ?? '');
    $login  = trim($_POST['email'] ?? '');
    $mdp    = $_POST['mdp'] ?? '';
    $mdp2   = $_POST['mdp2'] ?? '';

    if (!$nom || !$login || !$mdp || !$mdp2) {
        $error = "Veuillez remplir tous les champs.";
    } elseif (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } elseif (strlen($mdp) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($mdp !== $mdp2) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        try {
            $db = getDB();
            // Vérifier si le login existe déjà
            $stmt = $db->prepare("SELECT login FROM Famille WHERE login = ?");
            $stmt->execute([$login]);
            if ($stmt->fetch()) {
                $error = "Cet email est déjà utilisé. <a href='connexion.php'>Se connecter</a>";
            } else {
                $hash = password_hash($mdp, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO Famille (login, mdp, nom) VALUES (?, ?, ?)");
                $stmt->execute([$login, $hash, $nom]);

                // Connexion automatique après inscription
                $_SESSION['user']  = $login;
                $_SESSION['nom']   = $nom;
                $_SESSION['role']  = 'famille';
                header("Location: parent-enfants.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = "Erreur base de données : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S'inscrire - Ateliers du Mercredi</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .inscription-wrapper { display: flex; gap: 40px; align-items: flex-start; justify-content: center; flex-wrap: wrap; }
        .white-card { background: white; border-radius: 20px; padding: 35px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); flex: 1; max-width: 480px; }
        .form-title { font-family: 'Baloo 2', cursive; font-size: 2rem; margin-bottom: 25px; color: #3e2723; }
        .image-container { flex: 1; min-width: 280px; display: flex; align-items: center; justify-content: center; }
        .image-container img { width: 100%; border-radius: 20px; max-height: 420px; object-fit: cover; }
        .btn-submit { background: #ff5e78; color: white; border: none; padding: 14px 60px; font-family: 'Baloo 2', cursive; font-size: 1.6rem; border-radius: 15px; cursor: pointer; box-shadow: 0 5px 15px rgba(255,94,120,0.4); margin-top: 10px; transition: transform 0.2s; }
        .btn-submit:hover { transform: scale(1.04); }
        .row-2 { display: flex; gap: 10px; }
        .row-2 input { flex: 1; }
    </style>
</head>
<body>

<div class="banner-yellow">
    <div class="container" style="display:flex; justify-content:space-between; align-items:center;">
        <h1 style="font-family:'Baloo 2'; color:#3e2723;">S'inscrire</h1>
        <nav>
            <a href="index.php">Accueil</a>
            <a href="activites.php">les activités</a>
            <a href="connexion.php">se connecter</a>
        </nav>
    </div>
</div>

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
                    <input type="text" name="nom" placeholder="ex: Famille Dubois" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Email (servira de login) :</label>
                    <input type="email" name="email" placeholder="ex: marie.alice@gmail.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
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
                Déjà un compte ? <a href="connexion.php" style="color:#ff5e78; font-weight:bold;">Se connecter</a>
            </p>
        </div>

        <div class="image-container">
            <img src="create_acc.jpg" alt="Enfants ateliers" onerror="this.style.display='none'">
        </div>
    </div>
</div>

</body>
</html>
