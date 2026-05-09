<?php require_once 'php/modifier-enfant.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un enfant - Ateliers du Mercredi</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/ajouter-enfant.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .modif-badge {
            display: inline-block;
            background: #e8f0fe;
            border: 1px solid #c5d4ef;
            border-radius: 8px;
            padding: 4px 12px;
            font-size: .82rem;
            color: #1a5fb4;
            font-weight: 600;
            margin-bottom: 18px;
        }
        .field-hint {
            font-size: .75rem;
            color: #94a3b8;
            margin-top: 3px;
        }
        .btn-save {
            background: #ff5e78;
            color: white;
            border: none;
            padding: 13px 0;
            width: 100%;
            font-family: 'Baloo 2', cursive;
            font-size: 1.15rem;
            border-radius: 12px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(255,94,120,.3);
            transition: transform .2s, box-shadow .2s;
            margin-top: 6px;
        }
        .btn-save:hover {
            transform: scale(1.02);
            box-shadow: 0 6px 16px rgba(255,94,120,.4);
        }
        .btn-cancel {
            display: block;
            text-align: center;
            margin-top: 12px;
            color: #888;
            font-size: .9rem;
            text-decoration: none;
            font-weight: 600;
            transition: color .2s;
        }
        .btn-cancel:hover { color: #333; }
        .success-box {
            background: #e8f5e9;
            border: 1px solid #a5d6a7;
            border-left: 4px solid #28a745;
            border-radius: 10px;
            padding: 12px 16px;
            font-weight: 600;
            color: #2e7d32;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .current-values {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: .84rem;
            color: #475569;
        }
        .current-values strong { color: #1a1a2e; }
    </style>
</head>
<body>

<div class="banner-yellow">
    <div class="container" style="display:flex; justify-content:space-between; align-items:center;">
        <h1 style="font-family:'Baloo 2'; color:#3e2723;">Modifier un enfant</h1>
        <nav>
            <a href="index.php">Accueil</a>
            <a href="parent-enfants.php">Mon espace</a>
            <a href="deconnexion.php" style="color:#c0392b;">se déconnecter</a>
        </nav>
    </div>
</div>

<div class="container" style="margin-top:35px; padding-bottom:60px;">
    <div class="add-child-container">

        <div class="form-part">

            <div class="modif-badge"> Modification du profil enfant</div>

            <h2 style="font-family:'Baloo 2'; margin-top:0; margin-bottom:6px; color:#3e2723;">
                <?= htmlspecialchars($enfant['prenom'] . ' ' . $enfant['nom']) ?>
            </h2>

            <!-- Résumé des valeurs actuelles -->
            <div class="current-values">
                Valeurs actuelles —
                <strong>Nom :</strong> <?= htmlspecialchars($enfant['nom']) ?> &nbsp;·&nbsp;
                <strong>Prénom :</strong> <?= htmlspecialchars($enfant['prenom']) ?> &nbsp;·&nbsp;
                <strong>Âge :</strong> <?= htmlspecialchars($enfant['age']) ?> ans
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-box">
                    <span style="font-size:1.2rem;"></span>
                    <?= $success ?>
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

                <button type="submit" class="btn-save"> Enregistrer les modifications</button>
            </form>

            <a href="parent-enfants.php" class="btn-cancel">← Retour à mon espace</a>
        </div>

        <!-- Partie image (identique à ajouter-enfant) -->
        <div class="image-part">
            <img src="images/create_acc.jpg" alt="Modifier enfant"
                 style="max-width:75%; max-height:290px;"
                 onerror="this.style.display='none'">
            <div class="yellow-strip">
                <span>Ateliers</span>
            </div>
        </div>

    </div>
</div>

</body>
</html>
