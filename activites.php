<?php require_once 'php/activites.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Activites - Ateliers du Mercredi</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/activites.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
/* ── Panneau recommandations ── */
.reco-panel {
    display: none;
    padding: 20px 24px 24px;
    background: #f8f9ff;
    border-top: 2px solid #e2e8f0;
}
.reco-header { margin-bottom: 16px; }
.reco-header h4 { font-family: 'Baloo 2'; color: #1a1a2e; margin: 0 0 4px; font-size: 1.1rem; }
.reco-header p  { color: #666; font-size: .85rem; margin: 0; }

/* Filtres dans le panneau reco */
.reco-filters {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 14px;
    padding: 12px;
    background: white;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
}
.reco-filters select,
.reco-filters input {
    padding: 6px 10px;
    border-radius: 8px;
    border: 1px solid #d0d0e0;
    font-size: .83rem;
    background: #fff;
    color: #333;
}
.reco-filters label {
    font-size: .78rem;
    font-weight: 700;
    color: #666;
    display: flex;
    align-items: center;
    gap: 5px;
}
.reco-filter-btn {
    background: #1a1a2e;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 6px 14px;
    font-size: .82rem;
    cursor: pointer;
    font-weight: bold;
}
.reco-filter-btn:hover { background: #111; }
.reco-filter-reset {
    background: #eee;
    color: #555;
    border: none;
    border-radius: 8px;
    padding: 6px 14px;
    font-size: .82rem;
    cursor: pointer;
}

.reco-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 14px;
}
.reco-card {
    background: white;
    border-radius: 12px;
    padding: 14px;
    border: 2px solid #e2e8f0;
    transition: border-color .15s;
}
.reco-card:hover { border-color: #9ca3af; }
.reco-card.best-match { border-color: #1a1a2e; }
.reco-card-title { font-weight: 800; font-size: .92rem; color: #1a1a2e; margin-bottom: 8px; }
.reco-card-meta  { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 8px; }
.reco-meta-chip  { font-size: .73rem; padding: 3px 8px; border-radius: 6px; }
.chip-date  { background: #dbeafe; color: #1e40af; }
.chip-time  { background: #f3e8ff; color: #7e22ce; }
.chip-room  { background: #dcfce7; color: #166534; }
.reco-fill-bar   { width: 100%; height: 6px; background: #e5e7eb; border-radius: 99px; overflow: hidden; margin-bottom: 5px; }
.reco-fill-inner { height: 100%; border-radius: 99px; }
.fill-low  { background: #22c55e; }
.fill-mid  { background: #f59e0b; }
.fill-high { background: #ef4444; }
.reco-places { font-size: .75rem; color: #666; margin-bottom: 8px; }
.reco-tags   { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 10px; }
.reco-tag    { font-size: .68rem; padding: 2px 7px; border-radius: 10px; font-weight: 700; }
.tag-same-activity { background: #1a1a2e; color: white; }
.tag-same-theme    { background: #4a4a6a; color: white; }
.tag-age-match     { background: #2563eb; color: white; }
.tag-close-time    { background: #7c3aed; color: white; }
.tag-low-fill      { background: #059669; color: white; }
.tag-similar-name  { background: #6b7280; color: white; }
.reco-btn {
    width: 100%;
    background: #1a1a2e;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 8px;
    font-size: .8rem;
    font-weight: 700;
    cursor: pointer;
}
.reco-btn:hover   { background: #111; }
.reco-btn:disabled { background: #ccc; cursor: not-allowed; }
.reco-empty { color: #aaa; font-size: .85rem; padding: 20px; text-align: center; grid-column: 1/-1; }

/* compteur de resultats */
.reco-count { font-size: .8rem; color: #888; margin-bottom: 10px; }
    </style>
</head>
<body>

<header style="background:#fdf6d8; padding:12px 50px; display:flex; justify-content:space-between; align-items:center;">
    <h1 style="font-size:2rem; font-weight:900; margin:0;">Les activites</h1>
    <nav>
        <a href="index.php">Accueil</a>
        <a href="modifier-compte-parent.php">Mon compte</a>
        <a href="parent-enfants.php">Mon espace</a>
        <a href="deconnexion.php" style="color:#c0392b;">se deconnecter</a>
    </nav>
</header>

<div style="text-align:center; padding:35px 20px 10px;">
    <h1 style="color:#1a5fb4; font-size:2.8rem; font-weight:900; font-family:'Baloo 2';">NOS ACTIVITES DU MERCREDI</h1>
    <p style="color:#d4ac0d; font-style:italic; font-size:1.3rem; margin-top:8px;">cree, explore, imagine, chaque semaine</p>
</div>

<?php if ($message): ?>
<div style="max-width:780px; margin:12px auto;">
    <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
</div>
<?php endif; ?>

<?php if (empty($enfants)): ?>
<div style="text-align:center; background:#fff3cd; border:1px solid #ffc107; padding:15px; max-width:600px; margin:15px auto; border-radius:10px;">
    Aucun enfant enregistre. <a href="ajouter-enfant.php" style="color:#ff5e78; font-weight:bold;">Ajouter un enfant</a>.
</div>
<?php endif; ?>

<div class="legend" style="max-width:1200px; margin:0 auto 4px; padding:0 20px;">
    <span><span class="legend-dot dot-ok"></span> Place disponible</span>
    <span><span class="legend-dot dot-wait"></span> Presque complet</span>
    <span><span class="legend-dot dot-full"></span> Complet - liste d'attente possible</span>
</div>

<div class="search-container" style="max-width:1200px; margin:0 auto; padding:0 20px 10px;">
    <input type="text" id="searchBar" class="search-bar" placeholder="  rechercher une activite...">
</div>

<div style="max-width:1200px; margin:0 auto; padding:0 20px 60px;">

<?php foreach ($activites as $idx => $act):
    $creneaux = $creneauxByActivite[$act['nom']] ?? [];
    $byDate   = [];
    foreach ($creneaux as $cr) { $byDate[$cr['date']][] = $cr; }
    $dates     = array_keys($byDate); sort($dates);
    $firstDate = $dates[0] ?? null;
    $initY     = $firstDate ? (int)date('Y', strtotime($firstDate)) : (int)date('Y');
    $initM     = $firstDate ? (int)date('n', strtotime($firstDate)) : (int)date('n');

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

    $recosByCreneauJs = [];
    foreach ($creneaux as $cr) {
        $crId = (int)$cr['id'];
        if (isset($recommendationsData[$crId])) {
            $recosByCreneauJs[$crId] = $recommendationsData[$crId];
        }
    }

    // Recup des themes distincts pour le filtre
    $themesDispos = [];
    foreach ($recommendationsData as $recs) {
        foreach ($recs as $r) {
            if (!empty($r['theme']) && !in_array($r['theme'], $themesDispos)) {
                $themesDispos[] = $r['theme'];
            }
        }
    }
?>
<details class="activity-item" id="act-<?= $idx ?>" style="margin-bottom:18px; border:1px solid #e2e8f0; border-radius:18px; overflow:hidden;">
    <summary style="list-style:none; display:flex; align-items:center; gap:12px; padding:16px 22px; background:#fafafa; cursor:pointer;">
        <span class="arrow" style="font-size:1rem;">&#9658;</span>
        <div>
            <h2 style="margin:0; font-family:'Baloo 2'; font-size:1.4rem;"><?= htmlspecialchars($act['nom']) ?></h2>
            <div style="font-size:.82rem; color:#888;">Capacite : <?= $act['capacite'] ?> places &middot; <?= count($creneaux) ?> creneau(x)</div>
        </div>
    </summary>

    <div class="act-grid">
        <img src="<?= getImg($act['nom'], $imgMap) ?>" class="activity-img" alt="<?= htmlspecialchars($act['nom']) ?>">

        <div>
            <h3 style="margin-top:0; font-size:1.15rem;"><?= htmlspecialchars($act['nom']) ?></h3>
            <p style="color:#555; font-size:.88rem; line-height:1.6;"><?= nl2br(htmlspecialchars($act['syllabus'])) ?></p>
            <p style="font-size:.78rem; color:#888;"><strong>Capacite :</strong> <?= $act['capacite'] ?> enfants/creneau</p>
            <?php if (!empty($act['theme'])): ?>
            <p style="font-size:.78rem; color:#555;"><strong>Theme :</strong> <?= htmlspecialchars($act['theme']) ?></p>
            <?php endif; ?>
            <?php if (!empty($act['tranche_age'])): ?>
            <p style="font-size:.78rem; color:#555;"><strong>Age :</strong> <?= htmlspecialchars($act['tranche_age']) ?> ans</p>
            <?php endif; ?>
        </div>

        <div class="cal-box">
            <?php if ($firstDate): ?>
            <div class="cal-nav">
                <button onclick="prevMonth_<?= $idx ?>()">&#9668;</button>
                <div class="cal-month-label" id="cal-label-<?= $idx ?>"></div>
                <button onclick="nextMonth_<?= $idx ?>()">&#9658;</button>
            </div>
            <div class="cal-grid" id="cal-<?= $idx ?>"></div>
            <?php else: ?>
            <div style="text-align:center; color:#aaa; padding:20px; font-size:.85rem;">Aucun creneau planifie</div>
            <?php endif; ?>
        </div>

       <div class="inscr-box">
            <div style="font-size:11px; font-weight:bold; color:#555; margin-bottom:6px;">Choisir un enfant :</div>

            <!-- SELECT ENFANT EN PREMIER -->
            <select name="id_enfant_top" id="sel-<?= $idx ?>" onchange="onEnfantChange_<?= $idx ?>()" style="width:100%; padding:7px; border-radius:8px; border:1px solid #bbb; margin-bottom:10px; font-size:.9rem; background:#fff; color:#111;">
                <option value="">-- Choisir un enfant --</option>
                <?php foreach ($enfants as $enf): ?>
                <option value="<?= $enf['id'] ?>" data-age="<?= (int)$enf['age'] ?>"><?= htmlspecialchars($enf['prenom'] . ' ' . $enf['nom']) ?></option>
                <?php endforeach; ?>
            </select>

            <div style="font-size:11px; font-weight:bold; color:#555; margin-bottom:3px;">Horaires disponibles :</div>

            <div class="slot-list" id="slots-<?= $idx ?>">
                <div style="text-align:center; color:#aaa; font-size:.8rem; padding:10px;">Choisissez une date</div>
            </div>

            <div class="attente-info" id="attente-info-<?= $idx ?>"></div>

            <form method="POST" action="activites.php">
                <input type="hidden" name="action"     value="inscrire">
                <input type="hidden" name="id_creneau" id="creneau-<?= $idx ?>" value="">
                <input type="hidden" name="id_enfant"  id="hidden-enfant-<?= $idx ?>" value="">
                <button type="submit" class="btn-inscr" id="btn-inscr-<?= $idx ?>">+ Inscrire</button>
                <button type="submit" class="btn-wait"  id="btn-wait-<?= $idx ?>">Rejoindre la liste d'attente</button>
            </form>

            <form method="POST" action="activites.php" onsubmit="return confirm('Se désinscrire de ce créneau ?')">
                <input type="hidden" name="action"     value="desinscrire">
                <input type="hidden" name="id_creneau" id="des-creneau-<?= $idx ?>" value="">
                <input type="hidden" name="id_enfant"  id="des-enfant-<?= $idx ?>"  value="">
                <button type="submit" class="btn-desins" id="btn-des-<?= $idx ?>">Se désinscrire</button>
            </form>

            <form method="POST" action="activites.php" onsubmit="return confirm('Quitter la liste d\'attente ?')">
                <input type="hidden" name="action"     value="quitter_attente">
                <input type="hidden" name="id_creneau" id="quit-creneau-<?= $idx ?>" value="">
                <input type="hidden" name="id_enfant"  id="quit-enfant-<?= $idx ?>"  value="">
                <button type="submit" class="btn-quitter" id="btn-quit-<?= $idx ?>">Quitter la liste d'attente</button>
            </form>
        </div>

    <!-- Panneau recommandations pleine largeur -->
    <div class="reco-panel" id="reco-<?= $idx ?>">
        <div class="reco-header">
            <h4>Creneaux alternatifs disponibles</h4>
            <p id="reco-subtitle-<?= $idx ?>"></p>
        </div>

        <!-- Filtres pour les suggestions -->
        <div class="reco-filters">
            <label>
                Theme :
                <select id="filtre-theme-<?= $idx ?>">
                    <option value="">Tous les themes</option>
                </select>
            </label>
            <label>
                Date apres :
                <input type="date" id="filtre-date-<?= $idx ?>">
            </label>
            <label>
                <input type="checkbox" id="filtre-age-<?= $idx ?>">
                Meme tranche d'age
            </label>
            <label>
                <input type="checkbox" id="filtre-moins-rempli-<?= $idx ?>">
                Les moins remplis
            </label>
            <button class="reco-filter-btn" onclick="appliquerFiltres_<?= $idx ?>()">Filtrer</button>
            <button class="reco-filter-reset" onclick="resetFiltres_<?= $idx ?>()">Reinitialiser</button>
        </div>

        <div class="reco-count" id="reco-count-<?= $idx ?>"></div>
        <div class="reco-grid" id="reco-grid-<?= $idx ?>">
            <div class="reco-empty">Selectionnez un creneau complet pour voir les suggestions.</div>
        </div>
    </div>

</details>

<?php if ($firstDate): ?>
<script>
(function(){

const byDate_<?= $idx ?>      = <?= json_encode($byDate) ?>;
const colorByDate_<?= $idx ?> = <?= json_encode($colorByDate) ?>;
const mesIns_<?= $idx ?>      = <?= json_encode($mesInscriptions) ?>;
const mesAtt_<?= $idx ?>      = <?= json_encode($mesAttentes) ?>;
const recosByCreneau_<?= $idx ?> = <?= json_encode($recosByCreneauJs) ?>;

const moisFR = ['','Janvier','Fevrier','Mars','Avril','Mai','Juin','Juillet','Aout','Septembre','Octobre','Novembre','Decembre'];
const moisCourt = ['','jan','fev','mar','avr','mai','jun','jul','aou','sep','oct','nov','dec'];

function pad(n){ return n<10?'0'+n:n; }

function formatDateLong(ds){
    const[y,m,d]=ds.split('-');
    const jours=['dim','lun','mar','mer','jeu','ven','sam'];
    const j=new Date(ds+'T00:00:00').getDay();
    return jours[j]+' '+parseInt(d)+' '+moisCourt[parseInt(m)]+' '+y;
}

const raisonLabels = {
    'same_activity': { text:'Meme atelier',       cls:'tag-same-activity' },
    'same_theme':    { text:'Meme theme',          cls:'tag-same-theme'    },
    'age_match':     { text:'Age adapte',          cls:'tag-age-match'     },
    'close_time':    { text:'Horaire proche',      cls:'tag-close-time'    },
    'low_fill':      { text:'Peu rempli',          cls:'tag-low-fill'      },
    'similar_name':  { text:'Activite similaire',  cls:'tag-similar-name'  },
};

let cy=<?= $initY ?>, cm=<?= $initM ?>;
let currentCreneauId = null;
let currentEnfantId  = null;
// Stocker toutes les recos pour filtrage cote client
let allRecos = [];

// Remplir le select des themes au chargement
(function remplirThemes(){
    const sel = document.getElementById('filtre-theme-<?= $idx ?>');
    const themes = new Set();
    Object.values(recosByCreneau_<?= $idx ?>).forEach(recs => {
        recs.forEach(r => { if(r.theme) themes.add(r.theme); });
    });
    themes.forEach(t => {
        const opt = document.createElement('option');
        opt.value = t; opt.textContent = t;
        sel.appendChild(opt);
    });
})();

function renderCal(){
    document.getElementById('cal-label-<?= $idx ?>').textContent = moisFR[cm]+' '+cy;
    const g=document.getElementById('cal-<?= $idx ?>');
    g.innerHTML='';
    ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'].forEach(d=>{
        const s=document.createElement('span'); s.className='dl'; s.textContent=d; g.appendChild(s);
    });
    const fd=new Date(cy,cm-1,1).getDay();
    const dim=new Date(cy,cm,0).getDate();
    for(let e=0;e<fd;e++){const s=document.createElement('span');s.className='dj empty';g.appendChild(s);}
    for(let d=1;d<=dim;d++){
        const ds=cy+'-'+pad(cm)+'-'+pad(d);
        const has=!!byDate_<?= $idx ?>[ds];
        const s=document.createElement('span');
        s.textContent=d;
        if(has){
            const col=colorByDate_<?= $idx ?>[ds]||'ok';
            s.className='dj day-'+col+' clickable';
            s.title=byDate_<?= $idx ?>[ds].length+' creneau(x)';
            s.onclick=()=>selectDate(ds,s);
        } else {
            s.className='dj empty';
        }
        g.appendChild(s);
    }
}

window.prevMonth_<?= $idx ?>=function(){cm--;if(cm<1){cm=12;cy--;}renderCal();};
window.nextMonth_<?= $idx ?>=function(){cm++;if(cm>12){cm=1;cy++;}renderCal();};

let lastDate=null;

function selectDate(date,el){
    lastDate=date;
    document.querySelectorAll('#cal-<?= $idx ?> .dj').forEach(d=>d.classList.remove('active'));
    el.classList.add('active');
    renderSlots(date);
}

function renderSlots(date){
    const crs=byDate_<?= $idx ?>[date]||[];
    const list=document.getElementById('slots-<?= $idx ?>');
    const selEnf=parseInt(document.getElementById('sel-<?= $idx ?>').value)||0;
    list.innerHTML='';
    resetActions();
    hideReco();
    if(!crs.length){
        list.innerHTML='<div style="text-align:center;color:#aaa;font-size:.8rem;padding:10px;">Aucun creneau ce jour</div>';
        return;
    }
    crs.forEach(cr=>{
        const nb=parseInt(cr.nb_inscrits);
        const cap=parseInt(cr.cap_activite);
        const full=nb>=cap;
        const pct=cap>0?nb/cap:1;
        const quasi=!full&&pct>=0.8;
        const confirme=selEnf&&mesIns_<?= $idx ?>[selEnf]&&mesIns_<?= $idx ?>[selEnf].includes(cr.id);
        const enAtt=selEnf&&mesAtt_<?= $idx ?>[selEnf]&&mesAtt_<?= $idx ?>[selEnf][cr.id];
        const salle=cr.salle_id?' &middot; <strong>Salle '+cr.salle_id+'</strong>':'';

        const div=document.createElement('div');
        let cls='slot-item ';
        if(confirme)   cls+='inscrit';
        else if(enAtt) cls+='en-attente';
        else if(full)  cls+='full';
        else if(quasi) cls+='wait';
        else           cls+='ok';

        let badge='';
        if(confirme)   badge='<span class="badge badge-conf">Inscrit</span>';
        else if(enAtt) badge='<span class="badge badge-att">File #'+enAtt+'</span>';
        else if(full)  badge='<span class="badge badge-full">Complet</span>';
        else if(quasi) badge='<span class="badge badge-wait">Presque complet</span>';
        else           badge='<span class="badge badge-ok">Disponible</span>';

        const restantes=Math.max(0,cap-nb);
        div.className=cls;
        div.innerHTML='<strong>'+cr.debut.substring(0,5)+' - '+cr.fin.substring(0,5)+'</strong>'+salle+badge+'<br>'
            +'<small style="opacity:.7">'+nb+'/'+cap+' inscrits'
            +(full?' &middot; '+cr.nb_attente+' en attente':' &middot; '+restantes+' place'+(restantes>1?'s':'')+' restante'+(restantes>1?'s':''))
            +'</small>';
        div.onclick=()=>selectSlot(cr,div);
        list.appendChild(div);
    });
}

function selectSlot(cr, el) {
    // ✅ CORRECTION 1 : toujours relire la valeur actuelle du select
    const selEnf = parseInt(document.getElementById('sel-<?= $idx ?>').value) || 0;

    document.querySelectorAll('#slots-<?= $idx ?> .slot-item').forEach(s=>s.classList.remove('selected'));
    el.classList.add('selected');

    const nb=parseInt(cr.nb_inscrits);
    const cap=parseInt(cr.cap_activite);
    const full=nb>=cap;
    const confirme=selEnf&&mesIns_<?= $idx ?>[selEnf]&&mesIns_<?= $idx ?>[selEnf].includes(cr.id);
    const enAtt=selEnf&&mesAtt_<?= $idx ?>[selEnf]&&mesAtt_<?= $idx ?>[selEnf][cr.id];

    resetActions();
    hideReco();

    // ✅ CORRECTION 2 : alimenter les deux champs cachés du formulaire d'inscription
    document.getElementById('creneau-<?= $idx ?>').value       = cr.id;
    document.getElementById('hidden-enfant-<?= $idx ?>').value = selEnf;

    currentCreneauId = cr.id;
    currentEnfantId  = selEnf;

    const btnI=document.getElementById('btn-inscr-<?= $idx ?>');
    const btnW=document.getElementById('btn-wait-<?= $idx ?>');
    const attInfo=document.getElementById('attente-info-<?= $idx ?>');

    if(confirme){
        document.getElementById('des-creneau-<?= $idx ?>').value=cr.id;
        document.getElementById('des-enfant-<?= $idx ?>').value=selEnf;
        document.getElementById('btn-des-<?= $idx ?>').style.display='block';
        btnI.style.display='none'; btnW.style.display='none';
    } else if(enAtt){
        document.getElementById('quit-creneau-<?= $idx ?>').value=cr.id;
        document.getElementById('quit-enfant-<?= $idx ?>').value=selEnf;
        document.getElementById('btn-quit-<?= $idx ?>').style.display='block';
        attInfo.textContent='Cet enfant est en liste d\'attente a la position #'+enAtt+'.';
        attInfo.style.display='block';
        btnI.style.display='none'; btnW.style.display='none';
    } else if(full){
        btnI.style.display='none'; btnW.style.display='block';
        showReco(cr.id, selEnf);
    } else {
        btnI.style.display='block'; btnW.style.display='none';
    }
}

// Appliquer les filtres sur les recos
function getFilters(){
    return {
        theme: document.getElementById('filtre-theme-<?= $idx ?>').value,
        date:  document.getElementById('filtre-date-<?= $idx ?>').value,
        age:   document.getElementById('filtre-age-<?= $idx ?>').checked,
        moinsRempli: document.getElementById('filtre-moins-rempli-<?= $idx ?>').checked,
    };
}

function filtrerRecos(recos, filtres, selEnf){
    let res = [...recos];

    if(filtres.theme){
        res = res.filter(r => r.theme === filtres.theme);
    }
    if(filtres.date){
        res = res.filter(r => r.date >= filtres.date);
    }
    if(filtres.age && selEnf){
        const opt = document.querySelector('#sel-<?= $idx ?> option[value="'+selEnf+'"]');
        const age = opt ? parseInt(opt.dataset.age) : 0;
        if(age > 0){
            res = res.filter(r => {
                if(!r.tranche_age) return true;
                const m = r.tranche_age.match(/(\d+)-(\d+)/);
                if(!m) return true;
                return age >= parseInt(m[1]) && age <= parseInt(m[2]);
            });
        }
    }
    if(filtres.moinsRempli){
        // garder seulement les creneaux avec taux de remplissage < 50%
        res = res.filter(r => parseInt(r.taux_remplissage) < 50);
    }

    return res;
}

window.appliquerFiltres_<?= $idx ?> = function(){
    if(currentCreneauId === null) return;
    const filtres = getFilters();
    const filtered = filtrerRecos(allRecos, filtres, currentEnfantId);
    renderRecoGrid(filtered, currentEnfantId);
};

window.resetFiltres_<?= $idx ?> = function(){
    document.getElementById('filtre-theme-<?= $idx ?>').value = '';
    document.getElementById('filtre-date-<?= $idx ?>').value  = '';
    document.getElementById('filtre-age-<?= $idx ?>').checked = false;
    document.getElementById('filtre-moins-rempli-<?= $idx ?>').checked = false;
    if(currentCreneauId !== null){
        renderRecoGrid(allRecos, currentEnfantId);
    }
};

function showReco(creneauId, selEnf){
    const panel = document.getElementById('reco-<?= $idx ?>');
    const sub   = document.getElementById('reco-subtitle-<?= $idx ?>');
    const recos = recosByCreneau_<?= $idx ?>[creneauId] || [];

    allRecos = recos;
    currentCreneauId = creneauId;
    currentEnfantId  = selEnf;

    let enfNom = '';
    if(selEnf){
        const opt = document.querySelector('#sel-<?= $idx ?> option[value="'+selEnf+'"]');
        if(opt) enfNom = opt.textContent.trim();
    }

    sub.textContent = enfNom
        ? 'Ce creneau est complet - voici nos meilleures suggestions pour '+enfNom
        : 'Ce creneau est complet - voici nos meilleures suggestions';

    renderRecoGrid(recos, selEnf);
    panel.style.display = 'contents';
    setTimeout(()=> panel.scrollIntoView({behavior:'smooth', block:'nearest'}), 50);
}

function renderRecoGrid(recos, selEnf){
    const grid  = document.getElementById('reco-grid-<?= $idx ?>');
    const count = document.getElementById('reco-count-<?= $idx ?>');
    grid.innerHTML = '';

    if(!recos.length){
        count.textContent = '';
        grid.innerHTML = '<div class="reco-empty">Aucun creneau ne correspond a vos filtres.<br>Essayez de les elargir ou rejoignez la liste d\'attente.</div>';
        return;
    }

    count.textContent = recos.length + ' creneau(x) trouve(s)';

    recos.forEach((r, i) => {
        const taux   = parseInt(r.taux_remplissage) || 0;
        const places = parseInt(r.places_restantes) || 0;
        const dateF  = formatDateLong(r.date);
        const fillCls = taux < 40 ? 'fill-low' : taux < 75 ? 'fill-mid' : 'fill-high';
        const isBest = (i === 0);

        const tagsHtml = (r.raisons || []).map(reason => {
            const lbl = raisonLabels[reason];
            return lbl ? '<span class="reco-tag '+lbl.cls+'">'+lbl.text+'</span>' : '';
        }).join('');

        const salleHtml = r.id_salle
            ? '<span class="reco-meta-chip chip-room">Salle '+r.id_salle+'</span>'
            : '';

        let btnHtml = '';
        const alreadyIn   = selEnf && mesIns_<?= $idx ?>[selEnf] && mesIns_<?= $idx ?>[selEnf].includes(r.id);
        const alreadyWait = selEnf && mesAtt_<?= $idx ?>[selEnf] && mesAtt_<?= $idx ?>[selEnf][r.id];

        if(alreadyIn){
            btnHtml = '<button class="reco-btn" disabled>Deja inscrit</button>';
        } else if(alreadyWait){
            btnHtml = '<button class="reco-btn" disabled>Deja en attente</button>';
        } else if(!selEnf){
            btnHtml = '<button class="reco-btn" disabled>Choisir un enfant d\'abord</button>';
        } else {
            btnHtml = '<form method="POST" action="activites.php" style="margin:0;">'
                + '<input type="hidden" name="action" value="inscrire">'
                + '<input type="hidden" name="id_creneau" value="'+r.id+'">'
                + '<input type="hidden" name="id_enfant" value="'+selEnf+'">'
                + '<button type="submit" class="reco-btn">+ Inscrire sur ce creneau</button>'
                + '</form>';
        }

        const card = document.createElement('div');
        card.className = 'reco-card' + (isBest ? ' best-match' : '');
        card.innerHTML =
            '<div class="reco-card-title">'+escHtml(r.nom_activite)+'</div>'
            +'<div class="reco-card-meta">'
            +'<span class="reco-meta-chip chip-date">'+dateF+'</span>'
            +'<span class="reco-meta-chip chip-time">'+r.debut.substring(0,5)+' - '+r.fin.substring(0,5)+'</span>'
            +salleHtml
            +'</div>'
            +'<div class="reco-fill-bar"><div class="reco-fill-inner '+fillCls+'" style="width:'+taux+'%"></div></div>'
            +'<div class="reco-places"><strong>'+places+' place'+(places>1?'s':'')+' disponible'+(places>1?'s':'')+'</strong> &middot; '+taux+'% rempli</div>'
            +'<div class="reco-tags">'+tagsHtml+'</div>'
            +btnHtml;
        grid.appendChild(card);
    });
}

function hideReco(){
    document.getElementById('reco-<?= $idx ?>').style.display = 'none';
}

function escHtml(s){
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function resetActions(){
    ['btn-inscr','btn-wait','btn-des','btn-quit'].forEach(id=>
        document.getElementById(id+'-<?= $idx ?>').style.display='none'
    );
    document.getElementById('attente-info-<?= $idx ?>').style.display='none';
}

window.onEnfantChange_<?= $idx ?>=function(){
    if(lastDate) renderSlots(lastDate);
};

renderCal();
})();
</script>
<?php endif; ?>
<?php endforeach; ?>

<?php if (empty($activites)): ?>
<div style="text-align:center; padding:60px; color:#aaa;">Aucune activite disponible.</div>
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