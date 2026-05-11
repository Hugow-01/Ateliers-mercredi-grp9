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

// ── Récupérer les enfants avec inscriptions confirmées ───────
$stmtInscr = $db->prepare("
    SELECT e.*,
           GROUP_CONCAT(
               CONCAT(a.nom, '|', c.date, '|', c.debut, '|', c.id, '|', IFNULL(c.id_salle, ''), '|', 'inscrit', '|', '-1', '|', '-1')
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
$stmtInscr->execute([$idFamille]);
$enfantsInscr = $stmtInscr->fetchAll();

// ── Récupérer séparément les listes d'attente par enfant ─────
$stmtAttente = $db->prepare("
    SELECT e.id AS id_enfant,
           a.nom AS act_nom, c.date, c.debut, c.id AS id_creneau,
           IFNULL(c.id_salle, '') AS id_salle,
           la.position,
           (SELECT COUNT(*) FROM ListeAttente la2 WHERE la2.id_creneau = c.id) AS total_attente
    FROM ListeAttente la
    JOIN Enfant e   ON e.id  = la.id_enfant
    JOIN Creneau c  ON c.id  = la.id_creneau
    JOIN Activite a ON a.nom = c.nom_activite
    WHERE e.id_famille = ?
    ORDER BY e.id, c.date
");
$stmtAttente->execute([$idFamille]);
$attentesRows = $stmtAttente->fetchAll();

// Indexer les attentes par id_enfant
$attentesByEnfant = [];
foreach ($attentesRows as $row) {
    $attentesByEnfant[$row['id_enfant']][] = $row;
}

// Fusionner les données
$enfants = [];
foreach ($enfantsInscr as $enf) {
    $enf['attentes'] = $attentesByEnfant[$enf['id']] ?? [];
    $enfants[] = $enf;
}

$moisFR = [
    '01' => 'jan', '02' => 'fév', '03' => 'mar', '04' => 'avr',
    '05' => 'mai', '06' => 'jun', '07' => 'jul', '08' => 'aoû',
    '09' => 'sep', '10' => 'oct', '11' => 'nov', '12' => 'déc',
];