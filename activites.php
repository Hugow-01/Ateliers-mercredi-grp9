<?php require_once 'php/activites.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Activités - Ateliers du Mercredi</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/activites.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<header style="background:#fdf6d8; padding:12px 50px; display:flex; justify-content:space-between; align-items:center;">
    <h1 style="font-size:2rem; font-weight:900; margin:0;">Les activités</h1>
    <nav>
        <a href="index.php">Accueil</a>
        <a href="parent-enfants.php">Mon espace</a>
        <a href="deconnexion.php" style="color:#c0392b;">se déconnecter</a>
    </nav>
</header>

<div style="text-align:center; padding:35px 20px 10px;">
    <h1 style="color:#1a5fb4; font-size:2.8rem; font-weight:900; font-family:'Baloo 2';">NOS ACTIVITÉS DU MERCREDI</h1>
    <p style="color:#d4ac0d; font-style:italic; font-size:1.3rem; margin-top:8px;">crée, explore, imagine, chaque semaine</p>
</div>

<?php if ($message): ?>
<div style="max-width:780px; margin:12px auto;">
    <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
</div>
<?php endif; ?>

<?php if (empty($enfants)): ?>
<div style="text-align:center; background:#fff3cd; border:1px solid #ffc107; padding:15px; max-width:600px; margin:15px auto; border-radius:10px;">
     Aucun enfant enregistré. <a href="ajouter-enfant.php" style="color:#ff5e78; font-weight:bold;">Ajouter un enfant</a>.
</div>
<?php endif; ?>

<div class="legend" style="max-width:1200px; margin:0 auto 4px; padding:0 20px;">
    <span><span class="legend-dot dot-ok"></span> Place disponible</span>
    <span><span class="legend-dot dot-wait"></span> Presque complet</span>
    <span><span class="legend-dot dot-full"></span> Complet — liste d'attente possible</span>
</div>

<div class="search-container" style="max-width:1200px; margin:0 auto; padding:0 20px 10px;">
    <input type="text" id="searchBar" class="search-bar" placeholder="  rechercher une activité...">
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

    // Préparer les recommandations par créneau pour cet atelier
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
        <span class="arrow" style="font-size:1rem;">▶</span>
        <div>
            <h2 style="margin:0; font-family:'Baloo 2'; font-size:1.4rem;"><?= htmlspecialchars($act['nom']) ?></h2>
            <div style="font-size:.82rem; color:#888;">Capacité : <?= $act['capacite'] ?> places · <?= count($creneaux) ?> créneau(x)</div>
        </div>
    </summary>

    <div class="act-grid">
        <img src="<?= getImg($act['nom'], $imgMap) ?>" class="activity-img" alt="<?= htmlspecialchars($act['nom']) ?>">

        <div>
            <h3 style="margin-top:0; font-size:1.15rem;"><?= htmlspecialchars($act['nom']) ?></h3>
            <p style="color:#555; font-size:.88rem; line-height:1.6;"><?= nl2br(htmlspecialchars($act['syllabus'])) ?></p>
            <p style="font-size:.78rem; color:#888;"><strong>Capacité :</strong> <?= $act['capacite'] ?> enfants/créneau</p>
        </div>

        <div class="cal-box">
            <?php if ($firstDate): ?>
            <div class="cal-nav">
                <button onclick="prevMonth_<?= $idx ?>()">◀</button>
                <div class="cal-month-label" id="cal-label-<?= $idx ?>"></div>
                <button onclick="nextMonth_<?= $idx ?>()">▶</button>
            </div>
            <div class="cal-grid" id="cal-<?= $idx ?>"></div>
            <?php else: ?>
            <div style="text-align:center; color:#aaa; padding:20px; font-size:.85rem;">Aucun créneau planifié</div>
            <?php endif; ?>
        </div>

        <div class="inscr-box">
            <div style="font-size:11px; font-weight:bold; color:#555; margin-bottom:3px;">Horaires disponibles :</div>

            <div class="slot-list" id="slots-<?= $idx ?>">
                <div style="text-align:center; color:#aaa; font-size:.8rem; padding:10px;">← Choisissez une date</div>
            </div>

            <div class="attente-info" id="attente-info-<?= $idx ?>"></div>

            <?php foreach ($creneaux as $idx => $creneau): ?>
            <?php endforeach; ?>
            <form method="POST" action="activites.php" >
                <input type="hidden" name="action" value="inscrire">
                <input type="hidden" name="id_creneau" id="creneau-<?= $idx ?>" value="">
                <div class="enfants-list">
                    <?php foreach ($enfants as $enfant): ?>
                    <label>
                        <input type="checkbox" name="id_enfants[]" value="<?= $enfant['id'] ?>">
                        <?= htmlspecialchars($enfant['prenom']) ?>
                    </label><br>
                    <?php endforeach; ?>
                </div>
                <!--<button name="action" value="inscrire">Inscrire</button>
                <button name="action" value="desinscrire">Désinscrire</button>
                <button name="action" value="quitter_attente">Quitter liste d'attente</button>-->
                <button name="action" value="inscrire" type="submit" class="btn-inscr" id="btn-inscr-<?= $idx ?>">+ Inscrire</button>
                <button name="action" value="attente" type="submit" class="btn-wait"  id="btn-wait-<?= $idx ?>"> Rejoindre la liste d'attente</button>
            </form>
            

            <form method="POST" action="activites.php" onsubmit="return confirm('Se désinscrire de ce créneau ?')">
                <input type="hidden" name="action"     value="desinscrire">
                <input type="hidden" name="id_creneau" id="des-creneau-<?= $idx ?>" value="">
                <input type="hidden" name="id_enfants[]"  id="des-enfant-<?= $idx ?>"  value="">
                <button type="submit" class="btn-desins" id="btn-des-<?= $idx ?>">✕ Se désinscrire</button>
            </form>

            <form method="POST" action="activites.php" onsubmit="return confirm('Quitter la liste d\'attente ?')">
                <input type="hidden" name="action"     value="quitter_attente">
                <input type="hidden" name="id_creneau" id="quit-creneau-<?= $idx ?>" value="">
                <input type="hidden" name="id_enfants[]"  id="quit-enfant-<?= $idx ?>"  value="">
                <button type="submit" class="btn-quitter" id="btn-quit-<?= $idx ?>">✕ Quitter la liste d'attente</button>
            </form>
        </div>
    </div>

    <!-- ══ PANNEAU DE RECOMMANDATIONS (hors grille, pleine largeur) ══ -->
    <div class="reco-panel" id="reco-<?= $idx ?>">
        <div class="reco-header">
            <div class="reco-header-text">
                <h4>Créneaux alternatifs disponibles</h4>
                <p id="reco-subtitle-<?= $idx ?>"></p>
            </div>
        </div>
        <div class="reco-grid" id="reco-grid-<?= $idx ?>">
            <div class="reco-empty">Sélectionnez un créneau complet pour voir les suggestions.</div>
        </div>
    </div>

