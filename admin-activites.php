<?php
require_once 'config.php';
requireAdmin();

$db = getDB();
$message = '';
$messageType = '';

// Ajouter une activité
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'ajouter_activite') {
    $nom      = trim($_POST['nom'] ?? '');
    $cap      = intval($_POST['capacite'] ?? 0);
    $syllabus = trim($_POST['syllabus'] ?? '');
    if ($nom && $cap > 0 && $syllabus) {
        try {
            $stmt = $db->prepare("INSERT INTO Activité (nom, capacite, syllabus) VALUES (?, ?, ?)");
            $stmt->execute([$nom, $cap, $syllabus]);
            $message = "Activité ajoutée avec succès.";
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = "Erreur : " . $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
        $messageType = 'error';
    }
}

// Modifier une activité
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'modifier_activite') {
    $nomOld   = $_POST['nom_old'] ?? '';
    $cap      = intval($_POST['capacite'] ?? 0);
    $syllabus = trim($_POST['syllabus'] ?? '');
    if ($cap > 0 && $syllabus) {
        $db->prepare("UPDATE Activité SET capacite=?, syllabus=? WHERE nom=?")->execute([$cap, $syllabus, $nomOld]);
        $message = "Activité modifiée.";
        $messageType = 'success';
    }
}

// Ajouter un créneau
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'ajouter_creneau') {
    $nom_activite = $_POST['nom_activite'] ?? '';
    $date  = $_POST['date_creneau'] ?? '';
    $debut = $_POST['debut'] ?? '';
    $fin   = $_POST['fin'] ?? '';
    $salle = $_POST['id_salle'] ?? null;
    if ($nom_activite && $date && $debut && $fin) {
        $db->prepare("INSERT INTO Creneau (date, debut, fin, id_salle, nom_activite) VALUES (?,?,?,?,?)")->execute([$date, $debut, $fin, $salle ?: null, $nom_activite]);
        $message = "Créneau ajouté.";
        $messageType = 'success';
    }
}

// Supprimer un créneau
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'suppr_creneau') {
    $id = intval($_POST['id_creneau'] ?? 0);
    if ($id) {
        $db->prepare("DELETE FROM Enfant_Creneau WHERE id_creneau=?")->execute([$id]);
        $db->prepare("DELETE FROM Creneau WHERE id=?")->execute([$id]);
        $message = "Créneau supprimé.";
        $messageType = 'success';
    }
}

