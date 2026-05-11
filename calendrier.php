<?php
/**
 * calendrier.php — Widget calendrier réutilisable
 * Paramètres attendus via $calParams (array) :
 *   - idx          : identifiant unique (string/int)
 *   - byDate       : tableau [date => [...créneaux]]
 *   - colorByDate  : tableau [date => 'ok'|'wait'|'full']
 *   - initY        : année initiale (int)
 *   - initM        : mois initial (int)
 *   - onSelectDate : nom de la callback JS appelée avec (date, el)
 */
if (!isset($calParams)) return;

$idx         = $calParams['idx'];
$byDate      = $calParams['byDate'];
$colorByDate = $calParams['colorByDate'];
$initY       = $calParams['initY'];
$initM       = $calParams['initM'];
$firstDate   = !empty($byDate) ? min(array_keys($byDate)) : null;
?>

<div class="cal-box" id="cal-container-<?= $idx ?>">
    <?php if ($firstDate): ?>
    <div class="cal-nav">
        <button type="button" onclick="calPrev_<?= $idx ?>()">&#9668;</button>
        <div class="cal-month-label" id="cal-label-<?= $idx ?>"></div>
        <button type="button" onclick="calNext_<?= $idx ?>()">&#9658;</button>
    </div>
    <div class="cal-grid" id="cal-<?= $idx ?>"></div>
    <?php else: ?>
    <div style="text-align:center; color:#aaa; padding:20px; font-size:.85rem;">Aucun créneau planifié</div>
    <?php endif; ?>
</div>

<?php if ($firstDate): ?>
<script>
(function(){
    const CAL_BY_DATE_<?= $idx ?>      = <?= json_encode($byDate) ?>;
    const CAL_COLOR_<?= $idx ?>        = <?= json_encode($colorByDate) ?>;
    const moisFR_<?= $idx ?> = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

    let cy_<?= $idx ?> = <?= $initY ?>;
    let cm_<?= $idx ?> = <?= $initM ?>;

    function pad_<?= $idx ?>(n){ return n < 10 ? '0'+n : ''+n; }

    function renderCal_<?= $idx ?>(){
        document.getElementById('cal-label-<?= $idx ?>').textContent =
            moisFR_<?= $idx ?>[cm_<?= $idx ?>] + ' ' + cy_<?= $idx ?>;
        const g = document.getElementById('cal-<?= $idx ?>');
        g.innerHTML = '';
        ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'].forEach(d => {
            const s = document.createElement('span');
            s.className = 'dl'; s.textContent = d; g.appendChild(s);
        });
        const fd  = new Date(cy_<?= $idx ?>, cm_<?= $idx ?> - 1, 1).getDay();
        const dim = new Date(cy_<?= $idx ?>, cm_<?= $idx ?>, 0).getDate();
        for (let e = 0; e < fd; e++){
            const s = document.createElement('span'); s.className = 'dj empty'; g.appendChild(s);
        }
        for (let d = 1; d <= dim; d++){
            const ds  = cy_<?= $idx ?> + '-' + pad_<?= $idx ?>(cm_<?= $idx ?>) + '-' + pad_<?= $idx ?>(d);
            const has = !!CAL_BY_DATE_<?= $idx ?>[ds];
            const s   = document.createElement('span');
            s.textContent = d;
            if (has){
                const col = CAL_COLOR_<?= $idx ?>[ds] || 'ok';
                s.className = 'dj day-' + col + ' clickable';
                s.title = CAL_BY_DATE_<?= $idx ?>[ds].length + ' créneau(x)';
                s.onclick = () => {
                    document.querySelectorAll('#cal-<?= $idx ?> .dj').forEach(x => x.classList.remove('active'));
                    s.classList.add('active');
                    if (typeof window['calSelectDate_<?= $idx ?>'] === 'function'){
                        window['calSelectDate_<?= $idx ?>'](ds, s);
                    }
                };
            } else {
                s.className = 'dj empty';
            }
            g.appendChild(s);
        }
    }

    window['calPrev_<?= $idx ?>'] = function(){
        cm_<?= $idx ?>--;
        if (cm_<?= $idx ?> < 1){ cm_<?= $idx ?> = 12; cy_<?= $idx ?>--; }
        renderCal_<?= $idx ?>();
    };
    window['calNext_<?= $idx ?>'] = function(){
        cm_<?= $idx ?>++;
        if (cm_<?= $idx ?> > 12){ cm_<?= $idx ?> = 1; cy_<?= $idx ?>++; }
        renderCal_<?= $idx ?>();
    };
    // Alias pour compatibilité avec activites.php
    window['prevMonth_<?= $idx ?>'] = window['calPrev_<?= $idx ?>'];
    window['nextMonth_<?= $idx ?>'] = window['calNext_<?= $idx ?>'];

    renderCal_<?= $idx ?>();
})();
</script>
<?php endif; ?>