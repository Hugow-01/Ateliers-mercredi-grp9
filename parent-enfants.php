<?php
require_once 'config.php';
requireParent();

$db    = getDB();
$login = $_SESSION['user'];

// Récupérer les enfants avec leurs activités, créneaux ET salles
$enfants = $db->prepare("
    SELECT e.*,
           GROUP_CONCAT(
               CONCAT(a.nom,'|',c.date,'|',c.debut,'|',ec.id_creneau,'|',IFNULL(c.id_salle,''))
               ORDER BY c.date SEPARATOR ';;'
           ) AS activites_raw
    FROM Enfant e
    LEFT JOIN Enfant_Creneau ec ON ec.id_enfant = e.id
    LEFT JOIN Creneau c         ON c.id = ec.id_creneau
    LEFT JOIN Activité a        ON a.nom = c.nom_activite
    WHERE e.login_famille = ?
    GROUP BY e.id
    ORDER BY e.id
");
$enfants->execute([$login]);
$enfants = $enfants->fetchAll();

// Statut : rang de l'enfant dans le créneau vs capacité
function getStatut($db, $id_creneau, $id_enfant, $nom_activite) {
    // Rang de cet enfant dans ce créneau (ordre d'inscription = ordre id)
    $rang = $db->prepare("
        SELECT COUNT(*) FROM Enfant_Creneau
        WHERE id_creneau=? AND id_enfant <= ?
    ");
    $rang->execute([$id_creneau, $id_enfant]);
    $r = (int)$rang->fetchColumn();
    $cap = $db->prepare("SELECT capacite FROM Activité WHERE nom=?");
    $cap->execute([$nom_activite]);
    $c = (int)($cap->fetchColumn() ?? 99);
    return $r <= $c ? 'accepté' : 'liste d\'attente';
}

$moisFR=['01'=>'jan','02'=>'fév','03'=>'mar','04'=>'avr','05'=>'mai','06'=>'jun','07'=>'jul','08'=>'aoû','09'=>'sep','10'=>'oct','11'=>'nov','12'=>'déc'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Espace Parent</title>
<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
<style>
.activity-item-card { margin-bottom:14px; padding-bottom:10px; border-bottom:1px solid rgba(255,255,255,0.2); }
.activity-item-card:last-child { border-bottom:none; }
.act-name-card { font-size:1.1rem; font-weight:bold; display:block; margin-bottom:4px; }
.act-detail    { font-size:.78rem; opacity:.85; margin-bottom:4px; }
.status-row    { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:6px; }
.badge-ok      { background:rgba(255,255,255,.3); padding:3px 10px; border-radius:8px; font-size:.8rem; font-weight:bold; }
.badge-wait    { background:rgba(255,80,80,.5);   padding:3px 10px; border-radius:8px; font-size:.8rem; font-weight:bold; }
.btn-desins-card { background:#ff7043; color:white; border:none; padding:4px 12px; border-radius:8px; font-size:.75rem; cursor:pointer; font-weight:bold; }
.salle-badge { background:rgba(255,255,255,.2); padding:2px 8px; border-radius:6px; font-size:.75rem; }
</style>
</head>
<body>

<header style="background:#fdf6d8;padding:12px 50px;display:flex;justify-content:space-between;align-items:center;">
    <h1 style="font-size:2rem;font-weight:900;margin:0;">Espace parent</h1>
    <nav>
        <a href="index.php">Accueil</a>
        <a href="activites.php">nos activités</a>
        <a href="deconnexion.php" style="color:#c0392b;">se déconnecter</a>
    </nav>
</header>

<?php
// Compter les inscriptions pour la notif de bienvenue
$nbIns = 0;
foreach ($enfants as $e) { if ($e['activites_raw']) $nbIns += count(explode(';;', $e['activites_raw'])); }
if ($nbIns > 0):?>
<div class="notification-bar" id="notif-bar">
    <span>Bienvenue, <?=htmlspecialchars($_SESSION['nom'])?> — <?=$nbIns?> inscription(s) enregistrée(s).</span>
    <span class="close-notif" onclick="document.getElementById('notif-bar').style.display='none'">✕</span>
</div>
<?php endif;?>

<main class="children-grid" style="margin-top:30px;">
<?php if(empty($enfants)):?>
    <div style="text-align:center;padding:40px;color:#888;font-size:1.2rem;">
        <p>Aucun enfant enregistré.</p><p>Commencez par ajouter un enfant !</p>
    </div>
<?php endif;?>

<?php foreach($enfants as $i=>$enfant):
    $ordinal=['1er','2ème','3ème','4ème','5ème'][$i]??($i+1).'ème';
    $activitesList=[];
    if($enfant['activites_raw']){
        foreach(explode(';;',$enfant['activites_raw']) as $act){
            $parts=explode('|',$act);
            if(count($parts)>=5 && $parts[0]){
                $statut=getStatut($db,(int)$parts[3],(int)$enfant['id'],$parts[0]);
                // Formater la date : 2026-03-04 → "4 mar 2026"
                $dp=explode('-',$parts[1]);
                $dateF=ltrim($dp[2]??'','0').' '.($moisFR[$dp[1]??'']??'').' '.($dp[0]??'');
                $activitesList[]=[
                    'nom'        => $parts[0],
                    'date'       => $dateF,
                    'heure'      => substr($parts[2],0,5),
                    'id_creneau' => (int)$parts[3],
                    'salle'      => $parts[4],
                    'statut'     => $statut
                ];
            }
        }
    }
?>
<div class="child-card">
    <div class="card-top">
        <h2 class="child-title"><?=$ordinal?> enfant</h2>
        <div class="info-group"><label>Nom :</label><div class="value"><?=htmlspecialchars($enfant['nom'])?></div></div>
        <div class="info-group"><label>Prénom :</label><div class="value"><?=htmlspecialchars($enfant['prenom'])?></div></div>
        <div class="info-group"><label>Âge :</label><div class="value"><?=htmlspecialchars($enfant['age'])?> ans</div></div>
    </div>
    <div class="card-bottom">
        <div class="section-title-card">Activités inscrites :</div>
        <?php if(empty($activitesList)):?>
            <p style="opacity:.7;font-size:.9rem;">Aucune activité choisie.</p>
        <?php else: foreach($activitesList as $act):?>
        <div class="activity-item-card">
            <span class="act-name-card"><?=htmlspecialchars($act['nom'])?></span>
            <div class="act-detail">
                <?=htmlspecialchars($act['date'])?>
                &nbsp; <?=htmlspecialchars($act['heure'])?>
                <?php if($act['salle']):?>
                &nbsp;<span class="salle-badge">Salle <?=htmlspecialchars($act['salle'])?></span>
                <?php endif;?>
            </div>
            <div class="status-row">
                <span class="<?=$act['statut']==='accepté'?'badge-ok':'badge-wait'?>">
                    <?=$act['statut']==='accepté'?'accepté':'liste d\'attente'?>
                </span>
                <form method="POST" action="activites.php" onsubmit="return confirm('Se désinscrire de cette activité ?')">
                    <input type="hidden" name="action"      value="desinscrire">
                    <input type="hidden" name="id_creneau"  value="<?=$act['id_creneau']?>">
                    <input type="hidden" name="id_enfant"   value="<?=$enfant['id']?>">
                    <button type="submit" class="btn-desins-card">✕ Se désinscrire</button>
                </form>
            </div>
        </div>
        <?php endforeach; endif;?>
    </div>
</div>
<?php endforeach;?>
</main>

<footer class="footer-actions">
    <a href="ajouter-enfant.php"><button class="btn btn-yellow" style="padding:12px 35px;font-size:1.1rem;font-weight:bold;border-radius:20px;">+ ajouter un enfant</button></a>
    <a href="activites.php"><button class="btn btn-primary btn-big">choisir une activité</button></a>
</footer>
</body></html>
