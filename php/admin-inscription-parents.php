<?php
require_once __DIR__ . '/config.php';
requireAdmin();

$db          = getDB();
$message     = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'inscrire_admin') {
    $ids_enfants = $_POST['ids_enfants'] ?? [];
    $id_creneau  = intval($_POST['id_creneau'] ?? 0);

    if (empty($ids_enfants) || !$id_creneau) {
        $message = "Sélectionnez au moins un enfant et un créneau.";
        $messageType = 'error';
    } else {
        $nb_ok = 0;
        foreach ($ids_enfants as $id_enfant) {
            $id_enfant = intval($id_enfant);
            $check = $db->prepare("SELECT * FROM Enfant_Creneau WHERE id_enfant = ? AND id_creneau = ?");
            $check->execute([$id_enfant, $id_creneau]);
            if (!$check->fetch()) {
                $db->prepare("INSERT INTO Enfant_Creneau (id_enfant, id_creneau) VALUES (?, ?)")
                   ->execute([$id_enfant, $id_creneau]);
                $nb_ok++;
            }
        }
        $message = "$nb_ok enfant(s) inscrit(s) avec succès.";
        $messageType = 'success';
    }
}

$loginRecherche = trim($_GET['login_famille'] ?? '');
$enfantsTrouves = [];
if ($loginRecherche) {
    $s = $db->prepare("SELECT * FROM Enfant WHERE login_famille = ? ORDER BY nom");
    $s->execute([$loginRecherche]);
    $enfantsTrouves = $s->fetchAll();
}

$familles = $db->query("SELECT login, nom FROM Famille ORDER BY nom")->fetchAll();

$creneaux = $db->query("
    SELECT c.id, c.date, c.debut, c.fin, c.nom_activite, a.capacite,
           COUNT(ec.id_enfant) AS nb_inscrits
    FROM Creneau c
    JOIN Activité a ON a.nom = c.nom_activite
    LEFT JOIN Enfant_Creneau ec ON ec.id_creneau = c.id
    GROUP BY c.id
    ORDER BY c.date, c.nom_activite, c.debut
")->fetchAll();
