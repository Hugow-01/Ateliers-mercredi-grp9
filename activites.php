<?php
require_once 'config.php';
requireParent();

$db    = getDB();
$login = $_SESSION['user'];
$message = ''; $messageType = '';

$enfantsStmt = $db->prepare("SELECT id, nom, prenom FROM Enfant WHERE login_famille=? ORDER BY prenom");
$enfantsStmt->execute([$login]);
$enfants = $enfantsStmt->fetchAll();

// ════════════════════════════════════════════════════════════
//  HELPERS
// ════════════════════════════════════════════════════════════

/**
 * Retourne le nombre de places confirmées pour un créneau.
 */
function nbInscrits(PDO $db, int $id_creneau): int {
    $s = $db->prepare("SELECT COUNT(*) FROM Enfant_Creneau WHERE id_creneau=?");
    $s->execute([$id_creneau]);
    return (int)$s->fetchColumn();
}

/**
 * Retourne la capacité de l'activité liée au créneau.
 */
function capaciteCreneau(PDO $db, int $id_creneau): int {
    $s = $db->prepare(
        "SELECT a.capacite FROM Creneau c
         JOIN Activité a ON a.nom = c.nom_activite
         WHERE c.id = ?"
    );
    $s->execute([$id_creneau]);
    return (int)$s->fetchColumn();
}

/**
 * Retourne la prochaine position dans la file d'attente du créneau.
 */
function prochainePosition(PDO $db, int $id_creneau): int {
    $s = $db->prepare("SELECT COALESCE(MAX(position),0)+1 FROM ListeAttente WHERE id_creneau=?");
    $s->execute([$id_creneau]);
    return (int)$s->fetchColumn();
}

/**
 * Cherche des créneaux alternatifs : même activité, même date si possible,
 * avec au moins une place disponible.
 */
function creneauxAlternatifs(PDO $db, int $id_creneau_plein): array {
    $s = $db->prepare(
        "SELECT c.id, c.date, c.debut, c.fin, c.id_salle,
                COUNT(ec.id_enfant) AS nb_ins, a.capacite
         FROM Creneau c
         JOIN Activité a ON a.nom = c.nom_activite
         LEFT JOIN Enfant_Creneau ec ON ec.id_creneau = c.id
         WHERE c.nom_activite = (
             SELECT nom_activite FROM Creneau WHERE id = ?
         )
         AND c.id <> ?
         GROUP BY c.id
         HAVING nb_ins < a.capacite
         ORDER BY ABS(DATEDIFF(c.date,(SELECT date FROM Creneau WHERE id=?))), c.date
         LIMIT 3"
    );
    $s->execute([$id_creneau_plein, $id_creneau_plein, $id_creneau_plein]);
    return $s->fetchAll();
}

// ════════════════════════════════════════════════════════════
//  INSCRIPTION
// ════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='inscrire') {
    $id_enfant  = intval($_POST['id_enfant']??0);
    $id_creneau = intval($_POST['id_creneau']??0);

    if (!$id_enfant || !$id_creneau) {
        $message = 'Veuillez sélectionner un enfant et un créneau.';
        $messageType = 'error';
    } else {
        // Vérifier que l'enfant appartient bien à cette famille
        $chk = $db->prepare("SELECT id FROM Enfant WHERE id=? AND login_famille=?");
        $chk->execute([$id_enfant, $login]);
        if (!$chk->fetch()) {
            $message = 'Enfant non trouvé.'; $messageType = 'error';
        } else {
            // Déjà inscrit (confirmé) ?
            $dejaConf = $db->prepare("SELECT 1 FROM Enfant_Creneau WHERE id_enfant=? AND id_creneau=?");
            $dejaConf->execute([$id_enfant, $id_creneau]);

            // Déjà en attente ?
            $dejaAtt = $db->prepare("SELECT position FROM ListeAttente WHERE id_enfant=? AND id_creneau=?");
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
                    // Place disponible → inscription confirmée
                    $db->prepare("INSERT INTO Enfant_Creneau (id_enfant,id_creneau) VALUES (?,?)")
                       ->execute([$id_enfant, $id_creneau]);
                    $message = '✔ Inscription confirmée !';
                    $messageType = 'success';
                } else {
                    // Créneau plein → liste d'attente
                    $pos = prochainePosition($db, $id_creneau);
                    $db->prepare("INSERT INTO ListeAttente (id_enfant,id_creneau,position) VALUES (?,?,?)")
                       ->execute([$id_enfant, $id_creneau, $pos]);

                    $alts = creneauxAlternatifs($db, $id_creneau);
                    $msgAlts = '';
                    if (!empty($alts)) {
                        $msgAlts = '<br><strong>Créneaux avec des places disponibles :</strong><ul style="margin:6px 0 0 15px;">';
                        foreach ($alts as $a) {
                            $dispo = $a['capacite'] - $a['nb_ins'];
                            $msgAlts .= '<li>'.htmlspecialchars(date('d/m/Y', strtotime($a['date'])))
                                .' · '.substr($a['debut'],0,5).' – '.substr($a['fin'],0,5)
                                .' · <strong>'.$dispo.' place'.($dispo>1?'s':'').' disponible'.($dispo>1?'s':'').'</strong>'
                                .' — <a href="activites.php" style="color:#fff;text-decoration:underline;">voir</a></li>';
                        }
                        $msgAlts .= '</ul>';
                    }
                    $message = "⏳ Créneau complet — votre enfant est en liste d'attente (position #$pos).$msgAlts";
                    $messageType = 'info';
                }
            }
        }
    }
    // Recharger les enfants après action
    $enfantsStmt->execute([$login]);
    $enfants = $enfantsStmt->fetchAll();
}

