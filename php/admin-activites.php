<?php
require_once __DIR__ . '/config.php';
require_once 'mail.php';
requireAdmin();

$db          = getDB();
$message     = '';
$messageType = '';

const THEMES = [
    'Créativité & arts',
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

    // ── Créer une activité ───────────────────────────────────
    if ($action === 'ajouter_activite') {
        $nom         = trim($_POST['nom']         ?? '');
        $cap         = intval($_POST['capacite']   ?? 0);
        $syl         = trim($_POST['syllabus']     ?? '');
        $theme       = trim($_POST['theme']        ?? '');
        $tranche_age = trim($_POST['tranche_age']  ?? '');

        if (!$nom || $cap < 1) {
            $message = 'Le nom et la capacité sont obligatoires.';
            $messageType = 'error';
        } else {
            $chk = $db->prepare("SELECT nom FROM Activite WHERE nom = ?");
            $chk->execute([$nom]);
            if ($chk->fetch()) {
                $message = "Une activité portant ce nom existe déjà.";
                $messageType = 'error';
            } else {
                $db->prepare(
                    "INSERT INTO Activite (nom, capacite, syllabus, theme, tranche_age) VALUES (?, ?, ?, ?, ?)"
                )->execute([$nom, $cap, $syl, $theme, $tranche_age]);
                $message = "Activité $nom créée.";
                $messageType = 'success';
            }
        }
    }

    // ── Modifier une activité ────────────────────────────────
    if ($action === 'modifier_activite') {
        $nomOriginal = trim($_POST['nom_original']  ?? '');
        $cap         = intval($_POST['capacite']    ?? 0);
        $syl         = trim($_POST['syllabus']      ?? '');
        $theme       = trim($_POST['theme']         ?? '');
        $tranche_age = trim($_POST['tranche_age']   ?? '');
        $nouveauNom  = trim($_POST['nouveau_nom']   ?? '');

        if (!$nomOriginal || $cap < 1 || !$nouveauNom) {
            $message = 'Données invalides.';
            $messageType = 'error';
        } else {
            if ($nouveauNom !== $nomOriginal) {
                $chk = $db->prepare("SELECT nom FROM Activite WHERE nom = ?");
                $chk->execute([$nouveauNom]);
                if ($chk->fetch()) {
                    $message = "Ce nom est déjà utilisé par une autre activité.";
                    $messageType = 'error';
                    goto fin_actions;
                }
            }

            $db->prepare(
                "UPDATE Activite SET nom = ?, capacite = ?, syllabus = ?, theme = ?, tranche_age = ? WHERE nom = ?"
            )->execute([$nouveauNom, $cap, $syl, $theme, $tranche_age, $nomOriginal]);

            // Notifier les inscrits (ON UPDATE CASCADE a déjà renommé les créneaux)
            $creneauxActivite = $db->prepare("SELECT id FROM Creneau WHERE nom_activite = ?");
            $creneauxActivite->execute([$nouveauNom]);
            foreach ($creneauxActivite->fetchAll() as $cr) {
                $msgModif = "L'activité \"$nomOriginal\" a été modifiée par l'administration.\n\n"
                    . "Nouveau nom : $nouveauNom\n"
                    . "Nouvelle capacité : $cap places\n\n"
                    . "Votre inscription reste valide. Connectez-vous pour voir les détails.";
                notifierCreneauModifie($db, (int)$cr['id'], 'modification', $msgModif);
            }

            $message = "Activité $nouveauNom mise à jour. Les familles concernées ont été notifiées.";
            $messageType = 'success';
        }
    }

    // ── Supprimer une activité ───────────────────────────────
    if ($action === 'supprimer_activite') {
        $nom = trim($_POST['nom_activite'] ?? '');
        if ($nom) {
            $creneauxStmt = $db->prepare("SELECT id, date, debut, fin FROM Creneau WHERE nom_activite = ?");
            $creneauxStmt->execute([$nom]);
            $creneauxASuppr = $creneauxStmt->fetchAll();

            foreach ($creneauxASuppr as $cr) {
                $msgAnnul = "L'activité \"$nom\" du "
                    . date('d/m/Y', strtotime($cr['date']))
                    . " (" . substr($cr['debut'], 0, 5) . " - " . substr($cr['fin'], 0, 5) . ")"
                    . " a été annulée par l'administration.\n\n"
                    . "Nous sommes désolés pour la gêne occasionnée. Consultez les autres créneaux disponibles.";
                notifierCreneauModifie($db, (int)$cr['id'], 'annulation', $msgAnnul);
            }

            foreach ($creneauxASuppr as $cr) {
                $idCr = (int)$cr['id'];
                $db->prepare("DELETE FROM Notification   WHERE id_creneau = ?")->execute([$idCr]);
                $db->prepare("DELETE FROM ListeAttente   WHERE id_creneau = ?")->execute([$idCr]);
                $db->prepare("DELETE FROM Enfant_Creneau WHERE id_creneau = ?")->execute([$idCr]);
                $db->prepare("DELETE FROM Creneau        WHERE id = ?")->execute([$idCr]);
            }

            $db->prepare("DELETE FROM Activite WHERE nom = ?")->execute([$nom]);
            $message = "Activité et tous ses créneaux supprimés. Les familles ont été notifiées.";
            $messageType = 'success';
        }
    }

    // ── Ajouter un créneau ───────────────────────────────────
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
        $message = "Créneau ajouté.";
        $messageType = 'success';
    }

    // ── Supprimer un créneau ─────────────────────────────────
    if ($action === 'suppr_creneau') {
        $idCr = intval($_POST['id_creneau']);

        $infoCr = $db->prepare("SELECT date, debut, fin, nom_activite FROM Creneau WHERE id = ?");
        $infoCr->execute([$idCr]);
        $crInfo = $infoCr->fetch();

        if ($crInfo) {
            $msgAnnul = "L'activité \"" . $crInfo['nom_activite'] . "\" du "
                . date('d/m/Y', strtotime($crInfo['date']))
                . " (" . substr($crInfo['debut'], 0, 5) . " - " . substr($crInfo['fin'], 0, 5) . ")"
                . " a été annulée par l'administration.\n\n"
                . "Nous sommes désolés pour la gêne occasionnée. Consultez les autres créneaux disponibles.";
            notifierCreneauModifie($db, $idCr, 'annulation', $msgAnnul);
        }

        $db->prepare("DELETE FROM Notification   WHERE id_creneau = ?")->execute([$idCr]);
        $db->prepare("DELETE FROM ListeAttente   WHERE id_creneau = ?")->execute([$idCr]);
        $db->prepare("DELETE FROM Enfant_Creneau WHERE id_creneau = ?")->execute([$idCr]);
        $db->prepare("DELETE FROM Creneau        WHERE id = ?")->execute([$idCr]);

        $message = "Créneau supprimé. Les familles concernées ont été notifiées par mail.";
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