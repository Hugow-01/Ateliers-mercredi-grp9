<?php
require_once 'config.php';
requireAdmin();

$db = getDB();
$message = '';
$messageType = '';

// Inscription par l'admin pour une famille
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'inscrire_admin') {
    $ids_enfants  = $_POST['ids_enfants'] ?? [];
    $id_creneau   = intval($_POST['id_creneau'] ?? 0);
    if (empty($ids_enfants) || !$id_creneau) {
        $message = "Sélectionnez au moins un enfant et un créneau.";
        $messageType = 'error';
    } else {
        $nb_ok = 0;
        foreach ($ids_enfants as $id_enfant) {
            $id_enfant = intval($id_enfant);
            $check = $db->prepare("SELECT * FROM Enfant_Creneau WHERE id_enfant=? AND id_creneau=?");
            $check->execute([$id_enfant, $id_creneau]);
            if (!$check->fetch()) {
                $db->prepare("INSERT INTO Enfant_Creneau (id_enfant, id_creneau) VALUES (?,?)")->execute([$id_enfant, $id_creneau]);
                $nb_ok++;
            }
        }
        $message = "$nb_ok enfant(s) inscrit(s) avec succès.";
        $messageType = 'success';
    }
}

// Recherche famille
$loginRecherche = trim($_GET['login_famille'] ?? '');
$enfantsTrouves = [];
if ($loginRecherche) {
    $s = $db->prepare("SELECT * FROM Enfant WHERE login_famille = ? ORDER BY nom");
    $s->execute([$loginRecherche]);
    $enfantsTrouves = $s->fetchAll();
}

// Récupérer toutes les familles pour la recherche
$familles = $db->query("SELECT login, nom FROM Famille ORDER BY nom")->fetchAll();

// Récupérer les créneaux avec activités
$creneaux = $db->query("
    SELECT c.id, c.date, c.debut, c.fin, c.nom_activite, a.capacite,
           COUNT(ec.id_enfant) as nb_inscrits
    FROM Creneau c
    JOIN Activité a ON a.nom = c.nom_activite
    LEFT JOIN Enfant_Creneau ec ON ec.id_creneau = c.id
    GROUP BY c.id
    ORDER BY c.date, c.nom_activite, c.debut
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription parents - Admin</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<header style="background:#fdd835; padding:15px 30px; display:flex; justify-content:space-between; align-items:center;">
    <h1 style="font-family:'Baloo 2'; font-size:1.8rem; color:#3e2723; margin:0;">Espace administrateur</h1>
    <nav>
        <a href="admin-dashboard.php">tableau de bord</a>
        <a href="admin-liste-enfants.php">liste des enfants</a>
        <a href="admin-activites.php">activités</a>
        <a href="admin-inscription-parents.php" style="text-decoration:underline;">inscriptions</a>
        <a href="deconnexion.php" style="color:#c0392b;">se déconnecter</a>
    </nav>
</header>

<div class="container" style="padding-top:30px; padding-bottom:60px;">
    <h2 style="font-family:'Baloo 2'; font-size:2rem; margin-bottom:20px;">Inscription par les parents</h2>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
    <?php endif; ?>

    <div class="card">
        <!-- Étape 1 : Recherche famille -->
        <h3 style="font-family:'Baloo 2'; margin-top:0;">1. Rechercher une famille</h3>
        <form method="GET" style="display:flex; gap:15px; align-items:flex-end; flex-wrap:wrap; margin-bottom:25px;">
            <div class="form-group" style="flex:2; min-width:250px; margin-bottom:0;">
                <label>Famille (email/login)</label>
                <select name="login_famille">
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($familles as $f): ?>
                    <option value="<?= htmlspecialchars($f['login']) ?>" <?= $loginRecherche===$f['login']?'selected':'' ?>>
                        <?= htmlspecialchars($f['nom']) ?> (<?= htmlspecialchars($f['login']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-small">🔍 Rechercher</button>
        </form>

        <?php if ($loginRecherche): ?>
        <!-- Étape 2 : Enfants trouvés -->
        <form method="POST">
            <input type="hidden" name="action" value="inscrire_admin">
            <h3 style="font-family:'Baloo 2';">2. Sélectionner les enfants</h3>
            <div class="table-wrapper" style="margin-bottom:25px;">
                <table>
                    <thead><tr>
                        <th><input type="checkbox" onclick="toggleAll(this)"> Sélectionner</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Âge</th>
                    </tr></thead>
                    <tbody>
                        <?php if (empty($enfantsTrouves)): ?>
                        <tr><td colspan="4" style="text-align:center; color:#aaa;">Aucun enfant pour cette famille.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($enfantsTrouves as $enf): ?>
                        <tr>
                            <td><input type="checkbox" name="ids_enfants[]" value="<?= $enf['id'] ?>" class="chk-enfant"></td>
                            <td><?= htmlspecialchars($enf['nom']) ?></td>
                            <td><?= htmlspecialchars($enf['prenom']) ?></td>
                            <td><?= htmlspecialchars($enf['age']) ?> ans</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Étape 3 : Choix du créneau -->
            <?php if (!empty($enfantsTrouves)): ?>
            <h3 style="font-family:'Baloo 2';">3. Choisir un créneau</h3>
            <div class="form-group" style="max-width:500px;">
                <select name="id_creneau" required>
                    <option value="">-- Sélectionner un créneau --</option>
                    <?php foreach ($creneaux as $cr): ?>
                    <option value="<?= $cr['id'] ?>">
                        <?= htmlspecialchars($cr['nom_activite']) ?> · <?= $cr['date'] ?> · <?= substr($cr['debut'],0,5) ?>–<?= substr($cr['fin'],0,5) ?> (<?= $cr['nb_inscrits'] ?>/<?= $cr['capacite'] ?> inscrits)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="padding:12px 40px; font-size:1.1rem;">Inscrire</button>
            <?php endif; ?>
        </form>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleAll(el) {
    document.querySelectorAll('.chk-enfant').forEach(c => c.checked = el.checked);
}
</script>
</body>
</html>
