<?php
require_once __DIR__ . '/config.php';
require_once 'mail.php';
requireAdmin();

$db = getDB();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'inscrire_admin') {
    $ids_enfants = $_POST['ids_enfants'] ?? [];
    $id_creneau = intval($_POST['id_creneau'] ?? 0);

    if (empty($ids_enfants) || !$id_creneau) {
        $message = "Sélectionnez au moins un enfant et un créneau.";
        $messageType = 'error';
    } else {
        $nb_ok = 0;
        $nb_attente = 0;

        // Récupérer la capacité du créneau
        $cap = capaciteCreneau($db, $id_creneau);

        foreach ($ids_enfants as $id_enfant) {
            $id_enfant = intval($id_enfant);

            // Déjà inscrit en confirmé ?
            $checkConf = $db->prepare("SELECT 1 FROM Enfant_Creneau WHERE id_enfant = ? AND id_creneau = ?");
            $checkConf->execute([$id_enfant, $id_creneau]);
            if ($checkConf->fetch()) continue;

            // Déjà en liste d'attente ?
            $checkAtt = $db->prepare("SELECT 1 FROM ListeAttente WHERE id_enfant = ? AND id_creneau = ?");
            $checkAtt->execute([$id_enfant, $id_creneau]);
            if ($checkAtt->fetch()) continue;

            // Compter les inscrits actuels
            $nb = nbInscrits($db, $id_creneau);

            if ($nb < $cap) {
                // Place disponible → inscription directe
                $db->prepare("INSERT INTO Enfant_Creneau (id_enfant, id_creneau) VALUES (?, ?)")
                   ->execute([$id_enfant, $id_creneau]);

                // Récupérer famille de l'enfant pour notifier
                $stmtEnf = $db->prepare("SELECT e.id_famille FROM Enfant e WHERE e.id = ?");
                $stmtEnf->execute([$id_enfant]);
                $enf = $stmtEnf->fetch();
                if ($enf) {
                    try {
                        envoyerMailConfirmationInscription($db, (int)$enf['id_famille'], $id_enfant, $id_creneau);
                    } catch (Exception $e) {
                        error_log("Erreur mail confirmation admin: " . $e->getMessage());
                    }
                }
                $nb_ok++;
            } else {
                // Créneau plein → liste d'attente
                $pos = prochainePosition($db, $id_creneau);
                $db->prepare("INSERT INTO ListeAttente (id_enfant, id_creneau, position) VALUES (?, ?, ?)")
                   ->execute([$id_enfant, $id_creneau, $pos]);

                // Notifier la famille
                $stmtInfo = $db->prepare("
                    SELECT c.date, c.debut, c.fin, c.nom_activite,
                    e.nom, e.prenom, e.id_famille
                    FROM Creneau c
                    JOIN Enfant e ON e.id = ?
                    WHERE c.id = ?
                ");
                $stmtInfo->execute([$id_enfant, $id_creneau]);
                $info = $stmtInfo->fetch();

                if ($info) {
                    $msgMail = "Votre enfant " . $info['prenom'] . " " . $info['nom']
                        . " a été placé en liste d'attente pour l'activité « "
                        . $info['nom_activite'] . " » du "
                        . date('d/m/Y', strtotime($info['date']))
                        . " (" . substr($info['debut'], 0, 5)
                        . " - " . substr($info['fin'], 0, 5) . ").\n\n"
                        . "Position actuelle : #$pos.\n\n"
                        . "Vous serez notifié si une place se libère.";

                    notifierFamille(
                        $db,
                        (int)$info['id_famille'],
                        $id_enfant,
                        $id_creneau,
                        'attente',
                        $msgMail
                    );
                }
                $nb_attente++;
            }
        }

        $parts = [];
        if ($nb_ok > 0) $parts[] = "$nb_ok enfant(s) inscrit(s) avec succès";
        if ($nb_attente > 0) $parts[] = "$nb_attente enfant(s) placé(s) en liste d'attente (créneau complet)";

        if (!empty($parts)) {
            $message = implode(' · ', $parts) . '.';
            $messageType = ($nb_attente > 0 && $nb_ok === 0) ? 'info' : 'success';
        } else {
            $message = "Aucun changement (déjà inscrit(s) ou en attente).";
            $messageType = 'info';
        }
    }
}

// Recherche par id de famille (on reçoit l'id via GET)
$idFamilleRecherche = intval($_GET['login_famille'] ?? 0);
$enfantsTrouves = [];
if ($idFamilleRecherche) {
    $s = $db->prepare("SELECT * FROM Enfant WHERE id_famille = ? ORDER BY nom");
    $s->execute([$idFamilleRecherche]);
    $enfantsTrouves = $s->fetchAll();
}

// Pour le select, on liste les familles avec leur id comme valeur
$familles = $db->query("SELECT id, login, nom FROM Famille ORDER BY nom")->fetchAll();

$creneaux = $db->query("
    SELECT c.id, c.date, c.debut, c.fin, c.nom_activite, a.capacite,
    COUNT(ec.id_enfant) AS nb_inscrits
    FROM Creneau c
    JOIN Activite a ON a.nom = c.nom_activite
    LEFT JOIN Enfant_Creneau ec ON ec.id_creneau = c.id
    GROUP BY c.id
    ORDER BY c.date, c.nom_activite, c.debut
")->fetchAll();