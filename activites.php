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

<?php
// ── Tous les thèmes distincts de TOUTES les activités (pour le combobox de filtres) ──
$tousLesThemes = array_values(array_unique(array_filter(array_column($activites, 'theme'))));
sort($tousLesThemes);
?>

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
        if ($allFull) $colorByDate[$date] = 'full';
        elseif ($hasWait) $colorByDate[$date] = 'wait';
        else  $colorByDate[$date] = 'ok';
    }

    $recosByCreneauJs = [];
    foreach ($creneaux as $cr) {
        $crId = (int)$cr['id'];
        if (isset($recommendationsData[$crId])) {
            $recosByCreneauJs[$crId] = $recommendationsData[$crId];
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
            <!-- Thème -->
            <label>
                Thème :
                <select id="filtre-theme-<?= $idx ?>">
                    <option value="">Tous les thèmes</option>
                    <?php foreach ($tousLesThemes as $th): ?>
                    <option value="<?= htmlspecialchars($th) ?>"><?= htmlspecialchars($th) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <!-- Filtres de date : intervalle -->
            <div class="filtre-date-group">
                <span>Intervalle de dates :</span>
                <div class="filtre-date-row">
                    <!-- Calendrier FR picker : date début -->
                    <div class="fr-cal-picker" id="picker-debut-<?= $idx ?>">
                        <div class="fr-cal-input-display"
                             id="picker-debut-<?= $idx ?>-display"
                             onclick="toggleFrCal('picker-debut-<?= $idx ?>')">
                            <span id="picker-debut-label-<?= $idx ?>">Date début</span>
                        </div>
                        <div class="fr-cal-dropdown" id="picker-debut-<?= $idx ?>-cal">
                            <div class="fr-cal-nav">
                                <button type="button" onclick="frCalPrev('picker-debut-<?= $idx ?>')">&#9668;</button>
                                <div class="fr-cal-month-label" id="picker-debut-<?= $idx ?>-month"></div>
                                <button type="button" onclick="frCalNext('picker-debut-<?= $idx ?>')">&#9658;</button>
                            </div>
                            <div class="fr-cal-grid" id="picker-debut-<?= $idx ?>-grid"></div>
                            <button type="button" class="fr-cal-clear"
                                    onclick="frCalClear('picker-debut-<?= $idx ?>', 'filtre-date-debut-<?= $idx ?>', 'picker-debut-label-<?= $idx ?>', 'Date début')">
                                Effacer
                            </button>
                        </div>
                        <input type="hidden" id="filtre-date-debut-<?= $idx ?>">
                    </div>
                    <small>→</small>
                    <!-- Calendrier FR picker : date fin -->
                    <div class="fr-cal-picker" id="picker-fin-<?= $idx ?>">
                        <div class="fr-cal-input-display"
                             id="picker-fin-<?= $idx ?>-display"
                             onclick="toggleFrCal('picker-fin-<?= $idx ?>')">
                            <span id="picker-fin-label-<?= $idx ?>">Date fin</span>
                        </div>
                        <div class="fr-cal-dropdown" id="picker-fin-<?= $idx ?>-cal">
                            <div class="fr-cal-nav">
                                <button type="button" onclick="frCalPrev('picker-fin-<?= $idx ?>')">&#9668;</button>
                                <div class="fr-cal-month-label" id="picker-fin-<?= $idx ?>-month"></div>
                                <button type="button" onclick="frCalNext('picker-fin-<?= $idx ?>')">&#9658;</button>
                            </div>
                            <div class="fr-cal-grid" id="picker-fin-<?= $idx ?>-grid"></div>
                            <button type="button" class="fr-cal-clear"
                                    onclick="frCalClear('picker-fin-<?= $idx ?>', 'filtre-date-fin-<?= $idx ?>', 'picker-fin-label-<?= $idx ?>', 'Date fin')">
                                Effacer
                            </button>
                        </div>
                        <input type="hidden" id="filtre-date-fin-<?= $idx ?>">
                    </div>
                </div>
            </div>

            <!-- Date exacte -->
            <div class="filtre-date-group">
                <span>Date exacte :</span>
                <div class="fr-cal-picker" id="picker-exacte-<?= $idx ?>">
                    <div class="fr-cal-input-display"
                         id="picker-exacte-<?= $idx ?>-display"
                         onclick="toggleFrCal('picker-exacte-<?= $idx ?>')">
                        <span id="picker-exacte-label-<?= $idx ?>">Choisir</span>
                    </div>
                    <div class="fr-cal-dropdown" id="picker-exacte-<?= $idx ?>-cal">
                        <div class="fr-cal-nav">
                            <button type="button" onclick="frCalPrev('picker-exacte-<?= $idx ?>')">&#9668;</button>
                            <div class="fr-cal-month-label" id="picker-exacte-<?= $idx ?>-month"></div>
                            <button type="button" onclick="frCalNext('picker-exacte-<?= $idx ?>')">&#9658;</button>
                        </div>
                        <div class="fr-cal-grid" id="picker-exacte-<?= $idx ?>-grid"></div>
                        <button type="button" class="fr-cal-clear"
                                onclick="frCalClear('picker-exacte-<?= $idx ?>', 'filtre-date-exacte-<?= $idx ?>', 'picker-exacte-label-<?= $idx ?>', 'Choisir')">
                            Effacer
                        </button>
                    </div>
                    <input type="hidden" id="filtre-date-exacte-<?= $idx ?>">
                </div>
            </div>

            <!-- Cases à cocher -->
            <label class="inline-label">
                <input type="checkbox" id="filtre-age-<?= $idx ?>">
                Même tranche d'âge
            </label>
            <label class="inline-label">
                <input type="checkbox" id="filtre-moins-rempli-<?= $idx ?>">
                Les moins remplis (&lt;50%)
            </label>

            <button class="reco-filter-btn" onclick="appliquerFiltres_<?= $idx ?>()">Filtrer</button>
            <button class="reco-filter-reset" onclick="resetFiltres_<?= $idx ?>()">Réinitialiser</button>
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

const byDate_<?= $idx ?> = <?= json_encode($byDate) ?>;
const colorByDate_<?= $idx ?> = <?= json_encode($colorByDate) ?>;
const mesIns_<?= $idx ?> = <?= json_encode($mesInscriptions) ?>;
const mesAtt_<?= $idx ?> = <?= json_encode($mesAttentes) ?>;
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

function formatDateFR(ds){
    if(!ds) return '';
    const[y,m,d]=ds.split('-');
    return pad(parseInt(d))+'/'+pad(parseInt(m))+'/'+y;
}

let cy=<?= $initY ?>, cm=<?= $initM ?>;
let currentCreneauId = null;
let currentEnfantId  = null;
let allRecos = [];

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
        else cls+='ok';

        let badge='';
        if(confirme)   badge='<span class="badge badge-conf">Inscrit</span>';
        else if(enAtt) badge='<span class="badge badge-att">File #'+enAtt+'</span>';
        else if(full)  badge='<span class="badge badge-full">Complet</span>';
        else if(quasi) badge='<span class="badge badge-wait">Presque complet</span>';
        else badge='<span class="badge badge-ok">Disponible</span>';

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

/* ── Filtres ── */
function getFilters(){
    return {
        theme: document.getElementById('filtre-theme-<?= $idx ?>').value,
        dateDebut:  document.getElementById('filtre-date-debut-<?= $idx ?>').value,
        dateFin: document.getElementById('filtre-date-fin-<?= $idx ?>').value,
        dateExacte: document.getElementById('filtre-date-exacte-<?= $idx ?>').value,
        age:  document.getElementById('filtre-age-<?= $idx ?>').checked,
        moinsRempli:document.getElementById('filtre-moins-rempli-<?= $idx ?>').checked,
    };
}

function filtrerRecos(recos, filtres, selEnf){
    let res = [...recos];

    if(filtres.theme){
        res = res.filter(r => r.theme === filtres.theme);
    }

    // Date exacte prime sur l'intervalle
    if(filtres.dateExacte){
        res = res.filter(r => r.date === filtres.dateExacte);
    } else {
        if(filtres.dateDebut) res = res.filter(r => r.date >= filtres.dateDebut);
        if(filtres.dateFin)   res = res.filter(r => r.date <= filtres.dateFin);
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
    frCalClear('picker-debut-<?= $idx ?>',  'filtre-date-debut-<?= $idx ?>',  'picker-debut-label-<?= $idx ?>',  'Date début');
    frCalClear('picker-fin-<?= $idx ?>',    'filtre-date-fin-<?= $idx ?>',    'picker-fin-label-<?= $idx ?>',    'Date fin');
    frCalClear('picker-exacte-<?= $idx ?>', 'filtre-date-exacte-<?= $idx ?>', 'picker-exacte-label-<?= $idx ?>', 'Choisir');
    document.getElementById('filtre-age-<?= $idx ?>').checked          = false;
    document.getElementById('filtre-moins-rempli-<?= $idx ?>').checked = false;
    if(currentCreneauId !== null){
        renderRecoGrid(allRecos, currentEnfantId);
    }
};

function showReco(creneauId, selEnf){
    const panel = document.getElementById('reco-<?= $idx ?>');
    const sub = document.getElementById('reco-subtitle-<?= $idx ?>');
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
        grid.innerHTML = '<div class="reco-empty">Aucun créneau disponible.<br>Rejoignez la liste d\'attente.</div>';
        return;
    }

    count.textContent = recos.length + ' créneau(x) trouvé(s)';

    recos.forEach((r, i) => {
        const taux = parseInt(r.taux_remplissage) || 0;
        const places = parseInt(r.places_restantes) || 0;
        const dateF  = formatDateLong(r.date);
        const fillCls = taux < 40 ? 'fill-low' : taux < 75 ? 'fill-mid' : 'fill-high';
        const salle  = r.id_salle ? ' · Salle ' + r.id_salle : '';

        let btnHtml = '';
        const alreadyIn = selEnf && mesIns_<?= $idx ?>[selEnf] && mesIns_<?= $idx ?>[selEnf].includes(r.id);
        const alreadyWait = selEnf && mesAtt_<?= $idx ?>[selEnf] && mesAtt_<?= $idx ?>[selEnf][r.id];

        if(alreadyIn){
            btnHtml = '<button class="reco-btn" disabled>Déjà inscrit</button>';
        } else if(alreadyWait){
            btnHtml = '<button class="reco-btn" disabled>Déjà en attente</button>';
        } else if(!selEnf){
            btnHtml = '<button class="reco-btn" disabled>Choisir un enfant d\'abord</button>';
        } else {
            btnHtml = '<form method="POST" action="activites.php" style="margin:0;">'
                + '<input type="hidden" name="action" value="inscrire">'
                + '<input type="hidden" name="id_creneau" value="'+r.id+'">'
                + '<input type="hidden" name="id_enfant" value="'+selEnf+'">'
                + '<button type="submit" class="reco-btn">+ Inscrire sur ce créneau</button>'
                + '</form>';
        }

        const card = document.createElement('div');
        card.className = 'reco-card' + (i === 0 ? ' best-match' : '');
        card.innerHTML =
            '<div class="reco-card-title">'+escHtml(r.nom_activite)+'</div>'
            +'<div class="reco-card-meta">'+dateF+'<br>'+r.debut.substring(0,5)+' – '+r.fin.substring(0,5)+escHtml(salle)+'</div>'
            +'<div class="reco-fill-bar"><div class="reco-fill-inner '+fillCls+'" style="width:'+taux+'%"></div></div>'
            +'<div class="reco-places"><strong>'+places+' place'+(places>1?'s':'')+' disponible'+(places>1?'s':'')+'</strong> · '+taux+'% rempli</div>'
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

<!-- CALENDRIER PICKER FR 
     Utilisé pour les filtres de dates dans les recommandations-->
<script>
(function(){
    const MOIS_FR = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

    // État par picker : { cy, cm }
    const _frCalState = {};

    function pad(n){ return n < 10 ? '0'+n : ''+n; }

    function frCalInit(pickerId){
        if(_frCalState[pickerId]) return;
        const now = new Date();
        _frCalState[pickerId] = { cy: now.getFullYear(), cm: now.getMonth()+1 };
        frCalRender(pickerId);
    }

    function frCalRender(pickerId){
        const st = _frCalState[pickerId];

        const monthEl = document.getElementById(pickerId + '-month');
        const gridEl  = document.getElementById(pickerId + '-grid');
        if(!monthEl || !gridEl) return;

        monthEl.textContent = MOIS_FR[st.cm] + ' ' + st.cy;
        gridEl.innerHTML = '';

        ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'].forEach(d => {
            const s = document.createElement('span');
            s.className = 'fr-cal-dl'; s.textContent = d;
            gridEl.appendChild(s);
        });

        const firstDay = new Date(st.cy, st.cm-1, 1).getDay();
        const daysInMonth = new Date(st.cy, st.cm, 0).getDate();
        const hiddenEl = document.getElementById(extractHiddenId(pickerId));
        const selectedVal = hiddenEl ? hiddenEl.value : '';

        for(let e = 0; e < firstDay; e++){
            const s = document.createElement('span');
            s.className = 'fr-cal-dj empty';
            gridEl.appendChild(s);
        }
        for(let d = 1; d <= daysInMonth; d++){
            const ds = st.cy + '-' + pad(st.cm) + '-' + pad(d);
            const s  = document.createElement('span');
            s.textContent = d;
            s.className   = 'fr-cal-dj selectable' + (ds === selectedVal ? ' selected' : '');
            s.onclick = () => frCalSelect(pickerId, ds);
            gridEl.appendChild(s);
        }
    }

    function extractIdx(pickerId){
        const parts = pickerId.split('-');
        return parts[parts.length - 1];
    }

    function extractHiddenId(pickerId){
        const idx = extractIdx(pickerId);
        if(pickerId.includes('-debut-'))   return 'filtre-date-debut-'  + idx;
        if(pickerId.includes('-fin-'))     return 'filtre-date-fin-'    + idx;
        if(pickerId.includes('-exacte-')) return 'filtre-date-exacte-' + idx;
        return '';
    }
    function extractLabelId(pickerId){
        const idx = extractIdx(pickerId);
        if(pickerId.includes('-debut-'))   return 'picker-debut-label-'  + idx;
        if(pickerId.includes('-fin-'))     return 'picker-fin-label-'    + idx;
        if(pickerId.includes('-exacte-')) return 'picker-exacte-label-' + idx;
        return '';
    }

    function frCalSelect(pickerId, ds){
        const hiddenId = extractHiddenId(pickerId);
        const labelId= extractLabelId(pickerId);
        const hiddenEl= document.getElementById(hiddenId);
        const labelEl= document.getElementById(labelId);
        if(hiddenEl) hiddenEl.value = ds;

        const parts = ds.split('-');
        if(labelEl) labelEl.textContent = pad(parseInt(parts[2])) + '/' + pad(parseInt(parts[1])) + '/' + parts[0];

        const dropEl = document.getElementById(pickerId + '-cal');
        if(dropEl) dropEl.classList.remove('open');

        frCalRender(pickerId);
    }

    window.toggleFrCal = function(pickerId){
        frCalInit(pickerId);
const dropEl = document.getElementById(pickerId + '-cal');
        if(!dropEl) return;
     const isOpen = dropEl.classList.contains('open');
        document.querySelectorAll('.fr-cal-dropdown.open').forEach(el => el.classList.remove('open'));
        if(!isOpen) dropEl.classList.add('open');
    };

    window.frCalPrev = function(pickerId){
        frCalInit(pickerId);
        const st = _frCalState[pickerId];
        st.cm--;
        if(st.cm < 1){ st.cm = 12; st.cy--; }
        frCalRender(pickerId);
    };

    window.frCalNext = function(pickerId){
        frCalInit(pickerId);
        const st = _frCalState[pickerId];
        st.cm++;
        if(st.cm > 12){ st.cm = 1; st.cy++; }
        frCalRender(pickerId);
    };

    window.frCalClear = function(pickerId, hiddenId, labelId, defaultLabel){
        const hiddenEl = document.getElementById(hiddenId);
        const labelEl = document.getElementById(labelId);
        if(hiddenEl) hiddenEl.value = '';
        if(labelEl) labelEl.textContent = defaultLabel || '...';
        if(_frCalState[pickerId]) frCalRender(pickerId);
    };

    document.addEventListener('click', function(e){
        if(!e.target.closest('.fr-cal-picker')){
            document.querySelectorAll('.fr-cal-dropdown.open').forEach(el => el.classList.remove('open'));
        }
    });
})();
</script>

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