<?php require_once 'php/modifier-compte-parent.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon compte - Ateliers du Mercredi</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/ajouter-enfant.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .compte-card {
            background: white;
            border-radius: 18px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            padding: 35px 40px;
            max-width: 520px;
            margin: 0 auto;
        }
        .section-label {
            font-size: 0.8rem;
            font-weight: 700;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 22px 0 10px;
        }
        .divider-section {
            border: none;
            border-top: 2px solid #f0f0f0;
            margin: 22px 0;
        }
        .btn-save {
            background: #ff5e78;
            color: white;
            border: none;
            padding: 13px 0;
            width: 100%;
            font-family: 'Baloo 2', cursive;
            font-size: 1.1rem;
            border-radius: 12px;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn-save:hover { opacity: 0.9; }
        .info-login {
            background: #f0f4fb;
            border: 1px solid #d0dcf0;
            border-radius: 8px;
            padding: 10px 14px;
            color: #555;
            font-size: 0.9rem;
        }
        .hint {
            font-size: 0.75rem;
            color: #aaa;
            margin-top: 4px;
        }
    </style>
</head>
<body>

<div class="banner-yellow">
    <div class="container" style="display:flex; justify-content:space-between; align-items:center;">
        <h1 style="font-family:'Baloo 2'; color:#3e2723;">Mon compte</h1>
        <nav>
            <a href="index.php">Accueil</a>
            <a href="parent-enfants.php">Mon espace</a>
            <a href="deconnexion.php" style="color:#c0392b;">se deconnecter</a>
        </nav>
    </div>
</div>

<div class="container" style="margin-top:35px; padding-bottom:60px;">

    <?php if ($error): ?>
        <div class="alert alert-error" style="max-width:520px; margin:0 auto 20px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success" style="max-width:520px; margin:0 auto 20px;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="compte-card">
        <h2 style="font-family:'Baloo 2'; color:#3e2723; margin-top:0;">Modifier mon compte</h2>

        <div class="section-label">Identifiant (non modifiable)</div>
        <div class="info-login"><?= htmlspecialchars($famille['login']) ?></div>
        <div class="hint">L'email sert de login, il ne peut pas etre modifie.</div>

        <hr class="divider-section">

        <form method="POST" action="modifier-compte-parent.php">

            <div class="section-label">Nom de la famille</div>
            <div class="form-group">
                <input type="text" name="nom"
                       value="<?= htmlspecialchars($_POST['nom'] ?? $famille['nom']) ?>"
                       placeholder="ex: Famille Dubois" required>
            </div>

            <hr class="divider-section">

            <div class="section-label">Changer le mot de passe (laisser vide si pas de changement)</div>

            <div class="form-group">
                <label>Mot de passe actuel (obligatoire pour confirmer)</label>
                <input type="password" name="mdp_actuel" placeholder="Votre mot de passe actuel" required>
            </div>

            <div class="form-group">
                <label>Nouveau mot de passe</label>
                <input type="password" name="nouveau_mdp" placeholder="Laisser vide pour ne pas changer">
                <div class="hint">Au moins 6 caracteres si vous souhaitez en choisir un nouveau.</div>
            </div>

            <div class="form-group">
                <label>Confirmer le nouveau mot de passe</label>
                <input type="password" name="confirmer_mdp" placeholder="Repetez le nouveau mot de passe">
            </div>

            <button type="submit" class="btn-save">Enregistrer les modifications</button>
        </form>

        <a href="parent-enfants.php" style="display:block; text-align:center; margin-top:14px; color:#888; font-size:0.9rem; text-decoration:none;">
            Retour a mon espace
        </a>
    </div>
</div>

</body>
</html>
