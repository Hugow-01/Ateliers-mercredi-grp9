<?php
require_once __DIR__ . '/config.php';
require_once 'mail.php';
requireParent();

$db      = getDB();
$login   = $_SESSION['user'];
$message = '';
$messageType = '';

// Récupérer l'id_famille à partir du login en session
$idFamille = getIdFamille($db, $login);

// Charger les enfants de la famille
$enfantsStmt = $db->prepare("SELECT id, nom, prenom, age FROM Enfant WHERE id_famille = ? ORDER BY prenom");
$enfantsStmt->execute([$idFamille]);
$enfants = $enfantsStmt->fetchAll();

// ── INSCRIPTION ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'inscrire') {
    $id_enfant  = intval($_POST['id_enfant']  ?? 0);
    $id_creneau = intval($_POST['id_creneau'] ?? 0);

    if (!$id_enfant || !$id_creneau) {
        $message = 'Veuillez selectionner un enfant et un creneau.';
        $messageType = 'error';
    } else {
        // Vérifier que l'enfant appartient à cette famille
        $chk = $db->prepare("SELECT id FROM Enfant WHERE id = ? AND id_famille = ?");
        $chk->execute([$id_enfant, $idFamille]);

        if (!$chk->fetch()) {
            $message = 'Enfant non trouve.';
            $messageType = 'error';
        } else {
            $dejaConf = $db->prepare("SELECT 1 FROM Enfant_Creneau WHERE id_enfant = ? AND id_creneau = ?");
            $dejaConf->execute([$id_enfant, $id_creneau]);

            $dejaAtt = $db->prepare("SELECT position FROM ListeAttente WHERE id_enfant = ? AND id_creneau = ?");
            $dejaAtt->execute([$id_enfant, $id_creneau]);
            $ligneAtt = $dejaAtt->fetch();

            if ($dejaConf->fetch()) {
                $message = 'Cet enfant est deja inscrit a ce creneau.';
                $messageType = 'error';
            } elseif ($ligneAtt) {
                $message = "Cet enfant est deja en liste d'attente (position #{$ligneAtt['position']}).";
                $messageType = 'info';
            } else {
                $nb  = nbInscrits($db, $id_creneau);
                $cap = capaciteCreneau($db, $id_creneau);

                if ($nb < $cap) {
                    $db->prepare("INSERT INTO Enfant_Creneau (id_enfant, id_creneau) VALUES (?, ?)")
                       ->execute([$id_enfant, $id_creneau]);
                    $message = 'Inscription confirmee !';
                    $messageType = 'success';
                } else {
                    $pos = prochainePosition($db, $id_creneau);
                    $db->prepare("INSERT INTO ListeAttente (id_enfant, id_creneau, position) VALUES (?, ?, ?)")
                       ->execute([$id_enfant, $id_creneau, $pos]);
                    $message = "Creneau complet - votre enfant est en liste d'attente (position #$pos).";
                    $messageType = 'info';

                    $stmtInfo = $db->prepare("
                        SELECT c.date, c.debut, c.fin, c.nom_activite,
                               e.nom, e.prenom, e.id_famille
                        FROM Creneau c
                        JOIN Enfant e ON e.id = ?
                        WHERE c.id = ?
                    ");
                    $stmtInfo->execute([$id_enfant, $id_creneau]);
                    $info = $stmtInfo->fetch();

                    $msgMail = "Votre enfant "
                        . $info['prenom'] . " " . $info['nom']
                        . " a ete place en liste d'attente pour l'activite \""
                        . $info['nom_activite'] . "\" du "
                        . date('d/m/Y', strtotime($info['date']))
                        . " (" . substr($info['debut'],0,5)
                        . " - " . substr($info['fin'],0,5)
                        . ").\n\n"
                        . "Position actuelle : #$pos.\n\n"
                        . "Vous serez notifie si une place se libere.";

                    notifierFamille(
                        $db,
                        (int)$info['id_famille'],
                        $id_enfant,
                        $id_creneau,
                        'attente',
                        $msgMail
                    );
                }
            }
        }
    }
    $enfantsStmt->execute([$idFamille]);
    $enfants = $enfantsStmt->fetchAll();
}

