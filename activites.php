<?php
require_once 'config.php';
requireParent();

$db    = getDB();
$login = $_SESSION['user'];
$message = ''; $messageType = '';

$enfantsStmt = $db->prepare("SELECT id, nom, prenom FROM Enfant WHERE login_famille=? ORDER BY id");
$enfantsStmt->execute([$login]);
$enfants = $enfantsStmt->fetchAll();

// ── INSCRIPTION ──
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='inscrire') {
    $id_enfant  = intval($_POST['id_enfant']??0);
    $id_creneau = intval($_POST['id_creneau']??0);
    if (!$id_enfant || !$id_creneau) {
        $message='Veuillez sélectionner un enfant et un créneau.'; $messageType='error';
    } else {
        $chk=$db->prepare("SELECT id FROM Enfant WHERE id=? AND login_famille=?");
        $chk->execute([$id_enfant,$login]);
        if (!$chk->fetch()) { $message='Enfant non trouvé.'; $messageType='error'; }
        else {
            $deja=$db->prepare("SELECT * FROM Enfant_Creneau WHERE id_enfant=? AND id_creneau=?");
            $deja->execute([$id_enfant,$id_creneau]);
            if ($deja->fetch()) { $message='Cet enfant est déjà inscrit à ce créneau.'; $messageType='error'; }
            else {
                $db->prepare("INSERT INTO Enfant_Creneau (id_enfant,id_creneau) VALUES (?,?)")->execute([$id_enfant,$id_creneau]);
                $nb=$db->prepare("SELECT COUNT(*) FROM Enfant_Creneau WHERE id_creneau=?"); $nb->execute([$id_creneau]);
                $cap=$db->prepare("SELECT a.capacite FROM Creneau c JOIN Activité a ON a.nom=c.nom_activite WHERE c.id=?"); $cap->execute([$id_creneau]);
                $statut=($nb->fetchColumn()<=$cap->fetchColumn())?'accepté':'liste d\'attente';
                $message=$statut==='accepté' ? "✔ Inscription confirmée !" : "⏳ Créneau complet — votre enfant est en liste d'attente.";
                $messageType=$statut==='accepté'?'success':'info';
            }
        }
    }
}

// ── DÉSINSCRIPTION ──
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='desinscrire') {
    $id_enfant=intval($_POST['id_enfant']??0); $id_creneau=intval($_POST['id_creneau']??0);
    $chk=$db->prepare("SELECT id FROM Enfant WHERE id=? AND login_famille=?"); $chk->execute([$id_enfant,$login]);
    if ($chk->fetch()) {
        $db->prepare("DELETE FROM Enfant_Creneau WHERE id_enfant=? AND id_creneau=?")->execute([$id_enfant,$id_creneau]);
        $message="✔ Désinscription effectuée."; $messageType='success';
    }
}