// Récupérer données
$activites = $db->query("SELECT * FROM Activité ORDER BY nom")->fetchAll();
$salles    = $db->query("SELECT * FROM Salle")->fetchAll();
$creneaux  = $db->query("
    SELECT c.*, COUNT(ec.id_enfant) as nb_inscrits
    FROM Creneau c
    LEFT JOIN Enfant_Creneau ec ON ec.id_creneau=c.id
    GROUP BY c.id
    ORDER BY c.nom_activite, c.date, c.debut
")->fetchAll();
$crByAct = [];
foreach ($creneaux as $cr) { $crByAct[$cr['nom_activite']][] = $cr; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Activités - Admin</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .modal-bg { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center; }
        .modal-bg.active { display:flex; }
        .modal { background:white; border-radius:20px; padding:35px; width:90%; max-width:500px; position:relative; }
        .modal h3 { font-family:'Baloo 2'; margin-top:0; }
        .close-modal { position:absolute; top:15px; right:20px; cursor:pointer; font-size:1.5rem; color:#aaa; }
        .close-modal:hover { color:#333; }
        .btn-sm { padding:6px 14px; border-radius:8px; border:none; cursor:pointer; font-weight:bold; font-size:0.85rem; }
        .creneau-row { display:flex; justify-content:space-between; align-items:center; padding:8px 12px; border:1px solid #eee; border-radius:8px; margin-bottom:6px; }
    </style>
</head>
<body>

<header style="background:#fdd835; padding:15px 30px; display:flex; justify-content:space-between; align-items:center;">
    <h1 style="font-family:'Baloo 2'; font-size:1.8rem; color:#3e2723; margin:0;">Espace administrateur</h1>
    <nav>
        <a href="admin-dashboard.php">tableau de bord</a>
        <a href="admin-liste-enfants.php">liste des enfants</a>
        <a href="admin-activites.php" style="text-decoration:underline;">activités</a>
        <a href="deconnexion.php" style="color:#c0392b;">se déconnecter</a>
    </nav>
</header>

<div class="container" style="padding-top:30px; padding-bottom:60px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-family:'Baloo 2'; font-size:2rem;">Gestion des activités</h2>
        <button onclick="document.getElementById('modal-add-act').classList.add('active')" class="btn btn-primary">+ Nouvelle activité</button>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
    <?php endif; ?>

    <?php foreach ($activites as $act): ?>
    <details style="margin-bottom:15px; border:1px solid #eee; border-radius:15px; overflow:hidden;">
        <summary style="padding:15px 20px; background:#fafafa; font-size:1.2rem; font-weight:800; cursor:pointer; display:flex; justify-content:space-between; align-items:center;">
            <span><?= htmlspecialchars($act['nom']) ?> <small style="font-weight:normal; color:#888;">(capacité: <?= $act['capacite'] ?>)</small></span>
            <span style="font-size:0.85rem; font-weight:normal;">▼</span>
        </summary>
        <div style="padding:20px;">
            <p style="color:#555; margin-bottom:15px;"><?= nl2br(htmlspecialchars($act['syllabus'])) ?></p>

            <!-- Créneaux existants -->
            <h4>Créneaux :</h4>
            <?php $crs = $crByAct[$act['nom']] ?? []; ?>
            <?php if (empty($crs)): ?>
                <p style="color:#aaa;">Aucun créneau défini.</p>
            <?php else: ?>
                <?php foreach ($crs as $cr): ?>
                <div class="creneau-row">
                    <span><?= htmlspecialchars($cr['date']) ?> · <?= substr($cr['debut'],0,5) ?> – <?= substr($cr['fin'],0,5) ?> · <?= $cr['nb_inscrits'] ?>/<?= $act['capacite'] ?> inscrits</span>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ce créneau ?')">
                        <input type="hidden" name="action" value="suppr_creneau">
                        <input type="hidden" name="id_creneau" value="<?= $cr['id'] ?>">
                        <button class="btn-sm" style="background:#ff7f50; color:white;">✕ Supprimer</button>
                    </form>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Ajouter créneau -->
            <details style="margin-top:15px;">
                <summary style="cursor:pointer; color:#1a5fb4; font-weight:bold;">+ Ajouter un créneau</summary>
                <form method="POST" style="display:flex; gap:10px; flex-wrap:wrap; margin-top:12px; align-items:flex-end;">
                    <input type="hidden" name="action" value="ajouter_creneau">
                    <input type="hidden" name="nom_activite" value="<?= htmlspecialchars($act['nom']) ?>">
                    <div class="form-group" style="flex:1; min-width:140px; margin-bottom:0;">
                        <label>Date</label>
                        <input type="date" name="date_creneau" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:110px; margin-bottom:0;">
                        <label>Début</label>
                        <input type="time" name="debut" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:110px; margin-bottom:0;">
                        <label>Fin</label>
                        <input type="time" name="fin" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:140px; margin-bottom:0;">
                        <label>Salle</label>
                        <select name="id_salle">
                            <option value="">-- Aucune --</option>
                            <?php foreach ($salles as $s): ?>
                            <option value="<?= htmlspecialchars($s['id']) ?>"><?= htmlspecialchars($s['id']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-small">Ajouter</button>
                </form>
            </details>

            <!-- Modifier infos activité -->
            <details style="margin-top:10px;">
                <summary style="cursor:pointer; color:#7a86f1; font-weight:bold;">Modifier les informations</summary>
                <form method="POST" style="margin-top:12px;">
                    <input type="hidden" name="action" value="modifier_activite">
                    <input type="hidden" name="nom_old" value="<?= htmlspecialchars($act['nom']) ?>">
                    <div class="form-group">
                        <label>Capacité</label>
                        <input type="number" name="capacite" value="<?= $act['capacite'] ?>" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Description / Syllabus</label>
                        <textarea name="syllabus" rows="3" required><?= htmlspecialchars($act['syllabus']) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-small">Sauvegarder</button>
                </form>
            </details>
        </div>
    </details>
    <?php endforeach; ?>
</div>

<!-- Modal ajouter activité -->
<div class="modal-bg" id="modal-add-act">
    <div class="modal">
        <span class="close-modal" onclick="document.getElementById('modal-add-act').classList.remove('active')">✕</span>
        <h3>Nouvelle activité</h3>
        <form method="POST">
            <input type="hidden" name="action" value="ajouter_activite">
            <div class="form-group">
                <label>Nom de l'activité</label>
                <input type="text" name="nom" placeholder="ex: Atelier Arts plastiques" required>
            </div>
            <div class="form-group">
                <label>Capacité (nb d'enfants max)</label>
                <input type="number" name="capacite" min="1" placeholder="ex: 20" required>
            </div>
            <div class="form-group">
                <label>Description / Syllabus</label>
                <textarea name="syllabus" rows="4" placeholder="Décrivez l'activité..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; font-size:1.1rem;">Créer l'activité</button>
        </form>
    </div>
</div>

</body>
</html>
