<?php
require_once 'config.php';
requireParent();

$error = '';
$success = '';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom    = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $age    = intval($_POST['age'] ?? 0);

    if (!$nom || !$prenom || $age < 1) {
        $error = "Veuillez remplir tous les champs correctement.";
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO Enfant (nom, prenom, age, login_famille) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $age, $_SESSION['user']]);
            header("Location: parent-enfants.php");
            exit;
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
    <title>Ajouter un enfant</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

</head>
<body>

<div class="banner-yellow">
    <div class="container" style="display:flex; justify-content:space-between; align-items:center;">
        <h1 style="font-family:'Baloo 2'; color:#3e2723;">Ajouter un enfant</h1>
        <nav>
            <a href="index.php">Accueil</a>
            <a href="parent-enfants.php">Mon espace</a>
            <a href="deconnexion.php" style="color:#c0392b;">se déconnecter</a>
        </nav>
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
                    <input type="text" name="nom" placeholder="ex: Dubois" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Prénom(s) :</label>
                    <input type="text" name="prenom" placeholder="ex: Marie Alice" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Âge :</label>
                    <input type="number" name="age" placeholder="ex: 8" min="3" max="17" value="<?= htmlspecialchars($_POST['age'] ?? '') ?>" required>
                </div>

                <div style="display:flex; gap:15px; margin-top:25px;">
                    <button type="submit" class="btn btn-primary" style="flex:1; padding:12px; font-size:1.1rem;">Ajouter l'enfant</button>
                    <a href="parent-enfants.php" class="btn" style="flex:1; padding:12px; text-align:center; font-size:1.1rem; background:#eee; color:#333;">Annuler</a>
                </div>
            </form>
        </div>

        <div class="image-part">
            <img src="create_acc.jpg" alt="Enfant poterie" style="max-width:75%; max-height:290px;" onerror="this.style.display='none'">
            <div class="yellow-strip">
                <span>Ateliers</span>
            </div>
        </div>
    </div>
</div>

</body>
</html>
