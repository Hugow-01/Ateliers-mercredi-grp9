<?php require_once 'php/admin-activites.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Activités - Admin</title>
    <link rel="icon" type="image/png" href="images/logo.png">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/activites.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php require_once 'includes/header-admin.php'; ?>


<div class="container" style="padding-top:30px; padding-bottom:60px;">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-family:'Baloo 2'; font-size:2rem; margin:0;">Gestion des Activités</h2>
        <button onclick="document.getElementById('modal-add').classList.add('active')"
                class="btn btn-primary">+ Nouvelle Activité</button>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
    <?php endif; ?>

    <div style="margin-top:10px;">
        <?php foreach ($activites as $act):
            $nomSlug = preg_replace('/[^a-z0-9]/i', '_', $act['nom']);
            $crsAct = $crByAct[$act['nom']] ?? [];

            // Préparer données calendrier pour cet atelier
            $byDateAct = [];
            $colorDateAct = [];
            foreach ($crsAct as $cr) {
                $byDateAct[$cr['date']][] = $cr;
            }
            foreach ($byDateAct as $date => $crs) {
                $allFull = true; $hasWait = false; $hasOk = false;
                foreach ($crs as $cr) {
                    $pct = $act['capacite'] > 0 ? $cr['nb'] / $act['capacite'] : 1;
                    if ($cr['nb'] < $act['capacite']) {
                        $allFull = false;
                        if ($pct >= 0.8) $hasWait = true;
                        else $hasOk   = true;
                    }
                }
                if ($allFull) $colorDateAct[$date] = 'full';
                elseif ($hasWait) $colorDateAct[$date] = 'wait';
                else $colorDateAct[$date] = 'ok';
            }
            $dates = array_keys($byDateAct); sort($dates);
            $firstD = $dates[0] ?? null;
            $initYAct = $firstD ? (int)date('Y', strtotime($firstD)) : (int)date('Y');
            $initMAct = $firstD ? (int)date('n', strtotime($firstD)) : (int)date('n');
        ?>
        <div class="card" style="margin-bottom:20px;" id="card-<?= $nomSlug ?>">

            <div class="act-card-header">
                <div style="display:flex; align-items:center; gap:12px;">
                    <?php
                        $imgSrc = '';
                        if (!empty($act['image']) && file_exists(__DIR__ . '/' . $act['image'])) {
                            $imgSrc = $act['image'];
                        }
                    ?>
                    <?php if ($imgSrc): ?>
                    <img src="<?= htmlspecialchars($imgSrc) ?>" class="act-thumb"
                         alt="<?= htmlspecialchars($act['nom']) ?>">
                    <?php endif; ?>
                    <div>
                        <h3 class="act-card-title"><?= htmlspecialchars($act['nom']) ?></h3>
                        <div style="margin-top:6px;">
                            <span class="meta-chip chip-cap"><?= $act['capacite'] ?> places/créneau</span>
                            <?php if (!empty($act['theme'])): ?>
                            <span class="meta-chip chip-theme">🏷 <?= htmlspecialchars($act['theme']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($act['tranche_age'])): ?>
                            <span class="meta-chip chip-age"><?= htmlspecialchars($act['tranche_age']) ?> ans</span>
                            <?php endif; ?>
                            <span style="font-size:.78rem; color:#94a3b8; margin-left:8px;">
                                <?= count($crsAct) ?> créneau(x)
                            </span>
                        </div>
                    </div>
                </div>
                <div class="act-card-actions">
                    <button class="btn btn-small btn-edit" onclick="toggleEdit('<?= $nomSlug ?>')">✏ Modifier</button>
                    <form method="POST" enctype="multipart/form-data"
                          onsubmit="return confirm('Supprimer l\'activité « <?= htmlspecialchars(addslashes($act['nom'])) ?> » ?')">
                        <input type="hidden" name="action" value="supprimer_activite">
                        <input type="hidden" name="nom_activite" value="<?= htmlspecialchars($act['nom']) ?>">
                        <button type="submit" class="btn btn-small btn-danger">🗑 Supprimer</button>
                    </form>
                </div>
            </div>

            <?php if (!empty($act['syllabus'])): ?>
            <div class="syllabus-preview"><?= htmlspecialchars($act['syllabus']) ?></div>
            <?php endif; ?>

            <!-- Formulaire de modification -->
            <div class="edit-form" id="edit-<?= $nomSlug ?>">
                <h4 style="margin:0 0 14px; font-family:'Baloo 2'; color:#3730a3; font-size:1rem;">
                    ✏ Modifier « <?= htmlspecialchars($act['nom']) ?> »
                </h4>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action"       value="modifier_activite">
                    <input type="hidden" name="nom_original" value="<?= htmlspecialchars($act['nom']) ?>">
                    <div class="edit-grid">
                        <div>
                            <label>Nom de l'activité *</label>
                            <input type="text" name="nouveau_nom" value="<?= htmlspecialchars($act['nom']) ?>" required>
                        </div>
                        <div>
                            <label>Capacité (enfants/créneau) *</label>
                            <input type="number" name="capacite" min="1" max="200" value="<?= $act['capacite'] ?>" required>
                        </div>
                        <div>
                            <label>Thème</label>
                            <select name="theme">
                                <option value="">— Choisir un thème —</option>
                                <?php foreach (THEMES as $th): ?>
                                <option value="<?= htmlspecialchars($th) ?>"
                                        <?= ($act['theme'] ?? '') === $th ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($th) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Tranche d'âge (ex : 5-12)</label>
                            <input type="text" name="tranche_age" placeholder="ex : 6-12"
                                   pattern="^\d{1,2}-\d{1,2}$"
                                   value="<?= htmlspecialchars($act['tranche_age'] ?? '') ?>">
                        </div>
                        <div class="span2">
                            <label>Image de l'atelier</label>
                            <div class="img-upload-zone">
                                <?php if ($imgSrc): ?>
                                <img src="<?= htmlspecialchars($imgSrc) ?>" class="img-upload-preview"
                                     alt="Image actuelle">
                                <span style="font-size:.78rem; color:#888;">Image actuelle</span>
                                <?php endif; ?>
                                <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp"
                                       style="border:none; padding:0; background:transparent;">
                            </div>
                            <div style="font-size:.75rem; color:#999; margin-top:4px;">
                                Formats acceptés : JPG, PNG, GIF, WebP. Taille max : 5 Mo. Laisser vide pour conserver l'image actuelle.
                            </div>
                        </div>
                        <div class="span2">
                            <label>Description / Syllabus</label>
                            <textarea name="syllabus"><?= htmlspecialchars($act['syllabus'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="edit-form-actions">
                        <button type="button" class="btn btn-small"
                                style="background:#e2e8f0; color:#334155;"
                                onclick="toggleEdit('<?= $nomSlug ?>')">Annuler</button>
                        <button type="submit" class="btn btn-small btn-primary">✔ Enregistrer</button>
                    </div>
                </form>
            </div>

            <!-- Calendrier des créneaux (vue admin) -->
            <?php if ($firstD): ?>
                <div id="admin-slots-<?= $nomSlug ?>" style="margin-top:10px; font-size:.85rem; color:#555;"></div>
                <script>
                (function(){
                    const byDate_adm = <?= json_encode($byDateAct) ?>;
                    const cap_adm = <?= (int)$act['capacite'] ?>;
                    function pad2(n){ return n<10?'0'+n:''+n; }
                    function formatHeure(t){ return t ? t.substring(0,5) : ''; }
                    window['calSelectDate_admin_<?= $nomSlug ?>'] = function(date){
                        const crs= byDate_adm[date] || [];
                        const box= document.getElementById('admin-slots-<?= $nomSlug ?>');
                        if (!crs.length){ box.innerHTML = '<em>Aucun créneau ce jour.</em>'; return; }
                        box.innerHTML = crs.map(cr => {
                            const nb = parseInt(cr.nb);
                            const pct = cap_adm > 0 ? Math.round(nb/cap_adm*100) : 100;
                            const col = nb >= cap_adm ? '#e53e3e' : (pct >= 80 ? '#f59e0b' : '#22c55e');
                            return '<span style="display:inline-block; margin:3px 6px; padding:4px 10px; background:#fff; border:2px solid '+col+'; border-radius:8px; font-size:.8rem;">'
                            + formatHeure(cr.debut) + '–' + formatHeure(cr.fin)
                            + ' <strong>' + nb + '/' + cap_adm + '</strong> (' + pct + '%)'
                            + (cr.id_salle ? ' · Salle ' + cr.id_salle : '')
                            + '</span>';
                        }).join('');
                    };
                })();
                </script>
            <?php endif; ?>

            <!-- Tableau des créneaux -->
            <div class="table-wrapper" style="margin-top:14px;">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Horaire</th>
                            <th>Salle</th>
                            <th>Inscrits</th>
                            <th>Remplissage</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($crsAct as $cr):
                            $taux_cr = $act['capacite'] > 0
                                ? round($cr['nb'] / $act['capacite'] * 100) : 0;
                            $dateAff = date('d/m/Y', strtotime($cr['date']));
                            // Décomposer l'heure debut/fin pour les time pickers
                            $debutH = substr($cr['debut'], 0, 2);
                            $debutM = substr($cr['debut'], 3, 2);
                            $finH = substr($cr['fin'], 0, 2);
                            $finM = substr($cr['fin'], 3, 2);
                        ?>
                        <tr class="cr-row">
                            <td><?= $dateAff ?></td>
                            <td><?= substr($cr['debut'],0,5) ?> – <?= substr($cr['fin'],0,5) ?></td>
                            <td><?= $cr['id_salle']
                                    ? htmlspecialchars($cr['id_salle'])
                                    : '<span style="color:#ccc">—</span>' ?></td>
                            <td><?= $cr['nb'] ?> / <?= $act['capacite'] ?></td>
                            <td><strong><?= $taux_cr ?>%</strong></td>
                            <td>
                                <div class="actions-cell">
                                    <button class="btn btn-small"
                                            style="background:#0ea5e9;color:#fff;"
                                            onclick="toggleCreneauEdit('cr-edit-<?= $cr['id'] ?>')">
                                        ✏ Modifier
                                    </button>
                                    <form method="POST"
                                          onsubmit="return confirm('Supprimer ce créneau ?')">
                                        <input type="hidden" name="action"     value="suppr_creneau">
                                        <input type="hidden" name="id_creneau" value="<?= $cr['id'] ?>">
                                        <button type="submit" class="btn btn-small btn-danger">Supprimer</button>
                                    </form>
                                </div>
                                <!-- Formulaire modification créneau -->
                                <div class="cr-edit-form" id="cr-edit-<?= $cr['id'] ?>">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="modifier_creneau">
                                        <input type="hidden" name="id_creneau" value="<?= $cr['id'] ?>">
                                        <input type="hidden" name="nouvelle_date" id="cr-date-hidden-<?= $cr['id'] ?>" value="<?= $cr['date'] ?>">
                                        <!-- Champs cachés alimentés par les time pickers FR -->
                                        <input type="hidden" name="nouveau_debut" id="cr-debut-hidden-<?= $cr['id'] ?>" value="<?= substr($cr['debut'],0,5) ?>">
                                        <input type="hidden" name="nouveau_fin" id="cr-fin-hidden-<?= $cr['id'] ?>"   value="<?= substr($cr['fin'],0,5) ?>">
                                        <div class="cr-edit-form-row">
                                            <div>
                                                <label>Date</label>
                                                <div class="fr-date-picker" id="cr-picker-<?= $cr['id'] ?>">
                                                    <div class="fr-date-display"
                                                         id="cr-picker-display-<?= $cr['id'] ?>"
                                                         onclick="adminFrCalToggle('<?= $cr['id'] ?>', <?= (int)date('Y', strtotime($cr['date'])) ?>, <?= (int)date('n', strtotime($cr['date'])) ?>)">
                                                        <span id="cr-picker-label-<?= $cr['id'] ?>"><?= date('d/m/Y', strtotime($cr['date'])) ?></span>
                                                    </div>
                                                    <div class="fr-date-dropdown" id="cr-picker-cal-<?= $cr['id'] ?>">
                                                        <div class="fr-date-nav">
                                                            <button type="button" onclick="adminFrCalPrev('<?= $cr['id'] ?>')">&#9668;</button>
                                                            <div class="fr-date-month" id="cr-picker-month-<?= $cr['id'] ?>"></div>
                                                            <button type="button" onclick="adminFrCalNext('<?= $cr['id'] ?>')">&#9658;</button>
                                                        </div>
                                                        <div class="fr-date-grid" id="cr-picker-grid-<?= $cr['id'] ?>"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <label>Début</label>
                                                <div class="fr-time-picker" id="cr-time-debut-<?= $cr['id'] ?>">
                                                    <?= frTimePicker('cr-debut-h-'.$cr['id'], $debutH, 'cr-debut-hidden-'.$cr['id'], 'h', 'cr-debut-m-'.$cr['id']) ?>
                                                    <span class="fr-time-sep">h</span>
                                                    <?= frTimePicker('cr-debut-m-'.$cr['id'], $debutM, 'cr-debut-hidden-'.$cr['id'], 'm', 'cr-debut-h-'.$cr['id']) ?>
                                                </div>
                                            </div>
                                            <div>
                                                <label>Fin</label>
                                                <div class="fr-time-picker" id="cr-time-fin-<?= $cr['id'] ?>">
                                                    <?= frTimePicker('cr-fin-h-'.$cr['id'], $finH, 'cr-fin-hidden-'.$cr['id'], 'h', 'cr-fin-m-'.$cr['id']) ?>
                                                    <span class="fr-time-sep">h</span>
                                                    <?= frTimePicker('cr-fin-m-'.$cr['id'], $finM, 'cr-fin-hidden-'.$cr['id'], 'm', 'cr-fin-h-'.$cr['id']) ?>
                                                </div>
                                            </div>
                                            <div>
                                                <label>Salle</label>
                                                <select name="nouvelle_salle">
                                                    <option value="">— Aucune —</option>
                                                    <?php foreach ($salles as $s): ?>
                                                    <option value="<?= htmlspecialchars($s['id']) ?>"
                                                            <?= $cr['id_salle'] === $s['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($s['id']) ?> · <?= htmlspecialchars($s['batiment']) ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <button type="submit" class="btn btn-small btn-primary">✔ Valider</button>
                                            <button type="button" class="btn btn-small"
                                                    style="background:#e2e8f0;color:#334155;"
                                                    onclick="toggleCreneauEdit('cr-edit-<?= $cr['id'] ?>')">
                                                Annuler
                                            </button>
                                        </div>

                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($crsAct)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center; color:#aaa; padding:12px;">
                                Aucun créneau pour le moment
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Ajout d'un créneau -->
            <form method="POST" class="creneau-add-form" id="add-form-<?= $nomSlug ?>">
                <input type="hidden" name="action" value="ajouter_creneau">
                <input type="hidden" name="nom_activite" value="<?= htmlspecialchars($act['nom']) ?>">
                <input type="hidden" name="date" id="add-date-hidden-<?= $nomSlug ?>">
                <input type="hidden" name="debut" id="add-debut-hidden-<?= $nomSlug ?>" value="08:00">
                <input type="hidden" name="fin" id="add-fin-hidden-<?= $nomSlug ?>"   value="09:00">

                <div class="form-group">
                    <label>Date</label>
                    <div class="fr-date-picker" id="add-picker-<?= $nomSlug ?>">
                        <div class="fr-date-display"
                             id="add-picker-display-<?= $nomSlug ?>"
                             onclick="addFrCalToggle('<?= $nomSlug ?>')">
                            <span id="add-picker-label-<?= $nomSlug ?>">Choisir une date</span>
                        </div>
                        <div class="fr-date-dropdown" id="add-picker-cal-<?= $nomSlug ?>">
                            <div class="fr-date-nav">
                                <button type="button" onclick="addFrCalPrev('<?= $nomSlug ?>')">&#9668;</button>
                                <div class="fr-date-month" id="add-picker-month-<?= $nomSlug ?>"></div>
                                <button type="button" onclick="addFrCalNext('<?= $nomSlug ?>')">&#9658;</button>
                            </div>
                            <div class="fr-date-grid" id="add-picker-grid-<?= $nomSlug ?>"></div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Début</label>
                    <div class="fr-time-picker">
                        <?= frTimePicker('add-debut-h-'.$nomSlug, '08', 'add-debut-hidden-'.$nomSlug, 'h', 'add-debut-m-'.$nomSlug) ?>
                        <span class="fr-time-sep">h</span>
                        <?= frTimePicker('add-debut-m-'.$nomSlug, '00', 'add-debut-hidden-'.$nomSlug, 'm', 'add-debut-h-'.$nomSlug) ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>Fin</label>
                    <div class="fr-time-picker">
                        <?= frTimePicker('add-fin-h-'.$nomSlug, '09', 'add-fin-hidden-'.$nomSlug, 'h', 'add-fin-m-'.$nomSlug) ?>
                        <span class="fr-time-sep">h</span>
                        <?= frTimePicker('add-fin-m-'.$nomSlug, '00', 'add-fin-hidden-'.$nomSlug, 'm', 'add-fin-h-'.$nomSlug) ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>Salle</label>
                    <select name="id_salle">
                        <option value="">— Aucune —</option>
                        <?php foreach ($salles as $s): ?>
                        <option value="<?= htmlspecialchars($s['id']) ?>">
                            <?= htmlspecialchars($s['id']) ?> · <?= htmlspecialchars($s['batiment']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-small btn-primary"
                        onclick="return validateAddForm('<?= $nomSlug ?>')">+ Ajouter créneau</button>
            </form>

        </div>
        <?php endforeach; ?>

        <?php if (empty($activites)): ?>
        <div style="text-align:center; padding:40px; color:#aaa;">Aucune activité. Créez-en une !</div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL : Nouvelle activité -->
<div class="modal-bg" id="modal-add">
    <div class="modal" style="max-width:560px;">
        <span class="close-modal"
              onclick="document.getElementById('modal-add').classList.remove('active')">✕</span>
        <h3 style="font-family:'Baloo 2'; margin-bottom:20px;">Nouvelle Activité</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="ajouter_activite">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div class="form-group" style="grid-column:span 2;">
                    <label>Nom de l'activité *</label>
                    <input type="text" name="nom" placeholder="ex : Atelier Poterie" required>
                </div>
                <div class="form-group">
                    <label>Capacité *</label>
                    <input type="number" name="capacite" min="1" placeholder="ex : 15" required>
                </div>
                <div class="form-group">
                    <label>Tranche d'âge</label>
                    <input type="text" name="tranche_age" placeholder="ex : 6-12" pattern="^\d{1,2}-\d{1,2}$">
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label>Thème</label>
                    <select name="theme">
                        <option value="">— Choisir un thème —</option>
                        <?php foreach (THEMES as $th): ?>
                        <option value="<?= htmlspecialchars($th) ?>"><?= htmlspecialchars($th) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label>Image de l'atelier</label>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp"
                           style="border:none; padding:0; background:transparent;">
                    <div style="font-size:.75rem; color:#999; margin-top:4px;">
                        Formats acceptés : JPG, PNG, GIF, WebP. Taille max : 5 Mo. (Optionnel)
                    </div>
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label>Description</label>
                    <textarea name="syllabus" rows="4" placeholder="Décrivez l'activité…"></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"
                    style="width:100%; padding:12px; font-size:1.05rem;">
                Créer l'activité
            </button>
        </form>
    </div>
</div>

<?php
/**
 * Génère un <select> pour heure (00–23) ou minutes (00, 05, 10 … 55).
 * $part = 'h' (heures) | 'm' (minutes)
 * Met à jour l'input caché $hiddenId au format HH:MM à chaque changement.
 */
function frTimePicker(string $selectId, string $currentVal, string $hiddenId, string $part, string $siblingId): string {
    $padVal = str_pad((string)(int)$currentVal, 2, '0', STR_PAD_LEFT);
    $opts = '';
    if ($part === 'h') {
        for ($i = 0; $i <= 23; $i++) {
            $v = str_pad((string)$i, 2, '0', STR_PAD_LEFT);
            $opts .= '<option value="'.$v.'"'.($v === $padVal ? ' selected' : '').'>'.$v.'</option>';
        }
    } else {
        foreach ([0,5,10,15,20,25,30,35,40,45,50,55] as $i) {
            $v = str_pad((string)$i, 2, '0', STR_PAD_LEFT);
            // Choisir la valeur la plus proche si $currentVal n'est pas un multiple de 5
            $closest = (string)str_pad((string)(round((int)$currentVal / 5) * 5 % 60), 2, '0', STR_PAD_LEFT);
            $opts .= '<option value="'.$v.'"'.($v === $closest ? ' selected' : '').'>'.$v.'</option>';
        }
    }
    $typeAttr = $part === 'h' ? 'h' : 'm';
    return '<select id="'.$selectId.'" data-hidden="'.$hiddenId.'" data-part="'.$typeAttr.'" data-sibling="'.$siblingId.'"
        onchange="frTimeSyncHidden(this)">'.$opts.'</select>';
}
?>

<script>
/* ── Synchronisation time picker FR → input caché ── */
function frTimeSyncHidden(sel) {
    const hiddenEl= document.getElementById(sel.dataset.hidden);
    const siblingEl = document.getElementById(sel.dataset.sibling);
    if (!hiddenEl || !siblingEl) return;

    let h, m;
    if (sel.dataset.part === 'h') {
        h= sel.value;
        m = siblingEl.value;
    } else {
        h = siblingEl.value;
        m= sel.value;
    }
    hiddenEl.value = h + ':' + m;
}

/* ══════════════════════════════════════════════════════
   CALENDRIER FR PICKER — Admin
   ══════════════════════════════════════════════════════ */
(function(){
    const MOIS = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

    function pad(n){ return n < 10 ? '0'+n : ''+n; }

    const _crState = {};
    const _addState = {};

    function renderGrid(gridEl, cy, cm, currentVal, onSelect){
        gridEl.innerHTML = '';
        ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'].forEach(d => {
            const s = document.createElement('span');
            s.className = 'fr-dl'; s.textContent = d;
            gridEl.appendChild(s);
        });
        const firstDay = new Date(cy, cm-1, 1).getDay();
        const daysInMonth = new Date(cy, cm, 0).getDate();
        for(let e = 0; e < firstDay; e++){
            const s = document.createElement('span');
            s.className = 'fr-dj empty'; gridEl.appendChild(s);
        }
        for(let d = 1; d <= daysInMonth; d++){
            const ds = cy + '-' + pad(cm) + '-' + pad(d);
            const s = document.createElement('span');
            s.textContent = d;
            s.className = 'fr-dj sel-day' + (ds === currentVal ? ' chosen' : '');
            s.onclick = () => onSelect(ds, d);
            gridEl.appendChild(s);
        }
    }

    /* ══ Pickers "modification créneau" ══ */
    window.adminFrCalToggle = function(crId, initY, initM){
        if(!_crState[crId]){
            _crState[crId] = {
                cy: initY || new Date().getFullYear(),
                cm: initM || new Date().getMonth()+1
            };
        }
        const drop = document.getElementById('cr-picker-cal-'+crId);
        const isOpen = drop.classList.contains('open');
        document.querySelectorAll('.fr-date-dropdown.open').forEach(el => el.classList.remove('open'));
        if(!isOpen){
            drop.classList.add('open');
            renderCrCal(crId);
        }
    };

    function renderCrCal(crId){
        const st = _crState[crId];
        const monthEl = document.getElementById('cr-picker-month-'+crId);
        const gridEl  = document.getElementById('cr-picker-grid-'+crId);
        const hiddenEl= document.getElementById('cr-date-hidden-'+crId);
        if(!monthEl || !gridEl) return;
        monthEl.textContent = MOIS[st.cm] + ' ' + st.cy;
        renderGrid(gridEl, st.cy, st.cm, hiddenEl ? hiddenEl.value : '', function(ds){
            if(hiddenEl) hiddenEl.value = ds;
            const lbl = document.getElementById('cr-picker-label-'+crId);
            if(lbl){ const p=ds.split('-'); lbl.textContent=pad(parseInt(p[2]))+'/'+pad(parseInt(p[1]))+'/'+p[0]; }
            document.getElementById('cr-picker-cal-'+crId).classList.remove('open');
            renderCrCal(crId);
        });
    }

    window.adminFrCalPrev = function(crId){
        if(!_crState[crId]) return;
        _crState[crId].cm--;
        if(_crState[crId].cm < 1){ _crState[crId].cm = 12; _crState[crId].cy--; }
        renderCrCal(crId);
    };
    window.adminFrCalNext = function(crId){
        if(!_crState[crId]) return;
        _crState[crId].cm++;
        if(_crState[crId].cm > 12){ _crState[crId].cm = 1; _crState[crId].cy++; }
        renderCrCal(crId);
    };

    /* ══ Pickers "ajout créneau" ══ */
    window.addFrCalToggle = function(slug){
        if(!_addState[slug]){
            const now = new Date();
            _addState[slug] = { cy: now.getFullYear(), cm: now.getMonth()+1 };
        }
        const drop = document.getElementById('add-picker-cal-'+slug);
        const isOpen = drop.classList.contains('open');
        document.querySelectorAll('.fr-date-dropdown.open').forEach(el => el.classList.remove('open'));
        if(!isOpen){
            drop.classList.add('open');
            renderAddCal(slug);
        }
    };

    function renderAddCal(slug){
        const st = _addState[slug];
        const monthEl = document.getElementById('add-picker-month-'+slug);
        const gridEl  = document.getElementById('add-picker-grid-'+slug);
        const hiddenEl= document.getElementById('add-date-hidden-'+slug);
        if(!monthEl || !gridEl) return;
        monthEl.textContent = MOIS[st.cm] + ' ' + st.cy;
        renderGrid(gridEl, st.cy, st.cm, hiddenEl ? hiddenEl.value : '', function(ds){
            if(hiddenEl) hiddenEl.value = ds;
            const lbl = document.getElementById('add-picker-label-'+slug);
            if(lbl){ const p=ds.split('-'); lbl.textContent=pad(parseInt(p[2]))+'/'+pad(parseInt(p[1]))+'/'+p[0]; }
            document.getElementById('add-picker-cal-'+slug).classList.remove('open');
            renderAddCal(slug);
        });
    }

    window.addFrCalPrev = function(slug){
        if(!_addState[slug]) return;
        _addState[slug].cm--;
        if(_addState[slug].cm < 1){ _addState[slug].cm = 12; _addState[slug].cy--; }
        renderAddCal(slug);
    };
    window.addFrCalNext = function(slug){
        if(!_addState[slug]) return;
        _addState[slug].cm++;
        if(_addState[slug].cm > 12){ _addState[slug].cm = 1; _addState[slug].cy++; }
        renderAddCal(slug);
    };

    window.validateAddForm = function(slug){
        const hidden = document.getElementById('add-date-hidden-'+slug);
        if(!hidden || !hidden.value){
            alert('Veuillez sélectionner une date dans le calendrier.');
            return false;
        }
        return true;
    };

    document.addEventListener('click', function(e){
        if(!e.target.closest('.fr-date-picker')){
            document.querySelectorAll('.fr-date-dropdown.open').forEach(el => el.classList.remove('open'));
        }
    });
})();

/* ══ Bascule formulaires ══ */
function toggleEdit(slug) {
    const form = document.getElementById('edit-' + slug);
    form.classList.toggle('open');
    if (form.classList.contains('open')) {
        setTimeout(() => form.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 60);
    }
}
function toggleCreneauEdit(id) {
    document.getElementById(id).classList.toggle('open');
}
document.querySelectorAll('.modal-bg').forEach(m =>
    m.addEventListener('click', e => { if (e.target === m) m.classList.remove('active'); })
);
</script>
</body>
</html>