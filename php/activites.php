<?php
require_once __DIR__ . '/config.php';
requireParent();

$db      = getDB();
$login   = $_SESSION['user'];
$message = '';
$messageType = '';

// Charger les enfants de la famille
$enfantsStmt = $db->prepare("SELECT id, nom, prenom, age FROM Enfant WHERE login_famille = ? ORDER BY prenom");
$enfantsStmt->execute([$login]);
$enfants = $enfantsStmt->fetchAll();

var_dump($_POST);

// ── INSCRIPTION ─────────────────────────────────────────────
// ── DÉSINSCRIPTION ──────────────────────────────────────────
// ── QUITTER LISTE D'ATTENTE ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? null;
    $id_creneau = intval($_POST['id_creneau'] ?? 0);
    $id_enfants = $_POST['id_enfants'] ?? [];

    if (empty($id_enfants) || !$id_creneau) {
        $message = 'Veuillez sélectionner au moins un enfant.';
        $messageType = 'error';
    } else {

        // 🔐 filtrare copii aparținând familiei
        $ids = array_map('intval', $id_enfants);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $db->prepare("
            SELECT id FROM Enfant 
            WHERE id IN ($placeholders) 
            AND login_famille = ?
        ");
        $stmt->execute([...$ids, $login]);
        $enfants_valides = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($enfants_valides)) {
            $message = 'Aucun enfant valide.';
            $messageType = 'error';
        } else {

            switch ($action) {

                case 'inscrire':

                    foreach ($enfants_valides as $id_enfant) {

                        $dejaConf = $db->prepare("SELECT 1 FROM Enfant_Creneau WHERE id_enfant = ? AND id_creneau = ?");
                        $dejaConf->execute([$id_enfant, $id_creneau]);

                        $dejaAtt = $db->prepare("SELECT 1 FROM ListeAttente WHERE id_enfant = ? AND id_creneau = ?");
                        $dejaAtt->execute([$id_enfant, $id_creneau]);

                        if ($dejaConf->fetch() || $dejaAtt->fetch()) continue;

                        $nb  = nbInscrits($db, $id_creneau);
                        $cap = capaciteCreneau($db, $id_creneau);

                        if ($nb < $cap) {
                            $db->prepare("INSERT INTO Enfant_Creneau (id_enfant, id_creneau) VALUES (?, ?)")
                               ->execute([$id_enfant, $id_creneau]);
                        } else {
                            $pos = prochainePosition($db, $id_creneau);
                            $db->prepare("INSERT INTO ListeAttente (id_enfant, id_creneau, position) VALUES (?, ?, ?)")
                               ->execute([$id_enfant, $id_creneau, $pos]);
                        }
                    }

                    $message = 'Inscription traitée.';
                    $messageType = 'success';
                break;

                case 'desinscrire':

                    foreach ($enfants_valides as $id_enfant) {
                        $db->prepare("DELETE FROM Enfant_Creneau WHERE id_enfant = ? AND id_creneau = ?")
                           ->execute([$id_enfant, $id_creneau]);
                    }

                    // promotion
                    while (nbInscrits($db, $id_creneau) < capaciteCreneau($db, $id_creneau)) {

                        $stmt = $db->prepare("SELECT id_enfant FROM ListeAttente WHERE id_creneau = ? ORDER BY position ASC LIMIT 1");
                        $stmt->execute([$id_creneau]);

                        $promo = $stmt->fetchColumn();
                        if (!$promo) break;

                        $db->prepare("DELETE FROM ListeAttente WHERE id_enfant = ? AND id_creneau = ?")
                           ->execute([$promo, $id_creneau]);

                        $db->prepare("INSERT INTO Enfant_Creneau (id_enfant, id_creneau) VALUES (?, ?)")
                           ->execute([$promo, $id_creneau]);
                    }

                    $message = 'Désinscription effectuée.';
                    $messageType = 'success';
                break;

                case 'quitter_attente':

                    foreach ($enfants_valides as $id_enfant) {
                        $db->prepare("DELETE FROM ListeAttente WHERE id_enfant = ? AND id_creneau = ?")
                           ->execute([$id_enfant, $id_creneau]);
                    }

                    $message = 'Retiré de la liste d’attente.';
                    $messageType = 'success';
                break;
            }
        }
    }

    // refresh enfants
    $enfantsStmt->execute([$login]);
    $enfants = $enfantsStmt->fetchAll();
}

// FIN INSCRIPTION, DESINSCRIPTION, QUITTER

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

// Index enfant → age (pour le moteur de recommandation)
$enfantsById = [];
foreach ($enfants as $enf) {
    $enfantsById[$enf['id']] = $enf;
}

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

// ── PRÉ-CALCUL DES RECOMMANDATIONS ─────────────────────────
// Pour chaque créneau complet, on précalcule les recommandations
// en tenant compte de l'âge du premier enfant sélectionnable
// (côté JS, on rafraîchira les suggestions selon l'enfant choisi)
$recommendationsData = [];
foreach ($allCreneaux as $cr) {
    if ((int)$cr['nb_inscrits'] >= (int)$cr['cap_activite']) {
        // Recommandations génériques (sans âge)
        $recs = creneauxRecommandes($db, (int)$cr['id'], 0);
        if (!empty($recs)) {
            $recommendationsData[$cr['id']] = $recs;
        }
    }
}

// Correspondance activité → image
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