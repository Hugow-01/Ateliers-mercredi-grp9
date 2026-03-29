<?php
require_once __DIR__ . '/config.php';
requireParent();

$db      = getDB();
$login   = $_SESSION['user'];
$message = '';
$messageType = '';

// Charger les enfants de la famille
$enfantsStmt = $db->prepare("SELECT id, nom, prenom FROM Enfant WHERE login_famille = ? ORDER BY prenom");
$enfantsStmt->execute([$login]);
$enfants = $enfantsStmt->fetchAll();

// ── INSCRIPTION ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'inscrire') {
    $id_enfant  = intval($_POST['id_enfant']  ?? 0);
    $id_creneau = intval($_POST['id_creneau'] ?? 0);

    if (!$id_enfant || !$id_creneau) {
        $message = 'Veuillez sélectionner un enfant et un créneau.';
        $messageType = 'error';
    } else {
        $chk = $db->prepare("SELECT id FROM Enfant WHERE id = ? AND login_famille = ?");
        $chk->execute([$id_enfant, $login]);

        if (!$chk->fetch()) {
            $message = 'Enfant non trouvé.';
            $messageType = 'error';
        } else {
            $dejaConf = $db->prepare("SELECT 1 FROM Enfant_Creneau WHERE id_enfant = ? AND id_creneau = ?");
            $dejaConf->execute([$id_enfant, $id_creneau]);

            $dejaAtt = $db->prepare("SELECT position FROM ListeAttente WHERE id_enfant = ? AND id_creneau = ?");
            $dejaAtt->execute([$id_enfant, $id_creneau]);
            $ligneAtt = $dejaAtt->fetch();

            if ($dejaConf->fetch()) {
                $message = 'Cet enfant est déjà inscrit à ce créneau.';
                $messageType = 'error';
            } elseif ($ligneAtt) {
                $message = "⏳ Cet enfant est déjà en liste d'attente (position #{$ligneAtt['position']}).";
                $messageType = 'info';
            } else {
                $nb  = nbInscrits($db, $id_creneau);
                $cap = capaciteCreneau($db, $id_creneau);

                if ($nb < $cap) {
                    $db->prepare("INSERT INTO Enfant_Creneau (id_enfant, id_creneau) VALUES (?, ?)")
                       ->execute([$id_enfant, $id_creneau]);
                    $message = '✔ Inscription confirmée !';
                    $messageType = 'success';
                } else {
                    $pos = prochainePosition($db, $id_creneau);
                    $db->prepare("INSERT INTO ListeAttente (id_enfant, id_creneau, position) VALUES (?, ?, ?)")
                       ->execute([$id_enfant, $id_creneau, $pos]);

                    $alts    = creneauxAlternatifs($db, $id_creneau);
                    $msgAlts = '';
                    if (!empty($alts)) {
                        $msgAlts = '<br><strong>Créneaux avec des places disponibles :</strong><ul style="margin:6px 0 0 15px;">';
                        foreach ($alts as $a) {
                            $dispo    = $a['capacite'] - $a['nb_ins'];
                            $msgAlts .= '<li>' . htmlspecialchars(date('d/m/Y', strtotime($a['date'])))
                                . ' · ' . substr($a['debut'], 0, 5) . ' – ' . substr($a['fin'], 0, 5)
                                . ' · <strong>' . $dispo . ' place' . ($dispo > 1 ? 's' : '') . ' disponible' . ($dispo > 1 ? 's' : '') . '</strong>'
                                . ' — <a href="activites.php" style="color:#fff;text-decoration:underline;">voir</a></li>';
                        }
                        $msgAlts .= '</ul>';
                    }
                    $message = "⏳ Créneau complet — votre enfant est en liste d'attente (position #$pos).$msgAlts";
                    $messageType = 'info';
                }
            }
        }
    }
    $enfantsStmt->execute([$login]);
    $enfants = $enfantsStmt->fetchAll();
}