</details>

<?php if ($firstDate): ?>
<script>
(function(){
/* ── Données PHP → JS ── */
const byDate_<?= $idx ?>      = <?= json_encode($byDate) ?>;
const colorByDate_<?= $idx ?> = <?= json_encode($colorByDate) ?>;
const mesIns_<?= $idx ?>      = <?= json_encode($mesInscriptions) ?>;
const mesAtt_<?= $idx ?>      = <?= json_encode($mesAttentes) ?>;
const recosByCreneau_<?= $idx ?> = <?= json_encode($recosByCreneauJs) ?>;

/* ── Labels ── */
const moisFR = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
const moisCourt = ['','jan','fév','mar','avr','mai','jun','jul','aoû','sep','oct','nov','déc'];
function pad(n){ return n<10?'0'+n:n; }
function formatDate(ds){ const[y,m,d]=ds.split('-'); return d+'/'+m+'/'+y; }
function formatDateLong(ds){
    const[y,m,d]=ds.split('-');
    const jours=['dim','lun','mar','mer','jeu','ven','sam'];
    const j=new Date(ds).getDay();
    return jours[j]+' '+parseInt(d)+' '+moisCourt[parseInt(m)]+' '+y;
}

/* ── Libellés des raisons ── */
const raisonLabels = {
    'same_activity': { text:'Même atelier', cls:'tag-same-activity' },
    'same_theme':    { text:'Même thème',   cls:'tag-same-theme'    },
    'age_match':     { text:'Âge adapté',   cls:'tag-age-match'     },
    'close_time':    { text:'Horaire proche',cls:'tag-close-time'   },
    'low_fill':      { text:'Peu rempli',   cls:'tag-low-fill'      },
    'similar_name':  { text:'Activité similaire', cls:'tag-similar-name'},
};

let cy=<?= $initY ?>, cm=<?= $initM ?>;

/* ── Calendrier ── */
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
            s.title=byDate_<?= $idx ?>[ds].length+' créneau(x)';
            s.onclick=()=>selectDate(ds,s);
        } else {
            s.className='dj empty';
        }
        g.appendChild(s);
    }
}
window.prevMonth_<?= $idx ?>=function(){cm--;if(cm<1){cm=12;cy--;}renderCal();};
window.nextMonth_<?= $idx ?>=function(){cm++;if(cm>12){cm=1;cy++;}renderCal();};

