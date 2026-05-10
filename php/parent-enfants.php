<?php
require_once __DIR__ . '/config.php';
requireParent();

$deleted = ($_GET['deleted'] ?? '') === '1';
$db    = getDB();
$login = $_SESSION['user'];

// Récupérer l'id_famille
$idFamille = getIdFamille($db, $login);

// ── Récupérer les notifications non lues pour cette famille ──
$stmtNotif = $db->prepare("
    SELECT n.id, n.type, n.message, n.date_creation,
           e.prenom, e.nom AS nom_enfant
    FROM Notification n
    JOIN Enfant e ON e.id = n.id_enfant
    WHERE n.id_famille = ? AND n.lu = 0
    ORDER BY n.date_creation DESC
");
$stmtNotif->execute([$idFamille]);
$notifications = $stmtNotif->fetchAll();

// ── Marquer les notifications comme lues dès affichage ───────
if (!empty($notifications)) {
    $ids = implode(',', array_map(fn($n) => intval($n['id']), $notifications));
    $db->exec("UPDATE Notification SET lu = 1 WHERE id IN ($ids)");
}

// ── Récupérer les enfants avec leurs activités et créneaux ───
$stmt = $db->prepare("
    SELECT e.*,
           GROUP_CONCAT(
               CONCAT(a.nom, '|', c.date, '|', c.debut, '|', ec.id_creneau, '|', IFNULL(c.id_salle, ''))
               ORDER BY c.date SEPARATOR ';;'
           ) AS activites_raw
    FROM Enfant e
    LEFT JOIN Enfant_Creneau ec ON ec.id_enfant = e.id
    LEFT JOIN Creneau c         ON c.id = ec.id_creneau
    LEFT JOIN Activite a        ON a.nom = c.nom_activite
    WHERE e.id_famille = ?
    GROUP BY e.id
    ORDER BY e.id
");
$stmt->execute([$idFamille]);
$enfants = $stmt->fetchAll();

// Statut : vérifie dans Enfant_Creneau (accepté) ou ListeAttente
function getStatut(PDO $db, int $id_creneau, int $id_enfant, string $nom_activite): string {
    // Vérifier si présent dans les confirmés
    $chk = $db->prepare("SELECT 1 FROM Enfant_Creneau WHERE id_creneau = ? AND id_enfant = ?");
    $chk->execute([$id_creneau, $id_enfant]);
    if ($chk->fetch()) return 'accepte';
    return "liste d'attente";
}

$moisFR = [
    '01' => 'jan', '02' => 'fév', '03' => 'mar', '04' => 'avr',
    '05' => 'mai', '06' => 'jun', '07' => 'jul', '08' => 'aoû',
    '09' => 'sep', '10' => 'oct', '11' => 'nov', '12' => 'déc',
];