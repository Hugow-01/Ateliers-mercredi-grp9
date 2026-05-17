<?php
require_once __DIR__ . '/config.php';
require_once 'mail.php';
requireAdmin();

$db = getDB();
$message = '';
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

// gestion upload image ─────────────────────────────
function handleImageUpload(array $file, string $nomActivite): ?string {
    if (empty($file['tmp_name'])) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) return null;

    $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowed)) return null;
    if ($file['size'] > 5 * 1024 * 1024) return null;

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $slug = preg_replace('/[^a-z0-9]/i', '_', $nomActivite);
    $dest = __DIR__ . '/../images/activites/';
    if (!is_dir($dest)) mkdir($dest, 0755, true);
    $filename = $slug . '_' . time() . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $dest . $filename)) {
        return 'images/activites/' . $filename;
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── Créer une activité ───────────────────────────────────
    if ($action === 'ajouter_activite') {
        $nom = trim($_POST['nom'] ?? '');
        $cap = intval($_POST['capacite'] ?? 0);
        $syl = trim($_POST['syllabus'] ?? '');
        $theme = trim($_POST['theme'] ?? '');
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
                try {
                    $db->query("SELECT image FROM Activite LIMIT 1");
                } catch (PDOException $e) {
                    $db->exec("ALTER TABLE Activite ADD COLUMN image VARCHAR(255) DEFAULT NULL");
                }

                $imgPath = null;
                if (!empty($_FILES['image']['tmp_name'])) {
                    $imgPath = handleImageUpload($_FILES['image'], $nom);
                }

                $db->prepare(
                    "INSERT INTO Activite (nom, capacite, syllabus, theme, tranche_age, image) VALUES (?, ?, ?, ?, ?, ?)"
                )->execute([$nom, $cap, $syl, $theme, $tranche_age, $imgPath]);
                $message = "Activité $nom créée.";
                $messageType = 'success';
            }
        }
    }

    // ── Modifier une activité ────────────────────────────────
    if ($action === 'modifier_activite') {
        $nomOriginal = trim($_POST['nom_original'] ?? '');
        $cap = intval($_POST['capacite'] ?? 0);
        $syl = trim($_POST['syllabus'] ?? '');
        $theme = trim($_POST['theme'] ?? '');
        $tranche_age = trim($_POST['tranche_age'] ?? '');
        $nouveauNom = trim($_POST['nouveau_nom'] ?? '');

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

            // Récupérer les anciennes valeurs pour comparer
            $ancienneActivite = $db->prepare("SELECT nom, capacite, syllabus, theme, tranche_age FROM Activite WHERE nom = ?");
            $ancienneActivite->execute([$nomOriginal]);
            $ancienne = $ancienneActivite->fetch();

            // Gérer l'image
            $imgPath = null;
            if (!empty($_FILES['image']['tmp_name'])) {
                $imgPath = handleImageUpload($_FILES['image'], $nouveauNom);
            }

            if ($imgPath) {
                $db->prepare(
                    "UPDATE Activite SET nom = ?, capacite = ?, syllabus = ?, theme = ?, tranche_age = ?, image = ? WHERE nom = ?"
                )->execute([$nouveauNom, $cap, $syl, $theme, $tranche_age, $imgPath, $nomOriginal]);
            } else {
                $db->prepare(
                    "UPDATE Activite SET nom = ?, capacite = ?, syllabus = ?, theme = ?, tranche_age = ? WHERE nom = ?"
                )->execute([$nouveauNom, $cap, $syl, $theme, $tranche_age, $nomOriginal]);
            }

            // ── Construire un message précis listant les changements ──
            $changements = [];
            if ($ancienne) {
                if ($nouveauNom !== $ancienne['nom']) {
                    $changements[] = "• Nom : « {$ancienne['nom']} » → « $nouveauNom »";
                }
                if ((int)$cap !== (int)$ancienne['capacite']) {
                    $changements[] = "• Capacité : {$ancienne['capacite']} places → $cap places";
                }
                if ($theme !== ($ancienne['theme'] ?? '')) {
                    $changements[] = "• Thème : « {$ancienne['theme']} » → « $theme »";
                }
                if ($tranche_age !== ($ancienne['tranche_age'] ?? '')) {
                    $changements[] = "• Tranche d'âge : {$ancienne['tranche_age']} → $tranche_age ans";
                }
                if ($syl !== ($ancienne['syllabus'] ?? '')) {
                    $changements[] = "• Description de l'atelier a été mise à jour.";
                }
            }

            if (!empty($changements)) {
                $listeChangements = implode("\n", $changements);
                $creneauxActivite = $db->prepare("SELECT id FROM Creneau WHERE nom_activite = ?");
                $creneauxActivite->execute([$nouveauNom]);
                foreach ($creneauxActivite->fetchAll() as $cr) {
                    $msgModif = "L'activité « $nomOriginal » a été modifiée par l'administration.\n\n"
                        . "Modifications apportées :\n"
                        . $listeChangements . "\n\n"
                        . "Votre inscription reste valide. Connectez-vous pour voir les détails.";
                    notifierCreneauModifie($db, (int)$cr['id'], 'modification', $msgModif);
                }
            }

            $message = "Activité $nouveauNom mise à jour.";
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
                $msgAnnul = "L'activité « $nom » du "
                    . date('d/m/Y', strtotime($cr['date']))
                    . " (" . substr($cr['debut'], 0, 5) . " - " . substr($cr['fin'], 0, 5) . ")"
                    . " a été annulée par l'administration.\n\n"
                    . "Nous sommes désolés pour la gêne occasionnée.";
                notifierCreneauModifie($db, (int)$cr['id'], 'annulation', $msgAnnul);
            }

            foreach ($creneauxASuppr as $cr) {
                $idCr = (int)$cr['id'];
                $db->prepare("DELETE FROM Notification WHERE id_creneau = ?")->execute([$idCr]);
                $db->prepare("DELETE FROM ListeAttente WHERE id_creneau = ?")->execute([$idCr]);
                $db->prepare("DELETE FROM Enfant_Creneau WHERE id_creneau = ?")->execute([$idCr]);
                $db->prepare("DELETE FROM Creneau WHERE id = ?")->execute([$idCr]);
            }

            $db->prepare("DELETE FROM Activite WHERE nom = ?")->execute([$nom]);
            $message = "Activité et tous ses créneaux supprimés.";
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

    // ── Modifier un créneau ──────────────────────────────────
    if ($action === 'modifier_creneau') {
        $idCr = intval($_POST['id_creneau'] ?? 0);
        $nouvelleDate = trim($_POST['nouvelle_date']  ?? '');
        $nouveauDebut = trim($_POST['nouveau_debut']  ?? '');
        $nouveauFin = trim($_POST['nouveau_fin']    ?? '');
        $nouvelleSalle = trim($_POST['nouvelle_salle'] ?? '') ?: null;

        if ($idCr && $nouvelleDate && $nouveauDebut && $nouveauFin) {
            $ancienCr = $db->prepare("SELECT date, debut, fin, nom_activite, id_salle FROM Creneau WHERE id = ?");
            $ancienCr->execute([$idCr]);
            $ancien = $ancienCr->fetch();

            $db->prepare(
                "UPDATE Creneau SET date = ?, debut = ?, fin = ?, id_salle = ? WHERE id = ?"
            )->execute([$nouvelleDate, $nouveauDebut, $nouveauFin, $nouvelleSalle, $idCr]);

            if ($ancien) {
                // ── Construire un message précis sur les changements du créneau ──
                $changements = [];
                $ancienneDate = $ancien['date'];
                $ancienDebut  = $ancien['debut'];
                $ancienneFin  = $ancien['fin'];
                $ancienneSalle = $ancien['id_salle'] ?? '';

                if ($nouvelleDate !== $ancienneDate) {
                    $changements[] = "• Date : " . date('d/m/Y', strtotime($ancienneDate))
                        . " → " . date('d/m/Y', strtotime($nouvelleDate));
                }
                if (substr($nouveauDebut, 0, 5) !== substr($ancienDebut, 0, 5)) {
                    $changements[] = "• Heure de début : " . substr($ancienDebut, 0, 5)
                        . " → " . substr($nouveauDebut, 0, 5);
                }
                if (substr($nouveauFin, 0, 5) !== substr($ancienneFin, 0, 5)) {
                    $changements[] = "• Heure de fin : " . substr($ancienneFin, 0, 5)
                        . " → " . substr($nouveauFin, 0, 5);
                }
                if ($nouvelleSalle !== $ancienneSalle) {
                    $ancSalleLabel = $ancienneSalle ?: 'aucune';
                    $nvSalleLabel  = $nouvelleSalle ?: 'aucune';
                    $changements[] = "• Salle : $ancSalleLabel → $nvSalleLabel";
                }

                if (!empty($changements)) {
                    $listeChangements = implode("\n", $changements);
                    $msgModif = "Un créneau de l'activité « {$ancien['nom_activite']} » a été modifié par l'administration.\n\n"
                        . "Modifications apportées :\n"
                        . $listeChangements . "\n\n"
                        . "Votre inscription reste valide. Si ce nouvel horaire ne vous convient pas, "
                        . "vous pouvez vous désinscrire depuis votre espace parent.";

                    notifierCreneauModifie($db, $idCr, 'modification', $msgModif);
                }
            }

            $message = "Créneau modifié. Les familles ont été notifiées.";
            $messageType = 'success';
        } else {
            $message = "Données manquantes pour la modification.";
            $messageType = 'error';
        }
    }

    // ── Supprimer un créneau ─────────────────────────────────
    if ($action === 'suppr_creneau') {
        $idCr = intval($_POST['id_creneau']);

        $infoCr = $db->prepare("SELECT date, debut, fin, nom_activite FROM Creneau WHERE id = ?");
        $infoCr->execute([$idCr]);
        $crInfo = $infoCr->fetch();

        if ($crInfo) {
            $msgAnnul = "Le créneau de l'activité « " . $crInfo['nom_activite'] . " » du "
                . date('d/m/Y', strtotime($crInfo['date']))
                . " (" . substr($crInfo['debut'], 0, 5) . " - " . substr($crInfo['fin'], 0, 5) . ")"
                . " a été annulé par l'administration.\n\n"
                . "Nous sommes désolés pour la gêne occasionnée.";
            notifierCreneauModifie($db, $idCr, 'annulation', $msgAnnul);
        }

        $db->prepare("DELETE FROM Notification WHERE id_creneau = ?")->execute([$idCr]);
        $db->prepare("DELETE FROM ListeAttente WHERE id_creneau = ?")->execute([$idCr]);
        $db->prepare("DELETE FROM Enfant_Creneau WHERE id_creneau = ?")->execute([$idCr]);
        $db->prepare("DELETE FROM Creneau WHERE id = ?")->execute([$idCr]);

        $message = "Créneau supprimé. Les familles ont été notifiées.";
        $messageType = 'success';
    }

    fin_actions:
}

// Vérifier/ajouter la colonne image si nécessaire
try {
    $db->query("SELECT image FROM Activite LIMIT 1");
} catch (PDOException $e) {
    $db->exec("ALTER TABLE Activite ADD COLUMN image VARCHAR(255) DEFAULT NULL");
}

// Chargement
$activites = $db->query("SELECT * FROM Activite ORDER BY nom")->fetchAll();
$salles = $db->query("SELECT id, batiment FROM Salle ORDER BY id")->fetchAll();

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