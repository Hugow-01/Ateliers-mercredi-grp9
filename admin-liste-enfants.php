<?php require_once 'php/admin-liste-enfants.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des enfants - Admin</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<header class="admin-header">
    <h1>Espace administrateur</h1>
    <nav>
        <a href="admin-dashboard.php">tableau de bord</a>
        <a href="admin-liste-enfants.php" style="text-decoration:underline;">liste des enfants</a>
        <a href="admin-activites.php">activités</a>
        <a href="admin-inscription-parents.php">inscriptions</a>
        <a href="deconnexion.php" style="color:#c0392b;">se déconnecter</a>
    </nav>
</header>

<div class="container" style="padding-top:30px; padding-bottom:60px;">
    <h2 style="font-family:'Baloo 2'; font-size:2rem; margin-bottom:20px;">Liste des enfants par activité</h2>

    <form method="GET" style="display:flex; gap:15px; align-items:flex-end; margin-bottom:25px; flex-wrap:wrap;">
        <div class="form-group" style="flex:1; min-width:200px; margin-bottom:0;">
            <label>Activité</label>
            <select name="activite">
                <option value="">Toutes les activités</option>
                <?php foreach ($activites as $a): ?>
                <option value="<?= htmlspecialchars($a['nom']) ?>" <?= $filtreActivite === $a['nom'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['nom']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="flex:1; min-width:160px; margin-bottom:0;">
            <label>Date</label>
            <input type="date" name="date" value="<?= htmlspecialchars($filtreDate) ?>">
        </div>
        <button type="submit" class="btn btn-primary btn-small">🔍 Filtrer</button>
        <a href="admin-liste-enfants.php" class="btn btn-small" style="background:#eee; color:#333;">Réinitialiser</a>
    </form>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nom</th><th>Prénom</th><th>Âge</th><th>Famille</th>
                        <th>Activité</th><th>Date</th><th>Horaire</th><th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($inscriptions)): ?>
                    <tr><td colspan="8" style="text-align:center; color:#aaa; padding:30px;">Aucun résultat</td></tr>
                    <?php endif; ?>
                    <?php foreach ($inscriptions as $ins):
                        $key = $ins['id_creneau'];
                        if (!isset($enfantRang[$key])) $enfantRang[$key] = 0;
                        $enfantRang[$key]++;
                        $statut = ($enfantRang[$key] <= $ins['capacite']) ? 'accepté' : "liste d'attente";
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($ins['nom']) ?></td>
                        <td><?= htmlspecialchars($ins['prenom']) ?></td>
                        <td><?= htmlspecialchars($ins['age']) ?> ans</td>
                        <td><?= htmlspecialchars($ins['login_famille']) ?></td>
                        <td><?= htmlspecialchars($ins['activite']) ?></td>
                        <td><?= htmlspecialchars($ins['date']) ?></td>
                        <td><?= substr($ins['debut'], 0, 5) ?> – <?= substr($ins['fin'], 0, 5) ?></td>
                        <td>
                            <?php if ($statut === 'accepté'): ?>
                                <span style="background:#d4edda; color:#155724; padding:4px 12px; border-radius:8px; font-weight:bold; font-size:.85rem;">accepté</span>
                            <?php else: ?>
                                <span style="background:#ffe0e0; color:#c0392b; padding:4px 12px; border-radius:8px; font-weight:bold; font-size:.85rem;">liste d'attente</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