// ── DÉSINSCRIPTION ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'desinscrire') {
    $id_enfant  = intval($_POST['id_enfant']  ?? 0);
    $id_creneau = intval($_POST['id_creneau'] ?? 0);

    $chk = $db->prepare("SELECT id FROM Enfant WHERE id = ? AND login_famille = ?");
    $chk->execute([$id_enfant, $login]);

    if ($chk->fetch()) {
        $db->prepare("DELETE FROM Enfant_Creneau WHERE id_enfant = ? AND id_creneau = ?")
           ->execute([$id_enfant, $id_creneau]);

        // Promouvoir le premier de la liste d'attente
        $premier = $db->prepare(
            "SELECT id_enfant FROM ListeAttente WHERE id_creneau = ? ORDER BY position ASC LIMIT 1"
        );
        $premier->execute([$id_creneau]);
        $promo = $premier->fetchColumn();

        if ($promo) {
            $db->prepare("INSERT INTO Enfant_Creneau (id_enfant, id_creneau) VALUES (?, ?)")
               ->execute([$promo, $id_creneau]);
            $db->prepare("DELETE FROM ListeAttente WHERE id_enfant = ? AND id_creneau = ?")
               ->execute([$promo, $id_creneau]);

            $restants = $db->prepare(
                "SELECT id FROM ListeAttente WHERE id_creneau = ? ORDER BY position ASC"
            );
            $restants->execute([$id_creneau]);
            $pos = 1;
            foreach ($restants->fetchAll() as $r) {
                $db->prepare("UPDATE ListeAttente SET position = ? WHERE id = ?")->execute([$pos++, $r['id']]);
            }
        }
        $message = '✔ Désinscription effectuée.';
        $messageType = 'success';
    }
}

// ── QUITTER LISTE D'ATTENTE ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'quitter_attente') {
    $id_enfant  = intval($_POST['id_enfant']  ?? 0);
    $id_creneau = intval($_POST['id_creneau'] ?? 0);

    $chk = $db->prepare("SELECT id FROM Enfant WHERE id = ? AND login_famille = ?");
    $chk->execute([$id_enfant, $login]);

    if ($chk->fetch()) {
        $db->prepare("DELETE FROM ListeAttente WHERE id_enfant = ? AND id_creneau = ?")
           ->execute([$id_enfant, $id_creneau]);

        $restants = $db->prepare(
            "SELECT id FROM ListeAttente WHERE id_creneau = ? ORDER BY position ASC"
        );
        $restants->execute([$id_creneau]);
        $pos = 1;
        foreach ($restants->fetchAll() as $r) {
            $db->prepare("UPDATE ListeAttente SET position = ? WHERE id = ?")->execute([$pos++, $r['id']]);
        }
        $message = '✔ Retiré de la liste d\'attente.';
        $messageType = 'success';
    }
}

// ── CHARGEMENT DES DONNÉES ──────────────────────────────────
$activites = $db->query("SELECT * FROM Activité ORDER BY nom")->fetchAll();

$allCreneaux = $db->query("
    SELECT c.*,
           s.batiment, s.id AS salle_id,
           COUNT(DISTINCT ec.id_enfant) AS nb_inscrits,
           COUNT(DISTINCT la.id_enfant) AS nb_attente,
           a.capacite                   AS cap_activite
    FROM Creneau c
    LEFT JOIN Salle s           ON s.id = c.id_salle
    LEFT JOIN Activité a        ON a.nom = c.nom_activite
    LEFT JOIN Enfant_Creneau ec ON ec.id_creneau = c.id
    LEFT JOIN ListeAttente la   ON la.id_creneau = c.id
    GROUP BY c.id
    ORDER BY c.date, c.debut
")->fetchAll();

$creneauxByActivite = [];
foreach ($allCreneaux as $cr) {
    $creneauxByActivite[$cr['nom_activite']][] = $cr;
}

// Inscriptions confirmées et attentes de cette famille
$mesInscriptions = [];
$mesAttentes     = [];

if (!empty($enfants)) {
    $ids      = implode(',', array_map('intval', array_column($enfants, 'id')));
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

// Correspondance activité → image
$imgMap = [
    'Arts'    => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=400',
    'Jeux'    => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=400',
    'Musique' => 'https://images.unsplash.com/photo-1511379938547-c1f69419868d?w=400',
    'Lecture' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=400',
    'Cuisine' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400',
];

function getImg(string $nom, array $map): string {
    foreach ($map as $k => $v) {
        if (stripos($nom, $k) !== false) {
            return $v;
        }
    }
    return 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=400';
}
