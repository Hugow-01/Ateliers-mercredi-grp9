<?php
require_once __DIR__ . '/config.php';
requireParent();

$db    = getDB();
$login = $_SESSION['user'];

// Récupérer les enfants avec leurs activités et créneaux
$stmt = $db->prepare("
    SELECT e.*,
           GROUP_CONCAT(
               CONCAT(a.nom, '|', c.date, '|', c.debut, '|', ec.id_creneau, '|', IFNULL(c.id_salle, ''))
               ORDER BY c.date SEPARATOR ';;'
           ) AS activites_raw
    FROM Enfant e
    LEFT JOIN Enfant_Creneau ec ON ec.id_enfant = e.id
    LEFT JOIN Creneau c         ON c.id = ec.id_creneau
    LEFT JOIN Activité a        ON a.nom = c.nom_activite
    WHERE e.login_famille = ?
    GROUP BY e.id
    ORDER BY e.id
");
$stmt->execute([$login]);
$enfants = $stmt->fetchAll();

// Statut : rang de l'enfant dans le créneau vs capacité
function getStatut(PDO $db, int $id_creneau, int $id_enfant, string $nom_activite): string {
    $rang = $db->prepare(
        "SELECT COUNT(*) FROM Enfant_Creneau WHERE id_creneau = ? AND id_enfant <= ?"
    );
    $rang->execute([$id_creneau, $id_enfant]);
    $r = (int) $rang->fetchColumn();

    $cap = $db->prepare("SELECT capacite FROM Activité WHERE nom = ?");
    $cap->execute([$nom_activite]);
    $c = (int) ($cap->fetchColumn() ?? 99);

    return $r <= $c ? 'accepté' : 'liste d\'attente';
}

$moisFR = [
    '01' => 'jan', '02' => 'fév', '03' => 'mar', '04' => 'avr',
    '05' => 'mai', '06' => 'jun', '07' => 'jul', '08' => 'aoû',
    '09' => 'sep', '10' => 'oct', '11' => 'nov', '12' => 'déc',
];
