<?php
require_once __DIR__ . '/config.php';
requireParent();

$db  = getDB();
$login = $_SESSION['user'];
$idFamille = getIdFamille($db, $login);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../parent-enfants.php");
    exit;
}

$id = intval($_POST['id'] ?? 0);
if (!$id) {
    header("Location: ../parent-enfants.php");
    exit;
}

// Vérifier que l'enfant appartient bien à cette famille
$stmt = $db->prepare("SELECT id FROM Enfant WHERE id = ? AND id_famille = ?");
$stmt->execute([$id, $idFamille]);
if (!$stmt->fetch()) {
    header("Location: ../parent-enfants.php?error=unauthorized");
    exit;
}

// Récupérer les créneaux confirmés de cet enfant
$creneaux = $db->prepare("SELECT id_creneau FROM Enfant_Creneau WHERE id_enfant = ?");
$creneaux->execute([$id]);
$listeCreneaux = $creneaux->fetchAll();

// Pour chaque créneau, promouvoir le premier en liste d'attente
foreach ($listeCreneaux as $cr) {
    $idCreneau = (int) $cr['id_creneau'];

    $db->prepare("DELETE FROM Enfant_Creneau WHERE id_enfant = ? AND id_creneau = ?")
       ->execute([$id, $idCreneau]);

    $premier = $db->prepare(
        "SELECT id_enfant FROM ListeAttente WHERE id_creneau = ? ORDER BY position ASC LIMIT 1"
    );
    $premier->execute([$idCreneau]);
    $promo = $premier->fetchColumn();

    if ($promo) {
        $db->prepare("INSERT INTO Enfant_Creneau (id_enfant, id_creneau) VALUES (?, ?)")
        ->execute([$promo, $idCreneau]);
        $db->prepare("DELETE FROM ListeAttente WHERE id_enfant = ? AND id_creneau = ?")
        ->execute([$promo, $idCreneau]);

        $restants = $db->prepare(
            "SELECT id FROM ListeAttente WHERE id_creneau = ? ORDER BY position ASC"
        );
        $restants->execute([$idCreneau]);
        $pos = 1;
        foreach ($restants->fetchAll() as $r) {
            $db->prepare("UPDATE ListeAttente SET position = ? WHERE id = ?")
               ->execute([$pos++, $r['id']]);
        }
    }
}

$db->prepare("DELETE FROM ListeAttente WHERE id_enfant = ?")->execute([$id]);
$db->prepare("DELETE FROM Enfant WHERE id = ? AND id_famille = ?")
   ->execute([$id, $idFamille]);

header("Location: ../parent-enfants.php?deleted=1");
exit;