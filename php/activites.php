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
 
// ── INSCRIPTION (multi-enfants) ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'inscrire') {
    $id_creneau  = intval($_POST['id_creneau'] ?? 0);
    $ids_enfants = array_map('intval', (array)($_POST['id_enfants'] ?? []));
    // Filtrer les zéros
    $ids_enfants = array_filter($ids_enfants);
 
    if (empty($ids_enfants) || !$id_creneau) {
        $message     = 'Veuillez sélectionner au moins un enfant et un créneau.';
        $messageType = 'error';
    } else {
        $messages = [];
 
        foreach ($ids_enfants as $id_enfant) {
            // Vérifier que l'enfant appartient bien à cette famille
            $chk = $db->prepare("SELECT id, prenom FROM Enfant WHERE id = ? AND login_famille = ?");
            $chk->execute([$id_enfant, $login]);
            $enfantRow = $chk->fetch();
 
            if (!$enfantRow) {
                $messages[] = "Enfant #$id_enfant introuvable.";
                continue;
            }
 
            $prenom = htmlspecialchars($enfantRow['prenom']);
 
            $dejaConf = $db->prepare("SELECT 1 FROM Enfant_Creneau WHERE id_enfant = ? AND id_creneau = ?");
            $dejaConf->execute([$id_enfant, $id_creneau]);
 
            $dejaAtt = $db->prepare("SELECT position FROM ListeAttente WHERE id_enfant = ? AND id_creneau = ?");
            $dejaAtt->execute([$id_enfant, $id_creneau]);
            $ligneAtt = $dejaAtt->fetch();
 
            if ($dejaConf->fetch()) {
                $messages[] = "⚠ $prenom est déjà inscrit(e) à ce créneau.";
            } elseif ($ligneAtt) {
                $messages[] = "ℹ $prenom est déjà en liste d'attente (position #{$ligneAtt['position']}).";
            } else {
                $nb  = nbInscrits($db, $id_creneau);
                $cap = capaciteCreneau($db, $id_creneau);
 
                if ($nb < $cap) {
                    $db->prepare("INSERT INTO Enfant_Creneau (id_enfant, id_creneau) VALUES (?, ?)")
                       ->execute([$id_enfant, $id_creneau]);
                    $messages[] = "✔ $prenom : inscription confirmée !";
                } else {
                    $pos = prochainePosition($db, $id_creneau);
                    $db->prepare("INSERT INTO ListeAttente (id_enfant, id_creneau, position) VALUES (?, ?, ?)")
                       ->execute([$id_enfant, $id_creneau, $pos]);
                    $messages[] = "ℹ $prenom : créneau complet — liste d'attente (position #$pos).";
                }
            }
        }
 
        $message     = implode('<br>', $messages);
        $messageType = 'info'; // le détail est dans chaque ligne
    }
 
    $enfantsStmt->execute([$login]);
    $enfants = $enfantsStmt->fetchAll();
}
 
// ── DÉSINSCRIPTION (multi-enfants) ──────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'desinscrire') {
    $id_creneau  = intval($_POST['id_creneau'] ?? 0);
    $ids_enfants = array_map('intval', (array)($_POST['id_enfants'] ?? []));
    $ids_enfants = array_filter($ids_enfants);
 
    if (empty($ids_enfants) || !$id_creneau) {
        $message     = 'Veuillez sélectionner au moins un enfant.';
        $messageType = 'error';
    } else {
        $messages = [];
 
        foreach ($ids_enfants as $id_enfant) {
            $chk = $db->prepare("SELECT id, prenom FROM Enfant WHERE id = ? AND login_famille = ?");
            $chk->execute([$id_enfant, $login]);
            $enfantRow = $chk->fetch();
 
            if (!$enfantRow) continue;
 
            $prenom = htmlspecialchars($enfantRow['prenom']);
 
            $db->prepare("DELETE FROM Enfant_Creneau WHERE id_enfant = ? AND id_creneau = ?")
               ->execute([$id_enfant, $id_creneau]);
 
            // Promouvoir le premier de la liste d'attente
            $premier = $db->prepare("SELECT id_enfant FROM ListeAttente WHERE id_creneau = ? ORDER BY position ASC LIMIT 1");
            $premier->execute([$id_creneau]);
            $promo = $premier->fetchColumn();
 
            if ($promo) {
                $db->prepare("INSERT INTO Enfant_Creneau (id_enfant, id_creneau) VALUES (?, ?)")
                   ->execute([$promo, $id_creneau]);
                $db->prepare("DELETE FROM ListeAttente WHERE id_enfant = ? AND id_creneau = ?")
                   ->execute([$promo, $id_creneau]);
 
                // Renuméroter la liste d'attente
                $restants = $db->prepare("SELECT id FROM ListeAttente WHERE id_creneau = ? ORDER BY position ASC");
                $restants->execute([$id_creneau]);
                $pos = 1;
                foreach ($restants->fetchAll() as $r) {
                    $db->prepare("UPDATE ListeAttente SET position = ? WHERE id = ?")->execute([$pos++, $r['id']]);
                }
            }
 
            $messages[] = "✔ $prenom : désinscription effectuée.";
        }
 
        $message     = implode('<br>', $messages);
        $messageType = 'success';
    }
}
 
// ── QUITTER LISTE D'ATTENTE (multi-enfants) ─────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'quitter_attente') {
    $id_creneau  = intval($_POST['id_creneau'] ?? 0);
    $ids_enfants = array_map('intval', (array)($_POST['id_enfants'] ?? []));
    $ids_enfants = array_filter($ids_enfants);
 
    if (empty($ids_enfants) || !$id_creneau) {
        $message     = 'Veuillez sélectionner au moins un enfant.';
        $messageType = 'error';
    } else {
        $messages = [];
 
        foreach ($ids_enfants as $id_enfant) {
            $chk = $db->prepare("SELECT id, prenom FROM Enfant WHERE id = ? AND login_famille = ?");
            $chk->execute([$id_enfant, $login]);
            $enfantRow = $chk->fetch();
 
            if (!$enfantRow) continue;
 
            $prenom = htmlspecialchars($enfantRow['prenom']);
 
            $db->prepare("DELETE FROM ListeAttente WHERE id_enfant = ? AND id_creneau = ?")
               ->execute([$id_enfant, $id_creneau]);
 
            // Renuméroter la liste d'attente
            $restants = $db->prepare("SELECT id FROM ListeAttente WHERE id_creneau = ? ORDER BY position ASC");
            $restants->execute([$id_creneau]);
            $pos = 1;
            foreach ($restants->fetchAll() as $r) {
                $db->prepare("UPDATE ListeAttente SET position = ? WHERE id = ?")->execute([$pos++, $r['id']]);
            }
 
            $messages[] = "✔ $prenom : retiré(e) de la liste d'attente.";
        }
 
        $message     = implode('<br>', $messages);
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
 
// Index enfant → données complètes (pour le moteur de recommandation)
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
$recommendationsData = [];
foreach ($allCreneaux as $cr) {
    if ((int)$cr['nb_inscrits'] >= (int)$cr['cap_activite']) {
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