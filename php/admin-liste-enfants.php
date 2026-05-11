<?php
require_once __DIR__ . '/config.php';
require_once 'mail.php';
requireAdmin();

$db = getDB();
$message = '';
$messageType = '';

// ══════════════════════════════════════════════════════════════
//  ACTION : désinscrire un enfant (confirmé ou liste d'attente)
// ══════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'desinscrire_admin') {

    $idEnfant  = intval($_POST['id_enfant']  ?? 0);
    $idCreneau = intval($_POST['id_creneau'] ?? 0);
    $typeStatut = $_POST['statut_type'] ?? '';

    $stmtEnf = $db->prepare("
        SELECT e.nom, e.prenom, e.id_famille
        FROM Enfant e
        WHERE e.id = ?
    ");
    $stmtEnf->execute([$idEnfant]);
    $enf = $stmtEnf->fetch();

    $stmtCr = $db->prepare("SELECT date, debut, fin, nom_activite FROM Creneau WHERE id = ?");
    $stmtCr->execute([$idCreneau]);
    $cr = $stmtCr->fetch();

    if ($enf && $cr) {
        $prenomNom = $enf['prenom'] . ' ' . $enf['nom'];
        $dateF = date('d/m/Y', strtotime($cr['date']));
        $hDeb = substr($cr['debut'], 0, 5);
        $hFin = substr($cr['fin'],   0, 5);
        $activite = $cr['nom_activite'];

        if ($typeStatut === 'accepte') {
            // Supprimer l'inscription confirmée
            $db->prepare("DELETE FROM Enfant_Creneau WHERE id_enfant=? AND id_creneau=?")
               ->execute([$idEnfant, $idCreneau]);

            // Promouvoir le premier de la liste d'attente
            $premier = $db->prepare("SELECT id_enfant FROM ListeAttente WHERE id_creneau=? ORDER BY position ASC LIMIT 1");
            $premier->execute([$idCreneau]);
            $promo = $premier->fetchColumn();

            if ($promo) {
                $db->prepare("INSERT INTO Enfant_Creneau (id_enfant, id_creneau) VALUES (?,?)")
                   ->execute([$promo, $idCreneau]);
                $db->prepare("DELETE FROM ListeAttente WHERE id_enfant=? AND id_creneau=?")
                   ->execute([$promo, $idCreneau]);

                // Renuméroter
                $restants = $db->prepare("SELECT id FROM ListeAttente WHERE id_creneau=? ORDER BY position ASC");
                $restants->execute([$idCreneau]);
                $pos = 1;
                foreach ($restants->fetchAll() as $r) {
                    $db->prepare("UPDATE ListeAttente SET position=? WHERE id=?")->execute([$pos++, $r['id']]);
                }

                // Notifier le promu
                $stmtPromo = $db->prepare("SELECT e.nom, e.prenom, e.id_famille FROM Enfant e WHERE e.id=?");
                $stmtPromo->execute([$promo]);
                $promoInfo = $stmtPromo->fetch();
                if ($promoInfo) {
                    $msgPromo = "Bonne nouvelle ! Une place s'est libérée pour l'activité \"$activite\" du $dateF ($hDeb–$hFin).\n\n"
                        . "Votre enfant " . $promoInfo['prenom'] . " " . $promoInfo['nom'] . " est maintenant inscrit(e) avec une place confirmée.";
                    notifierFamille($db, (int)$promoInfo['id_famille'], $promo, $idCreneau, 'accepte', $msgPromo);
                }
            }

            // Notifier la famille désinscrite
            $msgDesins = "L'administrateur a désinscrit $prenomNom de l'activité \"$activite\" du $dateF ($hDeb–$hFin).";
            notifierFamille($db, (int)$enf['id_famille'], $idEnfant, $idCreneau, 'annulation', $msgDesins);

            $message     = "$prenomNom désinscrit(e). La famille a été notifiée.";
            $messageType = 'success';

        } elseif ($typeStatut === 'attente') {
            // Retirer de la liste d'attente
            $db->prepare("DELETE FROM ListeAttente WHERE id_enfant=? AND id_creneau=?")
               ->execute([$idEnfant, $idCreneau]);

            // Renuméroter
            $restants = $db->prepare("SELECT id FROM ListeAttente WHERE id_creneau=? ORDER BY position ASC");
            $restants->execute([$idCreneau]);
            $pos = 1;
            foreach ($restants->fetchAll() as $r) {
                $db->prepare("UPDATE ListeAttente SET position=? WHERE id=?")->execute([$pos++, $r['id']]);
            }

            $msgRetrait = "L'administrateur a retiré $prenomNom de la liste d'attente pour l'activité \"$activite\" du $dateF ($hDeb–$hFin).";
            notifierFamille($db, (int)$enf['id_famille'], $idEnfant, $idCreneau, 'annulation', $msgRetrait);

            $message     = "$prenomNom retiré(e) de la liste d'attente. La famille a été notifiée.";
            $messageType = 'success';
        }
    }
}

