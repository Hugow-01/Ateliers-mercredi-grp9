<?php
require_once __DIR__ . '/config.php';
requireAdmin();

$db          = getDB();
$message     = '';
$messageType = '';

// ── ACTIONS POST ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'ajouter_activite') {
        $nom = trim($_POST['nom'] ?? '');
        $cap = intval($_POST['capacite'] ?? 0);
        $syl = trim($_POST['syllabus'] ?? '');
        if ($nom && $cap > 0) {
            $db->prepare("INSERT INTO Activité (nom, capacite, syllabus) VALUES (?, ?, ?)")
               ->execute([$nom, $cap, $syl]);
            $message = "Activité créée.";
            $messageType = 'success';
        }
    }

    if ($action === 'ajouter_creneau') {
        $db->prepare("INSERT INTO Creneau (date, debut, fin, nom_activite) VALUES (?, ?, ?, ?)")
           ->execute([$_POST['date'], $_POST['debut'], $_POST['fin'], $_POST['nom_activite']]);
        $message = "Créneau ajouté.";
        $messageType = 'success';
    }

    if ($action === 'suppr_creneau') {
        $db->prepare("DELETE FROM Creneau WHERE id = ?")->execute([$_POST['id_creneau']]);
        $message = "Créneau supprimé.";
        $messageType = 'success';
    }
}

$activites = $db->query("SELECT * FROM Activité ORDER BY nom")->fetchAll();

$creneaux  = $db->query("
    SELECT c.*, COUNT(ec.id_enfant) AS nb
    FROM Creneau c
    LEFT JOIN Enfant_Creneau ec ON ec.id_creneau = c.id
    GROUP BY c.id
    ORDER BY c.date
")->fetchAll();

$crByAct = [];
foreach ($creneaux as $cr) {
    $crByAct[$cr['nom_activite']][] = $cr;
}
