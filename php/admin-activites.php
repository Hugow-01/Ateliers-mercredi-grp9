<?php
require_once __DIR__ . '/config.php';
requireAdmin();

$db          = getDB();
$message     = '';
$messageType = '';

// ══════════════════════════════════════════════════════════════
//  LISTE DES THÈMES DISPONIBLES (cohérence avec le moteur de reco)
// ══════════════════════════════════════════════════════════════
const THEMES = [
    'Créativité & arts',
    'Nature & bien-être',
    'Sport & motricité',
    'Langage & imaginaire',
    'Musique & expression',
    'Sciences & découverte',
    'Numérique & robotique',
    'Théâtre & expression',
];

// ══════════════════════════════════════════════════════════════
//  ACTIONS POST
// ══════════════════════════════════════════════════════════════
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
            // Vérifier unicité du nom
            $chk = $db->prepare("SELECT nom FROM Activité WHERE nom = ?");
            $chk->execute([$nom]);
            if ($chk->fetch()) {
                $message = "Une activité portant ce nom existe déjà.";
                $messageType = 'error';
            } else {
                $db->prepare(
                    "INSERT INTO Activité (nom, capacite, syllabus, theme, tranche_age)
                     VALUES (?, ?, ?, ?, ?)"
                )->execute([$nom, $cap, $syl, $theme, $tranche_age]);
                $message = "✔ Activité « $nom » créée.";
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
            // Si le nom change, vérifier l'unicité
            if ($nouveauNom !== $nomOriginal) {
                $chk = $db->prepare("SELECT nom FROM Activité WHERE nom = ?");
                $chk->execute([$nouveauNom]);
                if ($chk->fetch()) {
                    $message = "Ce nom est déjà utilisé par une autre activité.";
                    $messageType = 'error';
                    goto fin_actions;
                }
                // Mettre à jour les créneaux référençant l'ancien nom
                $db->prepare("UPDATE Creneau SET nom_activite = ? WHERE nom_activite = ?")
                   ->execute([$nouveauNom, $nomOriginal]);
            }

            $db->prepare(
                "UPDATE Activité
                 SET nom = ?, capacite = ?, syllabus = ?, theme = ?, tranche_age = ?
                 WHERE nom = ?"
            )->execute([$nouveauNom, $cap, $syl, $theme, $tranche_age, $nomOriginal]);

            $message = "✔ Activité « $nouveauNom » mise à jour.";
            $messageType = 'success';
        }
    }

    // ── Supprimer une activité ───────────────────────────────
    if ($action === 'supprimer_activite') {
        $nom = trim($_POST['nom_activite'] ?? '');
        if ($nom) {
            // Vérifier qu'il n'y a pas de créneaux actifs
            $chk = $db->prepare("SELECT COUNT(*) FROM Creneau WHERE nom_activite = ?");
            $chk->execute([$nom]);
            if ((int)$chk->fetchColumn() > 0) {
                $message = "Impossible de supprimer : des créneaux existent pour cette activité. Supprimez-les d'abord.";
                $messageType = 'error';
            } else {
                $db->prepare("DELETE FROM Activité WHERE nom = ?")->execute([$nom]);
                $message = "✔ Activité supprimée.";
                $messageType = 'success';
            }
        }
    }

    // ── Ajouter un créneau ───────────────────────────────────
    if ($action === 'ajouter_creneau') {
        $db->prepare(
            "INSERT INTO Creneau (date, debut, fin, nom_activite, id_salle)
             VALUES (?, ?, ?, ?, ?)"
        )->execute([
            $_POST['date'],
            $_POST['debut'],
            $_POST['fin'],
            $_POST['nom_activite'],
            $_POST['id_salle'] ?: null,
        ]);
        $message = "✔ Créneau ajouté.";
        $messageType = 'success';
    }

    // ── Supprimer un créneau ─────────────────────────────────
    if ($action === 'suppr_creneau') {
        $db->prepare("DELETE FROM Creneau WHERE id = ?")->execute([$_POST['id_creneau']]);
        $message = "✔ Créneau supprimé.";
        $messageType = 'success';
    }

    fin_actions:
}

// ══════════════════════════════════════════════════════════════
//  CHARGEMENT
// ══════════════════════════════════════════════════════════════
$activites = $db->query("SELECT * FROM Activité ORDER BY nom")->fetchAll();
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