// ══════════════════════════════════════════════════════════════
//  CHARGEMENT DES DONNÉES
// ══════════════════════════════════════════════════════════════
$filtreActivite = trim($_GET['activite'] ?? '');
$filtreDate = trim($_GET['date']     ?? '');
$filtreStatut = trim($_GET['statut']   ?? '');
$filtreNomEnf = trim($_GET['nom_enfant'] ?? '');
$filtreEmail = trim($_GET['email_parent'] ?? '');

// ── Inscrits confirmés ──────────────────────────────────────
$whereConf  = "WHERE 1=1";
$paramsConf = [];
if ($filtreActivite) { $whereConf .= " AND a.nom = ?"; $paramsConf[] = $filtreActivite; }
if ($filtreDate) { $whereConf .= " AND c.date = ?"; $paramsConf[] = $filtreDate; }
if ($filtreNomEnf) { $whereConf .= " AND (e.nom LIKE ? OR e.prenom LIKE ?)"; $paramsConf[] = "%$filtreNomEnf%"; $paramsConf[] = "%$filtreNomEnf%"; }
if ($filtreEmail) { $whereConf .= " AND f.login LIKE ?"; $paramsConf[] = "%$filtreEmail%"; }

$stmtConf = $db->prepare("SELECT e.id AS id_enfant, e.nom, e.prenom, e.age, e.id_famille,f.login AS login_famille,
a.nom AS activite, a.capacite, c.date, c.debut, c.fin, c.id AS id_creneau,
'accepte' AS statut_type FROM Enfant_Creneau ec JOIN Enfant   e ON e.id  = ec.id_enfant JOIN Famille  f ON f.id  = e.id_famille
JOIN Creneau  c ON c.id  = ec.id_creneau JOIN Activite a ON a.nom = c.nom_activite $whereConf
ORDER BY a.nom, c.date, c.debut, e.nom
");
$stmtConf->execute($paramsConf);
$inscritsConf = $stmtConf->fetchAll();

// ── Liste d'attente ─────────────────────────────────────────
$whereAtt  = "WHERE 1=1";
$paramsAtt = [];
if ($filtreActivite) { $whereAtt .= " AND a.nom = ?";$paramsAtt[] = $filtreActivite; }
if ($filtreDate) { $whereAtt .= " AND c.date = ?";$paramsAtt[] = $filtreDate; }
if ($filtreNomEnf) { $whereAtt .= " AND (e.nom LIKE ? OR e.prenom LIKE ?)"; $paramsAtt[] = "%$filtreNomEnf%"; $paramsAtt[] = "%$filtreNomEnf%"; }
if ($filtreEmail) { $whereAtt .= " AND f.login LIKE ?";$paramsAtt[] = "%$filtreEmail%"; }

$stmtAtt = $db->prepare("SELECT e.id AS id_enfant, e.nom, e.prenom, e.age, e.id_famille,f.login AS login_famille,
a.nom AS activite, a.capacite,c.date, c.debut, c.fin, c.id AS id_creneau,la.position,'attente' AS statut_type
FROM ListeAttente la JOIN Enfant   e ON e.id  = la.id_enfant JOIN Famille  f ON f.id  = e.id_famille
JOIN Creneau  c ON c.id  = la.id_creneau JOIN Activite a ON a.nom = c.nom_activite $whereAtt
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
    $merged = [];
    $keys = [];
    foreach ($inscritsConf as $r) { $k = $r['id_creneau']; $keys[$k][] = $r; }
    foreach ($inscritsAtt  as $r) { $k = $r['id_creneau']; $keys[$k][] = $r; }
    foreach ($keys as $rows) { foreach ($rows as $r) $merged[] = $r; }
    $inscriptions = $merged;
}

$activites = $db->query("SELECT nom FROM Activite ORDER BY nom")->fetchAll();