// ════════════════════════════════════════════════════════════
//  DÉSINSCRIPTION (confirmée)
// ════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='desinscrire') {
    $id_enfant  = intval($_POST['id_enfant']??0);
    $id_creneau = intval($_POST['id_creneau']??0);
    $chk = $db->prepare("SELECT id FROM Enfant WHERE id=? AND login_famille=?");
    $chk->execute([$id_enfant, $login]);
    if ($chk->fetch()) {
        $db->prepare("DELETE FROM Enfant_Creneau WHERE id_enfant=? AND id_creneau=?")
           ->execute([$id_enfant, $id_creneau]);

        // Promouvoir le premier de la liste d'attente
        $premier = $db->prepare(
            "SELECT id_enfant FROM ListeAttente WHERE id_creneau=? ORDER BY position ASC LIMIT 1"
        );
        $premier->execute([$id_creneau]);
        $promo = $premier->fetchColumn();
        if ($promo) {
            $db->prepare("INSERT INTO Enfant_Creneau (id_enfant,id_creneau) VALUES (?,?)")
               ->execute([$promo, $id_creneau]);
            $db->prepare("DELETE FROM ListeAttente WHERE id_enfant=? AND id_creneau=?")
               ->execute([$promo, $id_creneau]);
            // Renuméroter la file
            $restants = $db->prepare(
                "SELECT id FROM ListeAttente WHERE id_creneau=? ORDER BY position ASC"
            );
            $restants->execute([$id_creneau]);
            $pos = 1;
            foreach ($restants->fetchAll() as $r) {
                $db->prepare("UPDATE ListeAttente SET position=? WHERE id=?")->execute([$pos++, $r['id']]);
            }
        }
        $message = '✔ Désinscription effectuée.'; $messageType = 'success';
    }
}

// ════════════════════════════════════════════════════════════
//  RETRAIT DE LA LISTE D'ATTENTE
// ════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='quitter_attente') {
    $id_enfant  = intval($_POST['id_enfant']??0);
    $id_creneau = intval($_POST['id_creneau']??0);
    $chk = $db->prepare("SELECT id FROM Enfant WHERE id=? AND login_famille=?");
    $chk->execute([$id_enfant, $login]);
    if ($chk->fetch()) {
        $db->prepare("DELETE FROM ListeAttente WHERE id_enfant=? AND id_creneau=?")
           ->execute([$id_enfant, $id_creneau]);
        // Renuméroter
        $restants = $db->prepare(
            "SELECT id FROM ListeAttente WHERE id_creneau=? ORDER BY position ASC"
        );
        $restants->execute([$id_creneau]);
        $pos = 1;
        foreach ($restants->fetchAll() as $r) {
            $db->prepare("UPDATE ListeAttente SET position=? WHERE id=?")->execute([$pos++, $r['id']]);
        }
        $message = '✔ Retiré de la liste d\'attente.'; $messageType = 'success';
    }
}