$activites=$db->query("SELECT * FROM Activité ORDER BY nom")->fetchAll();
$allCreneaux=$db->query("
    SELECT c.*, s.batiment, s.id AS salle_id,
           COUNT(ec.id_enfant) AS nb_inscrits, a.capacite AS cap_activite
    FROM Creneau c
    LEFT JOIN Salle s ON s.id=c.id_salle
    LEFT JOIN Activité a ON a.nom=c.nom_activite
    LEFT JOIN Enfant_Creneau ec ON ec.id_creneau=c.id
    GROUP BY c.id ORDER BY c.date,c.debut
")->fetchAll();

$creneauxByActivite=[];
foreach($allCreneaux as $cr) $creneauxByActivite[$cr['nom_activite']][]=$cr;

// Inscriptions existantes de cette famille
$mesInscriptions=[];
if (!empty($enfants)) {
    $ids=implode(',',array_column($enfants,'id'));
    $rows=$db->query("SELECT id_enfant,id_creneau FROM Enfant_Creneau WHERE id_enfant IN ($ids)")->fetchAll();
    foreach($rows as $r) $mesInscriptions[$r['id_enfant']][]=(int)$r['id_creneau'];
}

$imgMap=['Arts'=>'https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=400','Jeux'=>'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=400','Musique'=>'https://images.unsplash.com/photo-1511379938547-c1f69419868d?w=400','Lecture'=>'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=400','Cuisine'=>'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400'];
function getImg($nom,$map){foreach($map as $k=>$v){if(stripos($nom,$k)!==false)return $v;}return'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=400';}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Nos Activités</title>
<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
<style>
.act-grid{display:grid;grid-template-columns:240px 1fr 230px 210px;gap:20px;padding:20px;background:#fafafa;border-radius:20px;margin:10px 0 20px;}
.activity-img{width:100%;height:180px;border-radius:15px;object-fit:cover;}
.cal-box{border:1px solid #ddd;border-radius:12px;padding:10px;background:white;}
.cal-nav{display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;}
.cal-nav button{background:none;border:1px solid #ddd;cursor:pointer;font-size:1rem;padding:2px 8px;border-radius:6px;}
.cal-nav button:hover{background:#eee;}
.cal-month-label{font-weight:800;font-size:.85rem;text-align:center;color:#1a5fb4;}
.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:2px;text-align:center;font-size:11px;}
.dl{font-weight:700;color:#aaa;padding-bottom:3px;}
.dj{padding:5px 0;border-radius:5px;}
.dj.clickable{cursor:pointer;}
.dj.clickable:hover{background:#d6f2ff;}
.dj.active{background:#1a5fb4!important;color:#fff!important;font-weight:bold;}
.dj.has-slot{background:#e8f5e9;font-weight:bold;}
.dj.empty{color:#ddd;}
.slot-list{display:flex;flex-direction:column;gap:6px;max-height:200px;overflow-y:auto;}
.slot-item{border:1px solid #eee;padding:8px;text-align:center;border-radius:8px;font-size:12px;cursor:pointer;background:white;transition:.2s;}
.slot-item:hover:not(.full):not(.inscrit){border-color:#ff4d8d;background:#fff5f8;}
.slot-item.selected{background:#222;color:#fff;border-color:#222;}
.slot-item.full{background:#ffe0e0;color:#c00;cursor:not-allowed;}
.slot-item.inscrit{background:#e8f5e9;color:#1e7e34;border-color:#a5d6a7;cursor:default;}
.inscr-box{display:flex;flex-direction:column;gap:8px;}
.inscr-box select{width:100%;padding:8px;border-radius:8px;border:1px solid #ccc;font-size:13px;}
.btn-inscr{background:#eaff00;border:none;padding:10px;font-weight:bold;border-radius:10px;cursor:pointer;font-size:13px;width:100%;}
.btn-inscr:hover{background:#d4e800;}
.btn-desins{background:#ff7043;color:white;border:none;padding:8px;font-weight:bold;border-radius:10px;cursor:pointer;font-size:12px;width:100%;display:none;}
summary h2{display:inline;font-size:1.4rem;font-weight:800;}
.short-desc{color:#666;font-size:.85rem;margin-left:28px;margin-top:3px;}
@media(max-width:900px){.act-grid{grid-template-columns:1fr;}}
</style>
</head>
<body>
<header style="background:#fdf6d8;padding:12px 50px;display:flex;justify-content:space-between;align-items:center;">
    <h1 style="font-size:2rem;font-weight:900;margin:0;">Les activités</h1>
    <nav><a href="index.php">Accueil</a> <a href="parent-enfants.php">Mon espace</a> <a href="deconnexion.php" style="color:#c0392b;">se déconnecter</a></nav>
</header>
<div style="text-align:center;padding:35px 20px 10px;">
    <h1 style="color:#1a5fb4;font-size:2.8rem;font-weight:900;font-family:'Baloo 2';">NOS ACTIVITÉS DU MERCREDI</h1>
    <p style="color:#d4ac0d;font-style:italic;font-size:1.3rem;margin-top:8px;">crée, explore, imagine, chaque semaine</p>
</div>
<?php if($message):?><div style="max-width:700px;margin:10px auto;"><div class="alert alert-<?=$messageType?>"><?=$message?></div></div><?php endif;?>
<?php if(empty($enfants)):?>
<div style="text-align:center;background:#fff3cd;border:1px solid #ffc107;padding:15px;max-width:600px;margin:15px auto;border-radius:10px;">
⚠️ Aucun enfant enregistré. <a href="ajouter-enfant.php" style="color:#ff5e78;font-weight:bold;">Ajouter un enfant</a>.
</div><?php endif;?>
<div class="search-container"><input type="text" id="searchBar" class="search-bar" placeholder="🔍  rechercher une activité..."></div>

<div style="max-width:1200px;margin:0 auto;padding:0 20px 60px;">
<?php foreach($activites as $idx=>$act):
    $creneaux=$creneauxByActivite[$act['nom']]??[];
    $byDate=[];
    foreach($creneaux as $cr) $byDate[$cr['date']][]=$cr;
    $dates=array_keys($byDate); sort($dates);
    $firstDate=$dates[0]??null;
    $initY=$firstDate?(int)date('Y',strtotime($firstDate)):date('Y');
    $initM=$firstDate?(int)date('n',strtotime($firstDate)):date('n');
?>
<details class="activity-item" id="act-<?=$idx?>">
    <summary>
        <span class="arrow">▶</span>
        <div>
            <h2><?=htmlspecialchars($act['nom'])?></h2>
            <div class="short-desc">Capacité : <?=$act['capacite']?> places · <?=count($creneaux)?> créneau(x)</div>
        </div>
    </summary>
    <div class="act-grid">
        <img src="<?=getImg($act['nom'],$imgMap)?>" class="activity-img" alt="<?=htmlspecialchars($act['nom'])?>">
        <div>
            <h3 style="margin-top:0;font-size:1.3rem;"><?=htmlspecialchars($act['nom'])?></h3>
            <p style="color:#555;font-size:.9rem;line-height:1.6;"><?=nl2br(htmlspecialchars($act['syllabus']))?></p>
            <p style="font-size:.8rem;color:#888;margin-top:8px;"><strong>Capacité :</strong> <?=$act['capacite']?> enfants/créneau</p>
        </div>

        <!-- CALENDRIER avec mois + année + navigation -->
        <div class="cal-box">
            <?php if($firstDate):?>
            <div class="cal-nav">
                <button onclick="prevMonth_<?=$idx?>()">◀</button>
                <div class="cal-month-label" id="cal-label-<?=$idx?>"></div>
                <button onclick="nextMonth_<?=$idx?>()">▶</button>
            </div>
            <div class="cal-grid" id="cal-<?=$idx?>"></div>
            <?php else:?>
            <div style="text-align:center;color:#aaa;padding:20px;font-size:.85rem;">Aucun créneau planifié</div>
            <?php endif;?>
        </div>

        <!-- CRÉNEAUX + INSCRIPTION + DÉSINSCRIPTION -->
        <div class="inscr-box">
            <div style="font-size:11px;font-weight:bold;color:#555;margin-bottom:3px;">Horaires disponibles :</div>
            <div class="slot-list" id="slots-<?=$idx?>">
                <div style="text-align:center;color:#aaa;font-size:.8rem;padding:10px;">← Choisissez une date</div>
            </div>
            <form method="POST" action="activites.php">
                <input type="hidden" name="action" value="inscrire">
                <input type="hidden" name="id_creneau" id="creneau-<?=$idx?>" value="">
                <select name="id_enfant" id="sel-<?=$idx?>" onchange="onEnfantChange_<?=$idx?>()">
                    <option value="">-- Choisir un enfant --</option>
                    <?php foreach($enfants as $enf):?>
                    <option value="<?=$enf['id']?>"><?=htmlspecialchars($enf['prenom'].' '.$enf['nom'])?></option>
                    <?php endforeach;?>
                </select>
                <button type="submit" class="btn-inscr">+ inscrire</button>
            </form>
            <form method="POST" action="activites.php" onsubmit="return confirm('Se désinscrire ?')">
                <input type="hidden" name="action" value="desinscrire">
                <input type="hidden" name="id_creneau" id="des-creneau-<?=$idx?>" value="">
                <input type="hidden" name="id_enfant"  id="des-enfant-<?=$idx?>"  value="">
                <button type="submit" class="btn-desins" id="btn-des-<?=$idx?>">✕ se désinscrire</button>
            </form>
        </div>
    </div>
</details>

<?php if($firstDate):?>
<script>
(function(){
const byDate_<?=$idx?> = <?=json_encode($byDate)?>;
const mesIns_<?=$idx?> = <?=json_encode($mesInscriptions)?>;
const moisFR=['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
let cy=<?=$initY?>, cm=<?=$initM?>;

function pad(n){return n<10?'0'+n:n;}

function renderCal(){
    document.getElementById('cal-label-<?=$idx?>').textContent = moisFR[cm]+' '+cy;
    const g=document.getElementById('cal-<?=$idx?>');
    g.innerHTML='';
    ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'].forEach(d=>{
        const s=document.createElement('span');s.className='dl';s.textContent=d;g.appendChild(s);
    });
    const fd=new Date(cy,cm-1,1).getDay();
    const dim=new Date(cy,cm,0).getDate();
    for(let e=0;e<fd;e++){const s=document.createElement('span');s.className='dj empty';g.appendChild(s);}
    for(let d=1;d<=dim;d++){
        const ds=cy+'-'+pad(cm)+'-'+pad(d);
        const has=!!byDate_<?=$idx?>[ds];
        const s=document.createElement('span');
        s.textContent=d;
        s.className='dj'+(has?' has-slot clickable':' empty');
        if(has){s.title=byDate_<?=$idx?>[ds].length+' créneau(x)';s.onclick=()=>selectDate(ds,s);}
        g.appendChild(s);
    }
}

window.prevMonth_<?=$idx?>=function(){cm--;if(cm<1){cm=12;cy--;}renderCal();};
window.nextMonth_<?=$idx?>=function(){cm++;if(cm>12){cm=1;cy++;}renderCal();};

let lastDate=null;
function selectDate(date,el){
    lastDate=date;
    document.querySelectorAll('#cal-<?=$idx?> .dj').forEach(d=>d.classList.remove('active'));
    el.classList.add('active');
    renderSlots(date);
}

function renderSlots(date){
    const crs=byDate_<?=$idx?>[date]||[];
    const list=document.getElementById('slots-<?=$idx?>');
    list.innerHTML='';
    document.getElementById('creneau-<?=$idx?>').value='';
    document.getElementById('btn-des-<?=$idx?>').style.display='none';

    const selEnf=parseInt(document.getElementById('sel-<?=$idx?>').value)||0;

    if(!crs.length){list.innerHTML='<div style="text-align:center;color:#aaa;font-size:.8rem;padding:10px;">Aucun créneau ce jour</div>';return;}
    crs.forEach(cr=>{
        const full=cr.nb_inscrits>=cr.cap_activite;
        const inscrit=selEnf&&mesIns_<?=$idx?>[selEnf]&&mesIns_<?=$idx?>[selEnf].includes(cr.id);
        const salle=cr.salle_id?' · <strong>Salle '+cr.salle_id+'</strong>':'';
        const div=document.createElement('div');
        div.className='slot-item'+(inscrit?' inscrit':full?' full':'');
        div.innerHTML='<strong>'+cr.debut.substring(0,5)+' – '+cr.fin.substring(0,5)+'</strong>'+salle+'<br>'
            +'<small style="opacity:.7">'+cr.nb_inscrits+'/'+cr.cap_activite+' inscrits'
            +(full?' · <em>complet</em>':'')+(inscrit?' · ✔ déjà inscrit':'')+'</small>';
        if(!full&&!inscrit){div.onclick=()=>selectSlot(cr.id,div,selEnf);}
        list.appendChild(div);
    });
}

function selectSlot(id,el,selEnf){
    document.querySelectorAll('#slots-<?=$idx?> .slot-item').forEach(s=>s.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('creneau-<?=$idx?>').value=id;
    const inscrit=selEnf&&mesIns_<?=$idx?>[selEnf]&&mesIns_<?=$idx?>[selEnf].includes(id);
    const btnDes=document.getElementById('btn-des-<?=$idx?>');
    if(inscrit){
        document.getElementById('des-creneau-<?=$idx?>').value=id;
        document.getElementById('des-enfant-<?=$idx?>').value=selEnf;
        btnDes.style.display='block';
    } else { btnDes.style.display='none'; }
}

window.onEnfantChange_<?=$idx?>=function(){if(lastDate)renderSlots(lastDate);};

renderCal();
})();
</script>
<?php endif;?>
<?php endforeach;?>
<?php if(empty($activites)):?><div style="text-align:center;padding:60px;color:#aaa;">Aucune activité disponible.</div><?php endif;?>
</div>
<script>
document.getElementById('searchBar').addEventListener('input',function(){
    const t=this.value.toLowerCase();
    document.querySelectorAll('.activity-item').forEach(i=>i.style.display=i.querySelector('h2').innerText.toLowerCase().includes(t)?'':'none');
});
</script>
</body></html>
