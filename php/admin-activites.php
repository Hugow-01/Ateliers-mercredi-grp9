<?php
require_once __DIR__ . '/config.php';
require_once 'mail.php';
requireAdmin();

$db          = getDB();
$message     = '';
$messageType = '';

const THEMES = [
    'Creativite & arts',
    'Nature & bien-etre',
    'Sport & motricite',
    'Langage & imaginaire',
    'Musique & expression',
    'Sciences & decouverte',
    'Numerique & robotique',
    'Theatre & expression',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── Creer une activite ───────────────────────────────────
    if ($action === 'ajouter_activite') {
        $nom         = trim($_POST['nom']         ?? '');
        $cap         = intval($_POST['capacite']   ?? 0);
        $syl         = trim($_POST['syllabus']     ?? '');
        $theme       = trim($_POST['theme']        ?? '');
        $tranche_age = trim($_POST['tranche_age']  ?? '');

        if (!$nom || $cap < 1) {
            $message = 'Le nom et la capacite sont obligatoires.';
            $messageType = 'error';
        } else {
            $chk = $db->prepare("SELECT nom FROM Activite WHERE nom = ?");
            $chk->execute([$nom]);
            if ($chk->fetch()) {
                $message = "Une activite portant ce nom existe deja.";
                $messageType = 'error';
            } else {
                $db->prepare(
                    "INSERT INTO Activite (nom, capacite, syllabus, theme, tranche_age) VALUES (?, ?, ?, ?, ?)"
                )->execute([$nom, $cap, $syl, $theme, $tranche_age]);
                $message = "Activite $nom creee.";
                $messageType = 'success';
            }
        }
    }

    // ── Modifier une activite ────────────────────────────────
    if ($action === 'modifier_activite') {
        $nomOriginal = trim($_POST['nom_original']  ?? '');
        $cap         = intval($_POST['capacite']    ?? 0);
        $syl         = trim($_POST['syllabus']      ?? '');
        $theme       = trim($_POST['theme']         ?? '');
        $tranche_age = trim($_POST['tranche_age']   ?? '');
        $nouveauNom  = trim($_POST['nouveau_nom']   ?? '');

        if (!$nomOriginal || $cap < 1 || !$nouveauNom) {
            $message = 'Donnees invalides.';
            $messageType = 'error';
        } else {
            if ($nouveauNom !== $nomOriginal) {
                $chk = $db->prepare("SELECT nom FROM Activite WHERE nom = ?");
                $chk->execute([$nouveauNom]);
                if ($chk->fetch()) {
                    $message = "Ce nom est deja utilise par une autre activite.";
                    $messageType = 'error';
                    goto fin_actions;
                }
                $db->prepare("UPDATE Creneau SET nom_activite = ? WHERE nom_activite = ?")
                   ->execute([$nouveauNom, $nomOriginal]);
            }

            $db->prepare(
                "UPDATE Activite SET nom = ?, capacite = ?, syllabus = ?, theme = ?, tranche_age = ? WHERE nom = ?"
            )->execute([$nouveauNom, $cap, $syl, $theme, $tranche_age, $nomOriginal]);

            // Envoyer un mail a tous les inscrits des creneaux de cette activite
            $creneauxActivite = $db->prepare("SELECT id FROM Creneau WHERE nom_activite = ?");
            $creneauxActivite->execute([$nouveauNom]);
            foreach ($creneauxActivite->fetchAll() as $cr) {
                $msgModif = "L'activite \"$nomOriginal\" a ete modifiee par l'administration.\n\n"
                    . "Nouveau nom : $nouveauNom\n"
                    . "Nouvelle capacite : $cap places\n\n"
                    . "Votre inscription reste valide. Connectez-vous pour voir les details.";
                notifierCreneauModifie($db, (int)$cr['id'], 'modification', $msgModif);
            }

            $message = "Activite $nouveauNom mise a jour. Les familles concernees ont ete notifiees.";
            $messageType = 'success';
        }
    }

    // ── Supprimer une activite ───────────────────────────────
    if ($action === 'supprimer_activite') {
        $nom = trim($_POST['nom_activite'] ?? '');
        if ($nom) {
            $chk = $db->prepare("SELECT COUNT(*) FROM Creneau WHERE nom_activite = ?");
            $chk->execute([$nom]);
            if ((int)$chk->fetchColumn() > 0) {
                $message = "Impossible de supprimer : des creneaux existent pour cette activite. Supprimez-les d'abord.";
                $messageType = 'error';
            } else {
                $db->prepare("DELETE FROM Activite WHERE nom = ?")->execute([$nom]);
                $message = "Activite supprimee.";
                $messageType = 'success';
            }
        }
    }

    // ── Ajouter un creneau ───────────────────────────────────
    if ($action === 'ajouter_creneau') {
        $db->prepare(
            "INSERT INTO Creneau (date, debut, fin, nom_activite, id_salle) VALUES (?, ?, ?, ?, ?)"
        )->execute([
            $_POST['date'],
            $_POST['debut'],
            $_POST['fin'],
            $_POST['nom_activite'],
            $_POST['id_salle'] ?: null,
        ]);
        $message = "Creneau ajoute.";
        $messageType = 'success';
    }

    // ── Supprimer un creneau ─────────────────────────────────
    if ($action === 'suppr_creneau') {
        $idCr = intval($_POST['id_creneau']);

        // Recuperer les infos du creneau avant de le supprimer
        $infoCr = $db->prepare("
            SELECT c.date, c.debut, c.fin, c.nom_activite
            FROM Creneau c WHERE c.id = ?
        ");
        $infoCr->execute([$idCr]);
        $crInfo = $infoCr->fetch();

        if ($crInfo) {
            $msgAnnul = "L'activite \"" . $crInfo['nom_activite'] . "\" du "
                . date('d/m/Y', strtotime($crInfo['date']))
                . " (" . substr($crInfo['debut'],0,5) . " - " . substr($crInfo['fin'],0,5) . ")"
                . " a ete annulee par l'administration.\n\n"
                . "Nous sommes desoles pour la gene occasionnee. Consultez les autres creneaux disponibles.";

            notifierCreneauModifie($db, $idCr, 'annulation', $msgAnnul);
        }

        $db->prepare("DELETE FROM Creneau WHERE id = ?")->execute([$idCr]);
        $message = "Creneau supprime. Les familles concernees ont ete notifiees par mail.";
        $messageType = 'success';
    }

    fin_actions:
}

// Chargement
$activites = $db->query("SELECT * FROM Activite ORDER BY nom")->fetchAll();
$salles    = $db->query("SELECT id, batiment FROM Salle ORDER BY id")->fetchAll();

$creneaux = $db->query("
    SELECT c.*, COUNT(ec.id_enfant) AS nb
    FROM Creneau c
    LEFT JOIN Enfant_Creneau ec ON ec.id_creneau = c.id
    GROUP BY c.id
    ORDER BY c.date, c.debut
")->fetchAll();

$crByAct = [];
foreach ($creneaux as $cr) {
    $crByAct[$cr['nom_activite']][] = $cr;
}
