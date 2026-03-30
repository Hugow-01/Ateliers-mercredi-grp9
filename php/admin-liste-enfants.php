<?php
require_once __DIR__ . '/config.php';
requireAdmin();

$db          = getDB();
$message     = '';
$messageType = '';

// ══════════════════════════════════════════════════════════════
//  HELPER : envoyer une notification en base + email
// ══════════════════════════════════════════════════════════════
function notifierFamille(
    PDO    $db,
    string $loginFamille,
    int    $idEnfant,
    int    $idCreneau,
    string $type,          // 'accepte' | 'attente'
    string $msgTexte
): void {
    // 1. Notification en base
    $db->prepare("
        INSERT INTO Notification (login_famille, id_enfant, id_creneau, type, message)
        VALUES (?, ?, ?, ?, ?)
    ")->execute([$loginFamille, $idEnfant, $idCreneau, $type, $msgTexte]);

    // 2. Email réel via mail() natif PHP
    $sujet  = ($type === 'accepte')
        ? ' Inscription confirmée – Ateliers du Mercredi'
        : ' Mise en liste d\'attente – Ateliers du Mercredi';

    $corps  = "Bonjour,\n\n"
        . $msgTexte . "\n\n"
        . "Connectez-vous à votre espace parent pour plus de détails :\n"
        . "http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/parent-enfants.php\n\n"
        . "Cordialement,\nLes Ateliers du Mercredi";

    $headers = "From: noreply@ateliers-mercredi.com\r\n"
             . "Content-Type: text/plain; charset=UTF-8\r\n";

    @mail($loginFamille, $sujet, $corps, $headers);
}

// ══════════════════════════════════════════════════════════════
//  ACTION : basculer le statut d'un enfant sur un créneau
// ══════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'basculer_statut') {

    $idEnfant  = intval($_POST['id_enfant']  ?? 0);
    $idCreneau = intval($_POST['id_creneau'] ?? 0);
    $direction = $_POST['direction'] ?? ''; // 'vers_attente' | 'vers_accepte'

    // Récupérer infos enfant + famille + créneau
    $stmtEnf = $db->prepare("
        SELECT e.nom, e.prenom, e.login_famille,
               f.nom AS nom_famille
        FROM Enfant e
        JOIN Famille f ON f.login = e.login_famille
        WHERE e.id = ?
    ");
    $stmtEnf->execute([$idEnfant]);
    $enf = $stmtEnf->fetch();

    $stmtCr = $db->prepare("
        SELECT c.date, c.debut, c.fin, c.nom_activite, a.capacite
        FROM Creneau c
        JOIN Activité a ON a.nom = c.nom_activite
        WHERE c.id = ?
    ");
    $stmtCr->execute([$idCreneau]);
    $cr = $stmtCr->fetch();

    if ($enf && $cr) {
        $prenomNom  = htmlspecialchars($enf['prenom'] . ' ' . $enf['nom']);
        $dateF      = date('d/m/Y', strtotime($cr['date']));
        $heureDebut = substr($cr['debut'], 0, 5);
        $heureFin   = substr($cr['fin'],   0, 5);
        $activite   = $cr['nom_activite'];

        // ── Passer de "accepté" → "liste d'attente" ────────────
        if ($direction === 'vers_attente') {
            // Vérifier qu'il est bien inscrit
            $chk = $db->prepare("SELECT 1 FROM Enfant_Creneau WHERE id_enfant=? AND id_creneau=?");
            $chk->execute([$idEnfant, $idCreneau]);
            if ($chk->fetch()) {
                // Retirer de Enfant_Creneau
                $db->prepare("DELETE FROM Enfant_Creneau WHERE id_enfant=? AND id_creneau=?")
                   ->execute([$idEnfant, $idCreneau]);

                // Ajouter en ListeAttente (fin de liste)
                $pos = (function() use ($db, $idCreneau) {
                    $s = $db->prepare("SELECT COALESCE(MAX(position),0)+1 FROM ListeAttente WHERE id_creneau=?");
                    $s->execute([$idCreneau]);
                    return (int)$s->fetchColumn();
                })();
                $db->prepare("INSERT INTO ListeAttente (id_enfant,id_creneau,position) VALUES (?,?,?)")
                   ->execute([$idEnfant, $idCreneau, $pos]);

                // Notification
                $msg = "L'administrateur a modifié le statut de $prenomNom pour l'activité "
                     . "\"$activite\" du $dateF ($heureDebut–$heureFin) : "
                     . "votre enfant est maintenant en liste d'attente (position #$pos).";
                notifierFamille($db, $enf['login_famille'], $idEnfant, $idCreneau, 'attente', $msg);

                $message     = " $prenomNom déplacé en liste d'attente (#$pos). La famille a été notifiée.";
                $messageType = 'success';
            } else {
                $message = "Cet enfant n'est pas inscrit (confirmé) à ce créneau.";
                $messageType = 'error';
            }

        // ── Passer de "liste d'attente" → "accepté" ────────────
        } elseif ($direction === 'vers_accepte') {
            // Vérifier qu'il est bien en liste d'attente
            $chk = $db->prepare("SELECT 1 FROM ListeAttente WHERE id_enfant=? AND id_creneau=?");
            $chk->execute([$idEnfant, $idCreneau]);
            if ($chk->fetch()) {
                // Insérer dans Enfant_Creneau
                $db->prepare("INSERT INTO Enfant_Creneau (id_enfant,id_creneau) VALUES (?,?)")
                   ->execute([$idEnfant, $idCreneau]);

                // Retirer de ListeAttente et renuméroter
                $db->prepare("DELETE FROM ListeAttente WHERE id_enfant=? AND id_creneau=?")
                   ->execute([$idEnfant, $idCreneau]);
                $restants = $db->prepare("SELECT id FROM ListeAttente WHERE id_creneau=? ORDER BY position ASC");
                $restants->execute([$idCreneau]);
                $pos = 1;
                foreach ($restants->fetchAll() as $r) {
                    $db->prepare("UPDATE ListeAttente SET position=? WHERE id=?")->execute([$pos++, $r['id']]);
                }

                // Notification
                $msg = "Bonne nouvelle ! L'administrateur a confirmé la place de $prenomNom "
                     . "pour l'activité \"$activite\" du $dateF ($heureDebut–$heureFin). "
                     . "Votre enfant est maintenant inscrit(e) et sa place est confirmée.";
                notifierFamille($db, $enf['login_famille'], $idEnfant, $idCreneau, 'accepte', $msg);

                $message     = " $prenomNom déplacé en inscrit confirmé. La famille a été notifiée.";
                $messageType = 'success';
            } else {
                $message = "Cet enfant n'est pas en liste d'attente pour ce créneau.";
                $messageType = 'error';
            }
        }
    }
}

// ══════════════════════════════════════════════════════════════
//  CHARGEMENT DES DONNÉES (inscriptions + attentes)
// ══════════════════════════════════════════════════════════════
$filtreActivite = $_GET['activite'] ?? '';
$filtreDate     = $_GET['date']     ?? '';
$filtreStatut   = $_GET['statut']   ?? ''; // '' | 'accepte' | 'attente'

// ── Inscrits confirmés ──────────────────────────────────────
$whereConf  = "WHERE 1=1";
$paramsConf = [];
if ($filtreActivite) { $whereConf .= " AND a.nom = ?";    $paramsConf[] = $filtreActivite; }
if ($filtreDate)     { $whereConf .= " AND c.date = ?";   $paramsConf[] = $filtreDate; }

$stmtConf = $db->prepare("
    SELECT e.id AS id_enfant, e.nom, e.prenom, e.age, e.login_famille,
           a.nom AS activite, a.capacite,
           c.date, c.debut, c.fin, c.id AS id_creneau,
           'accepte' AS statut_type
    FROM Enfant_Creneau ec
    JOIN Enfant   e ON e.id  = ec.id_enfant
    JOIN Creneau  c ON c.id  = ec.id_creneau
    JOIN Activité a ON a.nom = c.nom_activite
    $whereConf
    ORDER BY a.nom, c.date, c.debut, e.nom
");
$stmtConf->execute($paramsConf);
$inscritsConf = $stmtConf->fetchAll();

// ── Liste d'attente ─────────────────────────────────────────
$whereAtt  = "WHERE 1=1";
$paramsAtt = [];
if ($filtreActivite) { $whereAtt .= " AND a.nom = ?";    $paramsAtt[] = $filtreActivite; }
if ($filtreDate)     { $whereAtt .= " AND c.date = ?";   $paramsAtt[] = $filtreDate; }

$stmtAtt = $db->prepare("
    SELECT e.id AS id_enfant, e.nom, e.prenom, e.age, e.login_famille,
           a.nom AS activite, a.capacite,
           c.date, c.debut, c.fin, c.id AS id_creneau,
           la.position,
           'attente' AS statut_type
    FROM ListeAttente la
    JOIN Enfant   e ON e.id  = la.id_enfant
    JOIN Creneau  c ON c.id  = la.id_creneau
    JOIN Activité a ON a.nom = c.nom_activite
    $whereAtt
    ORDER BY a.nom, c.date, c.debut, la.position
");
$stmtAtt->execute($paramsAtt);
$inscritsAtt = $stmtAtt->fetchAll();

// ── Fusion selon filtre statut ───────────────────────────────
if ($filtreStatut === 'accepte') {
    $inscriptions = $inscritsConf;
} elseif ($filtreStatut === 'attente') {
    $inscriptions = $inscritsAtt;
} else {
    // Fusionner : pour chaque créneau, afficher d'abord les confirmés puis les attentes
    $merged = [];
    $keys   = [];
    foreach ($inscritsConf as $r) { $k = $r['id_creneau']; $keys[$k][] = $r; }
    foreach ($inscritsAtt  as $r) { $k = $r['id_creneau']; $keys[$k][] = $r; }
    foreach ($keys as $rows) { foreach ($rows as $r) $merged[] = $r; }
    $inscriptions = $merged;
}

$activites = $db->query("SELECT nom FROM Activité ORDER BY nom")->fetchAll();