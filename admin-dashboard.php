<?php require_once 'php/admin-dashboard.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Admin</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<header class="admin-header">
    <h1>Espace administrateur</h1>
    <nav>
        <a href="index.php">Accueil</a>
        <a href="admin-dashboard.php" style="text-decoration:underline;">tableau de bord</a>
        <a href="admin-liste-enfants.php">liste des enfants</a>
        <a href="admin-activites.php">gestion activités</a>
        <a href="admin-responsables.php">responsables</a>
        <a href="deconnexion.php" style="color:#c0392b;">se déconnecter</a>
    </nav>
</header>

<div class="container" style="padding-top:30px; padding-bottom:60px;">
    <h2 style="font-family:'Baloo 2'; font-size:2rem; margin-bottom:5px;">Tableau de bord</h2>
    <p style="color:#888;">Bienvenue, <?= htmlspecialchars($_SESSION['nom']) ?></p>

    <div class="stats-grid">
        <div class="stat-card" style="border-color:#ff5e78;">
            <h2 style="color:#ff5e78;"><?= $nbActivites ?></h2>
            <p>Activités</p>
        </div>
        <div class="stat-card" style="border-color:#7a86f1;">
            <h2 style="color:#7a86f1;"><?= $nbEnfants ?></h2>
            <p>Enfants inscrits</p>
        </div>
        <div class="stat-card" style="border-color:#fdd835;">
            <h2 style="color:#b8a000;"><?= $nbFamilles ?></h2>
            <p>Familles</p>
        </div>
        <div class="stat-card" style="border-color:#00e5ff;">
            <h2 style="color:#00bcd4;"><?= $nbCreneaux ?></h2>
            <p>Créneaux</p>
        </div>
    </div>

    <h3 style="font-family:'Baloo 2'; margin-bottom:15px;">Accès rapides</h3>
    <div class="quick-links">
        <a href="admin-activites.php"          class="quick-link" style="border-top:4px solid #ff5e78;">Gérer les activités</a>
        <a href="admin-liste-enfants.php"       class="quick-link" style="border-top:4px solid #7a86f1;">Liste des enfants</a>
        <a href="admin-inscription-parents.php" class="quick-link" style="border-top:4px solid #fdd835;">Inscriptions parents</a>
        <a href="admin-responsables.php"        class="quick-link" style="border-top:4px solid #00e5ff;">Gérer les responsables</a>
    </div>

    <h3 style="font-family:'Baloo 2'; margin-bottom:15px;">Dernières inscriptions</h3>
    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Enfant</th>
                        <th>Activité</th>
                        <th>Date</th>
                        <th>Heure</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recents as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?></td>
                        <td><?= htmlspecialchars($r['activite']) ?></td>
                        <td><?= htmlspecialchars($r['date']) ?></td>
                        <td><?= htmlspecialchars(substr($r['debut'], 0, 5)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recents)): ?>
                    <tr><td colspan="4" style="text-align:center; color:#aaa;">Aucune inscription pour l'instant</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