/* ── Sélection de date ── */
let lastDate=null;
function selectDate(date,el){
    lastDate=date;
    document.querySelectorAll('#cal-<?= $idx ?> .dj').forEach(d=>d.classList.remove('active'));
    el.classList.add('active');
    renderSlots(date);
}

/* ── Rendu des créneaux ── */
function renderSlots(date){
    const crs=byDate_<?= $idx ?>[date]||[];
    const list=document.getElementById('slots-<?= $idx ?>');
    const selEnf=parseInt(document.getElementById('sel-<?= $idx ?>').value)||0;
    list.innerHTML='';
    resetActions();
    hideReco();
    if(!crs.length){
        list.innerHTML='<div style="text-align:center;color:#aaa;font-size:.8rem;padding:10px;">Aucun créneau ce jour</div>';
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
        const salle=cr.salle_id?' · <strong>Salle '+cr.salle_id+'</strong>':'';

        const div=document.createElement('div');
        let cls='slot-item ';
        if(confirme)      cls+='inscrit';
        else if(enAtt)    cls+='en-attente';
        else if(full)     cls+='full';
        else if(quasi)    cls+='wait';
        else              cls+='ok';

        let badge='';
        if(confirme)      badge='<span class="badge badge-conf">Inscrit</span>';
        else if(enAtt)    badge='<span class="badge badge-att"> File #'+enAtt+'</span>';
        else if(full)     badge='<span class="badge badge-full">Complet</span>';
        else if(quasi)    badge='<span class="badge badge-wait">Presque complet</span>';
        else              badge='<span class="badge badge-ok">Disponible</span>';

        const restantes=Math.max(0,cap-nb);
        div.className=cls;
        div.innerHTML='<strong>'+cr.debut.substring(0,5)+' – '+cr.fin.substring(0,5)+'</strong>'+salle+badge+'<br>'
            +'<small style="opacity:.7">'+nb+'/'+cap+' inscrits'
            +(full?' · '+cr.nb_attente+' en attente':' · '+restantes+' place'+(restantes>1?'s':'')+' restante'+(restantes>1?'s':''))
            +'</small>';
        div.onclick=()=>selectSlot(cr,div,selEnf);
        list.appendChild(div);
    });
}

/* ── Sélection d'un créneau ── */
function selectSlot(cr,el,selEnf){
    document.querySelectorAll('#slots-<?= $idx ?> .slot-item').forEach(s=>s.classList.remove('selected'));
    el.classList.add('selected');
    const nb=parseInt(cr.nb_inscrits);
    const cap=parseInt(cr.cap_activite);
    const full=nb>=cap;
    const confirme=selEnf&&mesIns_<?= $idx ?>[selEnf]&&mesIns_<?= $idx ?>[selEnf].includes(cr.id);
    const enAtt=selEnf&&mesAtt_<?= $idx ?>[selEnf]&&mesAtt_<?= $idx ?>[selEnf][cr.id];
    resetActions();
    hideReco();
    document.getElementById('creneau-<?= $idx ?>').value=cr.id;

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
        attInfo.textContent=' Cet enfant est en liste d\'attente à la position #'+enAtt+'.';
        attInfo.style.display='block';
        btnI.style.display='none'; btnW.style.display='none';
    } else if(full){
        btnI.style.display='none'; btnW.style.display='block';
        // ── Afficher les recommandations ──
        showReco(cr.id, selEnf);
    } else {
        btnI.style.display='block'; btnW.style.display='none';
    }
}





