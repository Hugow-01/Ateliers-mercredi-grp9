<?php
require_once 'config.php';
requireAdmin();

$db = getDB();

// Stats globales
$nbActivites  = $db->query("SELECT COUNT(*) FROM Activité")->fetchColumn();
$nbEnfants    = $db->query("SELECT COUNT(*) FROM Enfant")->fetchColumn();
$nbFamilles   = $db->query("SELECT COUNT(*) FROM Famille")->fetchColumn();
$nbCreneaux   = $db->query("SELECT COUNT(*) FROM Creneau")->fetchColumn();

// Inscriptions récentes
$recents = $db->query("
    SELECT e.nom, e.prenom, a.nom as activite, c.date, c.debut
    FROM Enfant_Creneau ec
    JOIN Enfant e ON e.id = ec.id_enfant
    JOIN Creneau c ON c.id = ec.id_creneau
    JOIN Activité a ON a.nom = c.nom_activite
    ORDER BY c.date DESC
    LIMIT 10
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Admin</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 30px 0; }
        .stat-card { background: white; border-radius: 15px; padding: 25px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.07); border-top: 4px solid; }
        .stat-card h2 { font-size: 3rem; margin: 0; font-family: 'Baloo 2'; }
        .stat-card p { margin: 5px 0 0; color: #666; font-weight: bold; }
        .quick-links { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .quick-link { background: white; border-radius: 15px; padding: 20px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.07); text-decoration: none; color: #333; transition: transform 0.2s; font-weight: bold; font-size: 1.1rem; }
        .quick-link:hover { transform: translateY(-3px); }
        @media(max-width:800px) { .stats-grid, .quick-links { grid-template-columns: 1fr 1fr; } }
    </style>
</head>
<body>

<header style="background:#fdd835; padding:15px 30px; display:flex; justify-content:space-between; align-items:center;">
    <h1 style="font-family:'Baloo 2'; font-size:1.8rem; color:#3e2723; margin:0;">Espace administrateur</h1>
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

    <!-- Statistiques -->
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

    <!-- Liens rapides -->
    <h3 style="font-family:'Baloo 2'; margin-bottom:15px;">Accès rapides</h3>
    <div class="quick-links">
        <a href="admin-activites.php" class="quick-link" style="border-top:4px solid #ff5e78;">Gérer les activités</a>
        <a href="admin-liste-enfants.php" class="quick-link" style="border-top:4px solid #7a86f1;">Liste des enfants</a>
        <a href="admin-inscription-parents.php" class="quick-link" style="border-top:4px solid #fdd835;">Inscriptions parents</a>
        <a href="admin-responsables.php" class="quick-link" style="border-top:4px solid #00e5ff;">Gérer les responsables</a>
    </div>

    <!-- Dernières inscriptions -->
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
                        <td><?= htmlspecialchars(substr($r['debut'],0,5)) ?></td>
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