// ── DESINSCRIPTION ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'desinscrire') {
    $id_enfant  = intval($_POST['id_enfant']  ?? 0);
    $id_creneau = intval($_POST['id_creneau'] ?? 0);

    $chk = $db->prepare("SELECT id FROM Enfant WHERE id = ? AND id_famille = ?");
    $chk->execute([$id_enfant, $idFamille]);

    if ($chk->fetch()) {
        $db->prepare("DELETE FROM Enfant_Creneau WHERE id_enfant = ? AND id_creneau = ?")
           ->execute([$id_enfant, $id_creneau]);

        $premier = $db->prepare("SELECT id_enfant FROM ListeAttente WHERE id_creneau = ? ORDER BY position ASC LIMIT 1");
        $premier->execute([$id_creneau]);
        $promo = $premier->fetchColumn();

        if ($promo) {
            $db->prepare("INSERT INTO Enfant_Creneau (id_enfant, id_creneau) VALUES (?, ?)")
               ->execute([$promo, $id_creneau]);
            $db->prepare("DELETE FROM ListeAttente WHERE id_enfant = ? AND id_creneau = ?")
               ->execute([$promo, $id_creneau]);

            $stmtPromo = $db->prepare("
                SELECT e.nom, e.prenom, e.id_famille,
                       c.date, c.debut, c.fin, c.nom_activite
                FROM Enfant e
                JOIN Creneau c ON c.id = ?
                WHERE e.id = ?
            ");
            $stmtPromo->execute([$id_creneau, $promo]);
            $promoInfo = $stmtPromo->fetch();

            $msgPromo = "Bonne nouvelle !\n\n"
                . "Une place s'est liberee pour l'activite \""
                . $promoInfo['nom_activite'] . "\" du "
                . date('d/m/Y', strtotime($promoInfo['date']))
                . " (" . substr($promoInfo['debut'],0,5)
                . " - " . substr($promoInfo['fin'],0,5)
                . ").\n\n"
                . "Votre enfant "
                . $promoInfo['prenom'] . " " . $promoInfo['nom']
                . " est maintenant inscrit(e) avec une place confirmee.";

            notifierFamille(
                $db,
                (int)$promoInfo['id_famille'],
                $promo,
                $id_creneau,
                'accepte',
                $msgPromo
            );

            $restants = $db->prepare("SELECT id FROM ListeAttente WHERE id_creneau = ? ORDER BY position ASC");
            $restants->execute([$id_creneau]);
            $pos = 1;
            foreach ($restants->fetchAll() as $r) {
                $db->prepare("UPDATE ListeAttente SET position = ? WHERE id = ?")->execute([$pos++, $r['id']]);
            }
        }
        $message = 'Desinscription effectuee.';
        $messageType = 'success';
    }
}

// ── QUITTER LISTE D'ATTENTE ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'quitter_attente') {
    $id_enfant  = intval($_POST['id_enfant']  ?? 0);
    $id_creneau = intval($_POST['id_creneau'] ?? 0);

    $chk = $db->prepare("SELECT id FROM Enfant WHERE id = ? AND id_famille = ?");
    $chk->execute([$id_enfant, $idFamille]);

    if ($chk->fetch()) {
        $db->prepare("DELETE FROM ListeAttente WHERE id_enfant = ? AND id_creneau = ?")
           ->execute([$id_enfant, $id_creneau]);

        $restants = $db->prepare("SELECT id FROM ListeAttente WHERE id_creneau = ? ORDER BY position ASC");
        $restants->execute([$id_creneau]);
        $pos = 1;
        foreach ($restants->fetchAll() as $r) {
            $db->prepare("UPDATE ListeAttente SET position = ? WHERE id = ?")->execute([$pos++, $r['id']]);
        }
        $message = 'Retire de la liste d\'attente.';
        $messageType = 'success';
    }
}

// ── CHARGEMENT DES DONNEES ──────────────────────────────────
$activites = $db->query("SELECT * FROM Activite ORDER BY nom")->fetchAll();

$allCreneaux = $db->query("
    SELECT c.*,
           s.batiment, s.id AS salle_id,
           COUNT(DISTINCT ec.id_enfant) AS nb_inscrits,
           COUNT(DISTINCT la.id_enfant) AS nb_attente,
           a.capacite                   AS cap_activite,
           a.theme                      AS theme,
           a.tranche_age                AS tranche_age
    FROM Creneau c
    LEFT JOIN Salle s           ON s.id = c.id_salle
    LEFT JOIN Activite a        ON a.nom = c.nom_activite
    LEFT JOIN Enfant_Creneau ec ON ec.id_creneau = c.id
    LEFT JOIN ListeAttente la   ON la.id_creneau = c.id
    GROUP BY c.id
    ORDER BY c.date, c.debut
")->fetchAll();

$creneauxByActivite = [];
foreach ($allCreneaux as $cr) {
    $creneauxByActivite[$cr['nom_activite']][] = $cr;
}

// Inscriptions confirmees et attentes de cette famille
$mesInscriptions = [];
$mesAttentes     = [];

$enfantsById = [];
foreach ($enfants as $enf) {
    $enfantsById[$enf['id']] = $enf;
}

if (!empty($enfants)) {
    $ids = implode(',', array_map('intval', array_column($enfants, 'id')));
    $rowsConf = $db->query(
        "SELECT id_enfant, id_creneau FROM Enfant_Creneau WHERE id_enfant IN ($ids)"
    )->fetchAll();
    foreach ($rowsConf as $r) {
        $mesInscriptions[$r['id_enfant']][] = (int) $r['id_creneau'];
    }
    $rowsAtt = $db->query(
        "SELECT id_enfant, id_creneau, position FROM ListeAttente WHERE id_enfant IN ($ids)"
    )->fetchAll();
    foreach ($rowsAtt as $r) {
        $mesAttentes[$r['id_enfant']][$r['id_creneau']] = (int) $r['position'];
    }
}

// Pre-calcul des recommandations pour les creneaux complets
$recommendationsData = [];
foreach ($allCreneaux as $cr) {
    if ((int)$cr['nb_inscrits'] >= (int)$cr['cap_activite']) {
        $recs = creneauxRecommandes($db, (int)$cr['id'], 0);
        if (!empty($recs)) {
            $recommendationsData[$cr['id']] = $recs;
        }
    }
}

// Images par activite
$imgMap = [
    'Arts'    => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=400',
    'Jeux'    => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=400',
    'Musique' => 'https://images.unsplash.com/photo-1511379938547-c1f69419868d?w=400',
    'Lecture' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=400',
    'Cuisine' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400',
    'football'=> 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?w=400',
];

function getImg(string $nom, array $map): string {
    foreach ($map as $k => $v) {
        if (stripos($nom, $k) !== false) return $v;
    }
    return 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=400';
}