/* ═══════════════════════════════════════════
   MOTEUR D'AFFICHAGE DES RECOMMANDATIONS
═══════════════════════════════════════════ */
function showReco(creneauId, selEnf){
    const panel = document.getElementById('reco-<?= $idx ?>');
    const grid  = document.getElementById('reco-grid-<?= $idx ?>');
    const sub   = document.getElementById('reco-subtitle-<?= $idx ?>');
    const recos = recosByCreneau_<?= $idx ?>[creneauId] || [];

    // Récupérer l'âge de l'enfant sélectionné pour affichage contextuel
    let enfNom = '';
    if(selEnf){
        const opt = document.querySelector('#sel-<?= $idx ?> option[value="'+selEnf+'"]');
        if(opt) enfNom = opt.textContent.trim();
    }

    sub.textContent = enfNom
        ? 'Ce créneau est complet — voici nos meilleures suggestions pour '+enfNom
        : 'Ce créneau est complet — voici nos meilleures suggestions';

    grid.innerHTML = '';

    if(!recos.length){
        grid.innerHTML = '<div class="reco-empty"> Aucun créneau alternatif disponible pour le moment.<br>Vous pouvez rejoindre la liste d\'attente ci-dessus.</div>';
        panel.style.display = 'block';
        return;
    }

    recos.forEach((r, i) => {
        const taux      = parseInt(r.taux_remplissage) || 0;
        const places    = parseInt(r.places_restantes) || 0;
        const dateF     = formatDateLong(r.date);
        const fillCls   = taux < 40 ? 'fill-low' : taux < 75 ? 'fill-mid' : 'fill-high';
        const isBest    = (i === 0);

        // Tags des raisons
        const tagsHtml = (r.raisons || []).map(reason => {
            const lbl = raisonLabels[reason];
            return lbl ? `<span class="reco-tag ${lbl.cls}">${lbl.text}</span>` : '';
        }).join('');

        // Salle
        const salleHtml = r.id_salle
            ? `<span class="reco-meta-chip chip-room"> Salle ${r.id_salle}</span>`
            : '';

        // Bouton : déjà inscrit / déjà en attente / inscription rapide
        let btnHtml = '';
        const alreadyIn  = selEnf && mesIns_<?= $idx ?>[selEnf] && mesIns_<?= $idx ?>[selEnf].includes(r.id);
        const alreadyWait= selEnf && mesAtt_<?= $idx ?>[selEnf] && mesAtt_<?= $idx ?>[selEnf][r.id];

        if(alreadyIn){
            btnHtml = `<button class="reco-btn" disabled> Déjà inscrit</button>`;
        } else if(alreadyWait){
            btnHtml = `<button class="reco-btn" disabled> Déjà en attente</button>`;
        } else if(!selEnf){
            btnHtml = `<button class="reco-btn" disabled title="Choisissez un enfant d'abord">Choisir un enfant d'abord</button>`;
        } else {
            btnHtml = `
            <form method="POST" action="activites.php" style="margin:0;">
                <input type="hidden" name="action"     value="inscrire">
                <input type="hidden" name="id_creneau" value="${r.id}">
                <input type="hidden" name="id_enfants[]"  value="${selEnf}">
                <button type="submit" class="reco-btn">
                    + Inscrire sur ce créneau
                </button>
            </form>`;
        }

        const card = document.createElement('div');
        card.className = 'reco-card' + (isBest ? ' best-match' : '');
        card.innerHTML = `
            <div class="reco-card-title">${escHtml(r.nom_activite)}</div>
            <div class="reco-card-meta">
                <span class="reco-meta-chip chip-date">  ${dateF}</span>
                <span class="reco-meta-chip chip-time">  ${r.debut.substring(0,5)}  ${r.fin.substring(0,5)}</span>
                ${salleHtml}
            </div>
            <div class="reco-fill-bar">
                <div class="reco-fill-inner ${fillCls}" style="width:${taux}%"></div>
            </div>
            <div class="reco-places">
                <strong>${places} place${places>1?'s':''} disponible${places>1?'s':''}</strong>
                · ${taux}% rempli
            </div>
            <div class="reco-tags">${tagsHtml}</div>
            ${btnHtml}
        `;
        grid.appendChild(card);
    });

    panel.style.display = 'block';
    // Scroll doux vers le panneau
    setTimeout(()=> panel.scrollIntoView({behavior:'smooth', block:'nearest'}), 50);
}

function hideReco(){
    const panel = document.getElementById('reco-<?= $idx ?>');
    panel.style.display = 'none';
}

function escHtml(s){
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ── Réinitialiser les boutons d'action ── */
function resetActions(){
    ['btn-inscr','btn-wait','btn-des','btn-quit'].forEach(id=>
        document.getElementById(id+'-<?= $idx ?>').style.display='none'
    );
    document.getElementById('attente-info-<?= $idx ?>').style.display='none';
}


renderCal();
})();
</script>
<?php endif; ?>
<?php endforeach; ?>

<?php if (empty($activites)): ?>
<div style="text-align:center; padding:60px; color:#aaa;">Aucune activité disponible.</div>
<?php endif; ?>
</div>

<script>
document.getElementById('searchBar').addEventListener('input', function(){
    const t=this.value.toLowerCase();
    document.querySelectorAll('.activity-item').forEach(i=>
        i.style.display=i.querySelector('h2').innerText.toLowerCase().includes(t)?'':'none'
    );
});
</script>
</body>
</html>