// ════════════════════════════════════════════════════════════
//  CHARGEMENT DES DONNÉES
// ════════════════════════════════════════════════════════════
$activites = $db->query("SELECT * FROM Activité ORDER BY nom")->fetchAll();

$allCreneaux = $db->query("
    SELECT c.*,
           s.batiment, s.id AS salle_id,
           COUNT(DISTINCT ec.id_enfant)  AS nb_inscrits,
           COUNT(DISTINCT la.id_enfant)  AS nb_attente,
           a.capacite                    AS cap_activite
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

// Inscriptions confirmées de cette famille
$mesInscriptions = [];
// Positions liste d'attente de cette famille
$mesAttentes = []; // [id_enfant][id_creneau] => position

if (!empty($enfants)) {
    $ids = implode(',', array_map('intval', array_column($enfants, 'id')));
    $rowsConf = $db->query(
        "SELECT id_enfant, id_creneau FROM Enfant_Creneau WHERE id_enfant IN ($ids)"
    )->fetchAll();
    foreach ($rowsConf as $r) {
        $mesInscriptions[$r['id_enfant']][] = (int)$r['id_creneau'];
    }
    $rowsAtt = $db->query(
        "SELECT id_enfant, id_creneau, position FROM ListeAttente WHERE id_enfant IN ($ids)"
    )->fetchAll();
    foreach ($rowsAtt as $r) {
        $mesAttentes[$r['id_enfant']][$r['id_creneau']] = (int)$r['position'];
    }
}

$imgMap = [
    'Arts'    => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=400',
    'Jeux'    => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=400',
    'Musique' => 'https://images.unsplash.com/photo-1511379938547-c1f69419868d?w=400',
    'Lecture' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=400',
    'Cuisine' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400',
];
function getImg($nom, $map) {
    foreach ($map as $k => $v) { if (stripos($nom, $k) !== false) return $v; }
    return 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=400';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Nos Activités</title>
<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
<script src="script.js" defer></script>
<style>
/* ── Légende & statuts créneaux ── */
.legend { display:flex; gap:20px; flex-wrap:wrap; align-items:center; font-size:.8rem; margin-bottom:14px; color:#333; }
.legend-dot { width:11px; height:11px; border-radius:3px; display:inline-block; margin-right:6px; }
.dot-ok   { background:#1a1a2e; }
.dot-wait { background:#4a4a6a; }
.dot-full { background:#888; }

/* ── Slot items ── */
.slot-item {
    border: 2px solid #d1d5db;
    border-radius: 10px;
    padding: 9px 12px;
    margin-bottom: 7px;
    cursor: pointer;
    transition: all .15s;
    position: relative;
    background: #fff;
    color: #111;
}
.slot-item.ok   { border-color:#1a1a2e; background:#f5f5f8; }
.slot-item.ok:hover  { background:#e8e8f0; transform:translateX(3px); }
.slot-item.wait { border-color:#4a4a6a; background:#f0f0f5; }
.slot-item.wait:hover { background:#e4e4ee; transform:translateX(3px); }
.slot-item.full { border-color:#bbb; background:#f7f7f7; cursor:not-allowed; opacity:.7; }
.slot-item.inscrit    { border-color:#1a1a2e; border-width:3px; background:#f5f5f8; color:#111; }
.slot-item.en-attente { border-color:#4a4a6a; border-width:3px; background:#f0f0f5; color:#111; }
.slot-item.selected   { border-color:#1a1a2e; border-width:3px; background:#fff; color:#111; box-shadow:0 2px 8px rgba(0,0,0,0.15); }

.badge {
    display:inline-block; border-radius:5px; font-size:.7rem;
    font-weight:700; padding:2px 8px; margin-left:6px; vertical-align:middle;
}
.badge-ok   { background:#1a1a2e; color:#fff; }
.badge-wait { background:#4a4a6a; color:#fff; }
.badge-full { background:#888;    color:#fff; }
.badge-conf { background:#111;    color:#fff; }
.badge-att  { background:#555;    color:#fff; }

/* ── Boutons d'action ── */
.btn-inscr  { background:#1a1a2e; color:#fff; border:none; border-radius:10px; padding:10px 20px; font-weight:700; cursor:pointer; margin-top:8px; width:100%; font-size:.9rem; letter-spacing:.3px; }
.btn-inscr:hover  { background:#111; }
.btn-wait   { background:#4a4a6a; color:#fff; border:none; border-radius:10px; padding:10px 20px; font-weight:700; cursor:pointer; margin-top:4px; width:100%; font-size:.9rem; }
.btn-wait:hover   { background:#333355; }
.btn-desins { background:#333; color:#fff; border:none; border-radius:10px; padding:8px 20px; font-weight:700; cursor:pointer; margin-top:4px; width:100%; display:none; font-size:.88rem; }
.btn-desins:hover { background:#111; }
.btn-quitter { background:#555; color:#fff; border:none; border-radius:10px; padding:8px 20px; font-weight:700; cursor:pointer; margin-top:4px; width:100%; display:none; font-size:.88rem; }
.btn-quitter:hover { background:#333; }

/* ── Alternatives ── */
.alt-box { background:#f5f5f8; border:1px solid #c0c0d0; border-radius:10px; padding:10px 14px; margin-top:10px; font-size:.82rem; display:none; color:#111; }
.alt-box h5 { margin:0 0 6px; color:#1a1a2e; font-weight:700; }
.alt-item { padding:4px 0; border-bottom:1px solid #d0d0e0; color:#222; }
.alt-item:last-child { border:none; }

/* ── Info attente ── */
.attente-info { background:#f0f0f5; border:1px solid #aaaacc; border-radius:10px; padding:8px 12px; margin-top:8px; font-size:.82rem; color:#1a1a2e; font-weight:600; display:none; }

/* ── Calendrier ── */
.cal-box { background:#f5f5f8; border-radius:14px; padding:12px; border:1px solid #ddd; }
.cal-nav { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; }
.cal-nav button { background:#1a1a2e; color:#fff; border:none; border-radius:7px; padding:4px 11px; cursor:pointer; font-size:.95rem; font-weight:bold; }
.cal-nav button:hover { background:#111; }
.cal-month-label { font-weight:800; font-size:.95rem; font-family:'Baloo 2'; color:#1a1a2e; }
.cal-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:3px; }
.dj { text-align:center; padding:5px 2px; border-radius:6px; font-size:.78rem; color:#333; }
.dl { font-weight:700; font-size:.68rem; color:#777; text-align:center; padding:3px 0; }
.dj.empty { color:#ccc; }
.dj.has-slot { cursor:pointer; font-weight:bold; }

/* Couleurs calendrier sobres */
.dj.day-ok   { background:#1a1a2e; color:#fff; }
.dj.day-ok:hover { background:#111; }
.dj.day-wait { background:#4a4a6a; color:#fff; }
.dj.day-wait:hover { background:#333355; }
.dj.day-full { background:#aaa;    color:#fff; }
.dj.day-full:hover { background:#888; }
.dj.active   { outline:2px solid #000; outline-offset:1px; }

/* ── Grille activité ── */
.act-grid { display:grid; grid-template-columns:160px 1fr 1fr 1fr; gap:18px; padding:20px; align-items:start; }
@media(max-width:900px){ .act-grid{ grid-template-columns:1fr 1fr; } }
@media(max-width:600px){ .act-grid{ grid-template-columns:1fr; } }
.activity-img { width:100%; border-radius:12px; object-fit:cover; height:130px; }
.inscr-box { display:flex; flex-direction:column; }
.slot-list { max-height:220px; overflow-y:auto; margin-bottom:6px; }
.inscr-box select { width:100%; padding:7px; border-radius:8px; border:1px solid #bbb; margin-top:6px; font-size:.9rem; background:#fff; color:#111; }
</style>
</head>

<body>
<header style="background:#fdf6d8;padding:12px 50px;display:flex;justify-content:space-between;align-items:center;">
    <h1 style="font-size:2rem;font-weight:900;margin:0;">Les activités</h1>
    <nav>
        <a href="index.php">Accueil</a>
        <a href="parent-enfants.php">Mon espace</a>
        <a href="deconnexion.php" style="color:#c0392b;">se déconnecter</a>
    </nav>
</header>

<div style="text-align:center;padding:35px 20px 10px;">
    <h1 style="color:#1a5fb4;font-size:2.8rem;font-weight:900;font-family:'Baloo 2';">NOS ACTIVITÉS DU MERCREDI</h1>
    <p style="color:#d4ac0d;font-style:italic;font-size:1.3rem;margin-top:8px;">crée, explore, imagine, chaque semaine</p>
</div>

<?php if ($message): ?>
<div style="max-width:780px;margin:12px auto;">
    <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
</div>
<?php endif; ?>

<?php if (empty($enfants)): ?>
<div style="text-align:center;background:#fff3cd;border:1px solid #ffc107;padding:15px;max-width:600px;margin:15px auto;border-radius:10px;">
    ⚠️ Aucun enfant enregistré. <a href="ajouter-enfant.php" style="color:#ff5e78;font-weight:bold;">Ajouter un enfant</a>.
</div>
<?php endif; ?>

<!-- Légende -->
<div class="legend" style="max-width:1200px;margin:0 auto 4px;padding:0 20px;">
    <span><span class="legend-dot dot-ok"></span> Place disponible</span>
    <span><span class="legend-dot dot-wait"></span> Presque complet</span>
    <span><span class="legend-dot dot-full" style="background:#aaa;"></span> Complet — liste d'attente possible</span>
</div>

<div class="search-container" style="max-width:1200px;margin:0 auto;padding:0 20px 10px;">
    <input type="text" id="searchBar" class="search-bar" placeholder="🔍  rechercher une activité..." style="width:100%;padding:10px 16px;border-radius:20px;border:1px solid #ddd;">
</div>

<div style="max-width:1200px;margin:0 auto;padding:0 20px 60px;">
<?php foreach ($activites as $idx => $act):
    $creneaux = $creneauxByActivite[$act['nom']] ?? [];
    $byDate   = [];
    foreach ($creneaux as $cr) $byDate[$cr['date']][] = $cr;
    $dates = array_keys($byDate); sort($dates);
    $firstDate = $dates[0] ?? null;
    $initY = $firstDate ? (int)date('Y', strtotime($firstDate)) : (int)date('Y');
    $initM = $firstDate ? (int)date('n', strtotime($firstDate)) : (int)date('n');

    // Calcul couleur par date : ok / wait / full
    $colorByDate = [];
    foreach ($byDate as $date => $crs) {
        $hasOk = false; $hasWait = false; $allFull = true;
        foreach ($crs as $cr) {
            $pct = $cr['cap_activite'] > 0 ? $cr['nb_inscrits'] / $cr['cap_activite'] : 1;
            if ($cr['nb_inscrits'] < $cr['cap_activite']) {
                $allFull = false;
                if ($pct >= 0.8) $hasWait = true;
                else             $hasOk   = true;
            }
        }
        if ($allFull)     $colorByDate[$date] = 'full';
        elseif ($hasWait) $colorByDate[$date] = 'wait';
        else              $colorByDate[$date] = 'ok';
    }
?>
<details class="activity-item" id="act-<?= $idx ?>" style="margin-bottom:18px;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;">
    <summary style="list-style:none;display:flex;align-items:center;gap:12px;padding:16px 22px;background:#fafafa;cursor:pointer;">
        <span class="arrow" style="font-size:1rem;">▶</span>
        <div>
            <h2 style="margin:0;font-family:'Baloo 2';font-size:1.4rem;"><?= htmlspecialchars($act['nom']) ?></h2>
            <div style="font-size:.82rem;color:#888;">Capacité : <?= $act['capacite'] ?> places · <?= count($creneaux) ?> créneau(x)</div>
        </div>
    </summary>

    <div class="act-grid">
        <!-- Image -->
        <img src="<?= getImg($act['nom'], $imgMap) ?>" class="activity-img" alt="<?= htmlspecialchars($act['nom']) ?>">

        <!-- Description -->
        <div>
            <h3 style="margin-top:0;font-size:1.15rem;"><?= htmlspecialchars($act['nom']) ?></h3>
            <p style="color:#555;font-size:.88rem;line-height:1.6;"><?= nl2br(htmlspecialchars($act['syllabus'])) ?></p>
            <p style="font-size:.78rem;color:#888;"><strong>Capacité :</strong> <?= $act['capacite'] ?> enfants/créneau</p>
        </div>

        <!-- Calendrier -->
        <div class="cal-box">
            <?php if ($firstDate): ?>
            <div class="cal-nav">
                <button onclick="prevMonth_<?= $idx ?>()">◀</button>
                <div class="cal-month-label" id="cal-label-<?= $idx ?>"></div>
                <button onclick="nextMonth_<?= $idx ?>()">▶</button>
            </div>
            <div class="cal-grid" id="cal-<?= $idx ?>"></div>
            <?php else: ?>
            <div style="text-align:center;color:#aaa;padding:20px;font-size:.85rem;">Aucun créneau planifié</div>
            <?php endif; ?>
        </div>

        <!-- Créneaux + inscription -->
        <div class="inscr-box">
            <div style="font-size:11px;font-weight:bold;color:#555;margin-bottom:3px;">Horaires disponibles :</div>

            <div class="slot-list" id="slots-<?= $idx ?>">
                <div style="text-align:center;color:#aaa;font-size:.8rem;padding:10px;">← Choisissez une date</div>
            </div>

            <!-- Info liste d'attente -->
            <div class="attente-info" id="attente-info-<?= $idx ?>"></div>

            <!-- Alternatives -->
            <div class="alt-box" id="alt-box-<?= $idx ?>">
                <h5>📅 Autres créneaux disponibles pour cette activité :</h5>
                <div id="alt-list-<?= $idx ?>"></div>
            </div>

            <!-- Formulaire inscription -->
            <form method="POST" action="activites.php">
                <input type="hidden" name="action"     value="inscrire">
                <input type="hidden" name="id_creneau" id="creneau-<?= $idx ?>" value="">
                <select name="id_enfant" id="sel-<?= $idx ?>" onchange="onEnfantChange_<?= $idx ?>()">
                    <option value="">-- Choisir un enfant --</option>
                    <?php foreach ($enfants as $enf): ?>
                    <option value="<?= $enf['id'] ?>"><?= htmlspecialchars($enf['prenom'].' '.$enf['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-inscr" id="btn-inscr-<?= $idx ?>">+ Inscrire</button>
                <button type="submit" class="btn-wait"  id="btn-wait-<?= $idx ?>"
                        onclick="document.getElementById('creneau-<?= $idx ?>').value=document.getElementById('creneau-<?= $idx ?>').value">
                    ⏳ Rejoindre la liste d'attente
                </button>
            </form>

            <!-- Désinscription confirmée -->
            <form method="POST" action="activites.php" onsubmit="return confirm('Se désinscrire de ce créneau ?')">
                <input type="hidden" name="action"     value="desinscrire">
                <input type="hidden" name="id_creneau" id="des-creneau-<?= $idx ?>" value="">
                <input type="hidden" name="id_enfant"  id="des-enfant-<?= $idx ?>"  value="">
                <button type="submit" class="btn-desins" id="btn-des-<?= $idx ?>">✕ Se désinscrire</button>
            </form>

            <!-- Quitter liste d'attente -->
            <form method="POST" action="activites.php" onsubmit="return confirm('Quitter la liste d\'attente ?')">
                <input type="hidden" name="action"     value="quitter_attente">
                <input type="hidden" name="id_creneau" id="quit-creneau-<?= $idx ?>" value="">
                <input type="hidden" name="id_enfant"  id="quit-enfant-<?= $idx ?>"  value="">
                <button type="submit" class="btn-quitter" id="btn-quit-<?= $idx ?>">✕ Quitter la liste d'attente</button>
            </form>
        </div><!-- /.inscr-box -->
    </div><!-- /.act-grid -->
</details>

<?php if ($firstDate): ?>
<script>
(function(){
const byDate_<?= $idx ?>   = <?= json_encode($byDate) ?>;
const colorByDate_<?= $idx ?> = <?= json_encode($colorByDate) ?>;
const mesIns_<?= $idx ?>   = <?= json_encode($mesInscriptions) ?>;
const mesAtt_<?= $idx ?>   = <?= json_encode($mesAttentes) ?>;
// Créneaux alternatifs par créneau plein (côté JS, depuis PHP)
const altsData_<?= $idx ?> = <?php
    $altsJs = [];
    foreach ($creneaux as $cr) {
        if ($cr['nb_inscrits'] >= $cr['cap_activite']) {
            $altsJs[$cr['id']] = creneauxAlternatifs($db, $cr['id']);
        }
    }
    echo json_encode($altsJs);
?>;

const moisFR = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
let cy = <?= $initY ?>, cm = <?= $initM ?>;

function pad(n){ return n < 10 ? '0'+n : n; }

function renderCal(){
    document.getElementById('cal-label-<?= $idx ?>').textContent = moisFR[cm]+' '+cy;
    const g = document.getElementById('cal-<?= $idx ?>');
    g.innerHTML = '';
    ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'].forEach(d => {
        const s = document.createElement('span'); s.className='dl'; s.textContent=d; g.appendChild(s);
    });
    const fd  = new Date(cy, cm-1, 1).getDay();
    const dim = new Date(cy, cm, 0).getDate();
    for (let e=0; e<fd; e++){
        const s = document.createElement('span'); s.className='dj empty'; g.appendChild(s);
    }
    for (let d=1; d<=dim; d++){
        const ds  = cy+'-'+pad(cm)+'-'+pad(d);
        const has = !!byDate_<?= $idx ?>[ds];
        const s   = document.createElement('span');
        s.textContent = d;
        if (has){
            const col = colorByDate_<?= $idx ?>[ds] || 'ok';
            s.className = 'dj day-'+col+' clickable';
            s.title = byDate_<?= $idx ?>[ds].length+' créneau(x)';
            s.onclick = () => selectDate(ds, s);
        } else {
            s.className = 'dj empty';
        }
        g.appendChild(s);
    }
}

window.prevMonth_<?= $idx ?> = function(){ cm--; if(cm<1){cm=12;cy--;} renderCal(); };
window.nextMonth_<?= $idx ?> = function(){ cm++; if(cm>12){cm=1;cy++;} renderCal(); };

let lastDate = null;
function selectDate(date, el){
    lastDate = date;
    document.querySelectorAll('#cal-<?= $idx ?> .dj').forEach(d => d.classList.remove('active'));
    el.classList.add('active');
    renderSlots(date);
}

function renderSlots(date){
    const crs     = byDate_<?= $idx ?>[date] || [];
    const list    = document.getElementById('slots-<?= $idx ?>');
    const selEnf  = parseInt(document.getElementById('sel-<?= $idx ?>').value) || 0;
    list.innerHTML = '';
    resetActions();

    if (!crs.length){
        list.innerHTML = '<div style="text-align:center;color:#aaa;font-size:.8rem;padding:10px;">Aucun créneau ce jour</div>';
        return;
    }

    crs.forEach(cr => {
        const nb      = parseInt(cr.nb_inscrits);
        const cap     = parseInt(cr.cap_activite);
        const full    = nb >= cap;
        const pct     = cap > 0 ? nb / cap : 1;
        const quasi   = !full && pct >= 0.8;
        const confirme = selEnf && mesIns_<?= $idx ?>[selEnf] && mesIns_<?= $idx ?>[selEnf].includes(cr.id);
        const enAtt    = selEnf && mesAtt_<?= $idx ?>[selEnf] && mesAtt_<?= $idx ?>[selEnf][cr.id];
        const salle    = cr.salle_id ? ' · <strong>Salle '+cr.salle_id+'</strong>' : '';

        const div = document.createElement('div');
        let cls = 'slot-item ';
        if      (confirme) cls += 'inscrit';
        else if (enAtt)    cls += 'en-attente';
        else if (full)     cls += 'full';
        else if (quasi)    cls += 'wait';
        else               cls += 'ok';

        let badge = '';
        if      (confirme) badge = '<span class="badge badge-conf">✔ Inscrit</span>';
        else if (enAtt)    badge = '<span class="badge badge-att">⏳ File #'+enAtt+'</span>';
        else if (full)     badge = '<span class="badge badge-full">Complet</span>';
        else if (quasi)    badge = '<span class="badge badge-wait">Presque complet</span>';
        else               badge = '<span class="badge badge-ok">Disponible</span>';

        const restantes = Math.max(0, cap - nb);
        div.className = cls;
        div.innerHTML =
            '<strong>'+cr.debut.substring(0,5)+' – '+cr.fin.substring(0,5)+'</strong>'+salle+badge+'<br>'
            +'<small style="opacity:.7">'+nb+'/'+cap+' inscrits'
            +(full ? ' · '+cr.nb_attente+' en attente' : ' · '+restantes+' place'+(restantes>1?'s':'')+' restante'+(restantes>1?'s':''))
            +'</small>';

        div.onclick = () => selectSlot(cr, div, selEnf);
        list.appendChild(div);
    });
}

function selectSlot(cr, el, selEnf){
    document.querySelectorAll('#slots-<?= $idx ?> .slot-item').forEach(s => s.classList.remove('selected'));
    el.classList.add('selected');

    const nb   = parseInt(cr.nb_inscrits);
    const cap  = parseInt(cr.cap_activite);
    const full = nb >= cap;
    const confirme = selEnf && mesIns_<?= $idx ?>[selEnf] && mesIns_<?= $idx ?>[selEnf].includes(cr.id);
    const enAtt    = selEnf && mesAtt_<?= $idx ?>[selEnf] && mesAtt_<?= $idx ?>[selEnf][cr.id];

    resetActions();
    document.getElementById('creneau-<?= $idx ?>').value = cr.id;

    // Bouton inscription ou attente
    const btnI = document.getElementById('btn-inscr-<?= $idx ?>');
    const btnW = document.getElementById('btn-wait-<?= $idx ?>');
    const attInfo = document.getElementById('attente-info-<?= $idx ?>');
    const altBox  = document.getElementById('alt-box-<?= $idx ?>');
    const altList = document.getElementById('alt-list-<?= $idx ?>');

    if (confirme) {
        // Bouton désinscription
        document.getElementById('des-creneau-<?= $idx ?>').value = cr.id;
        document.getElementById('des-enfant-<?= $idx ?>').value  = selEnf;
        document.getElementById('btn-des-<?= $idx ?>').style.display = 'block';
        btnI.style.display = 'none'; btnW.style.display = 'none';
    } else if (enAtt) {
        // Bouton quitter attente
        document.getElementById('quit-creneau-<?= $idx ?>').value = cr.id;
        document.getElementById('quit-enfant-<?= $idx ?>').value  = selEnf;
        document.getElementById('btn-quit-<?= $idx ?>').style.display = 'block';
        attInfo.textContent = '⏳ Cet enfant est en liste d\'attente à la position #'+enAtt+'.';
        attInfo.style.display = 'block';
        btnI.style.display = 'none'; btnW.style.display = 'none';
    } else if (full) {
        // Créneau plein → proposer attente + alternatives
        btnI.style.display = 'none';
        btnW.style.display = 'block';

        const alts = altsData_<?= $idx ?>[cr.id] || [];
        if (alts.length) {
            altList.innerHTML = '';
            alts.forEach(a => {
                const dispo = a.capacite - a.nb_ins;
                const d = document.createElement('div');
                d.className = 'alt-item';
                d.innerHTML = '📅 <strong>'+formatDate(a.date)+'</strong>'
                    +' · '+a.debut.substring(0,5)+' – '+a.fin.substring(0,5)
                    +' · <strong style="color:#16a34a">'+dispo+' place'+(dispo>1?'s':'')+' dispo</strong>';
                altList.appendChild(d);
            });
            altBox.style.display = 'block';
        }
    } else {
        btnI.style.display = 'block';
        btnW.style.display = 'none';
    }
}

function resetActions(){
    document.getElementById('btn-inscr-<?= $idx ?>').style.display    = 'none';
    document.getElementById('btn-wait-<?= $idx ?>').style.display     = 'none';
    document.getElementById('btn-des-<?= $idx ?>').style.display      = 'none';
    document.getElementById('btn-quit-<?= $idx ?>').style.display     = 'none';
    document.getElementById('attente-info-<?= $idx ?>').style.display = 'none';
    document.getElementById('alt-box-<?= $idx ?>').style.display      = 'none';
}

function formatDate(ds){
    const [y,m,d] = ds.split('-');
    return d+'/'+m+'/'+y;
}

window.onEnfantChange_<?= $idx ?> = function(){ if(lastDate) renderSlots(lastDate); };

renderCal();
})();
</script>
<?php endif; ?>
<?php endforeach; ?>

<?php if (empty($activites)): ?>
<div style="text-align:center;padding:60px;color:#aaa;">Aucune activité disponible.</div>
<?php endif; ?>
</div>

<script>
document.getElementById('searchBar').addEventListener('input', function(){
    const t = this.value.toLowerCase();
    document.querySelectorAll('.activity-item').forEach(i =>
        i.style.display = i.querySelector('h2').innerText.toLowerCase().includes(t) ? '' : 'none'
    );
});
</script>
</body>
</html>