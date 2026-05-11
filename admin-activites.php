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
    <style>
.theme-badge { display:inline-block; padding:3px 11px; border-radius:20px; font-size:.75rem; font-weight:700; background:#e0e7ff; color:#3730a3; margin-left:8px; vertical-align:middle; }
.act-card-header { display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:10px; margin-bottom:14px; }
.act-card-title  { font-family:'Baloo 2'; color:#1a5fb4; margin:0; font-size:1.2rem; }
.act-card-actions{ display:flex; gap:8px; flex-shrink:0; }
.syllabus-preview{ background:#f8fafc; border-left:3px solid #e2e8f0; padding:10px 14px; border-radius:0 8px 8px 0; font-size:.85rem; color:#475569; line-height:1.6; margin-bottom:14px; white-space:pre-wrap; }
.act-meta-row { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:14px; font-size:.82rem; }
.meta-chip { padding:4px 12px; border-radius:8px; font-weight:600; }
.chip-cap   { background:#dbeafe; color:#1d4ed8; }
.chip-theme { background:#e0e7ff; color:#3730a3; }
.chip-age   { background:#fce7f3; color:#9d174d; }
.edit-form  { display:none; background:#f0f4ff; border:1.5px solid #c7d2fe; border-radius:14px; padding:20px; margin-bottom:14px; }
.edit-form.open { display:block; }
.edit-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px; }
.edit-grid .span2 { grid-column:span 2; }
.edit-form label { display:block; font-size:.8rem; font-weight:700; color:#475569; margin-bottom:4px; }
.edit-form input, .edit-form select, .edit-form textarea { background:#fff; border:1px solid #c7d2fe; padding:8px 12px; border-radius:8px; font-size:.88rem; width:100%; }
.edit-form textarea { min-height:90px; resize:vertical; }
.edit-form-actions { display:flex; gap:10px; justify-content:flex-end; }
.btn-edit   { background:#6366f1; color:#fff; }
.btn-delete { background:#ef4444; color:#fff; }
.creneau-add-form { display:flex; gap:10px; margin-top:14px; background:#f8fafc; padding:12px; border-radius:12px; flex-wrap:wrap; align-items:flex-end; border:1px dashed #cbd5e1; }
.creneau-add-form .form-group { margin:0; }
.creneau-add-form label { font-size:.78rem; font-weight:700; color:#64748b; display:block; margin-bottom:3px; }
.creneau-add-form input, .creneau-add-form select { width:auto; padding:7px 10px; font-size:.85rem; }
.cr-edit-form { display:none; background:#f0f9ff; border:1px solid #bae6fd; border-radius:10px; padding:14px; margin-top:8px; }
.cr-edit-form.open { display:block; }
.cr-edit-form-row { display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end; }
.cr-edit-form label { font-size:.78rem; font-weight:700; color:#0369a1; display:block; margin-bottom:3px; }
.cr-edit-form input, .cr-edit-form select { padding:7px 10px; font-size:.85rem; border:1px solid #bae6fd; border-radius:8px; }
/* Image upload */
.img-upload-preview { width:80px; height:60px; object-fit:cover; border-radius:8px; border:2px solid #e2e8f0; }
.img-upload-zone { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
.act-thumb { width:60px; height:45px; object-fit:cover; border-radius:6px; border:1px solid #ddd; vertical-align:middle; margin-right:8px; }

/* Calendrier dans l'espace admin — même style que activites.php */
.admin-cal-section {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 16px;
    margin-top: 14px;
}
.admin-cal-section h4 {
    font-family: 'Baloo 2';
    font-size: 1rem;
    color: #1a5fb4;
    margin: 0 0 12px;
}
@media (max-width:700px) {
    .edit-grid { grid-template-columns:1fr; }
    .edit-grid .span2 { grid-column:span 1; }
}
    </style>
</head>
<body>

<header class="admin-header">
    <h1>Administration</h1>
    <nav>
        <a href="admin-dashboard.php">Tableau de bord</a>
        <a href="admin-activites.php" style="text-decoration:underline;">Activités</a>
        <a href="admin-liste-enfants.php">Liste enfants</a>
        <a href="admin-comptes.php">Comptes parents</a>
        <a href="admin-responsables.php">Responsables</a>
        <a href="deconnexion.php" style="color:#c0392b;">Déconnexion</a>
    </nav>
</header>

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
            $crsAct  = $crByAct[$act['nom']] ?? [];

            // Préparer données calendrier pour cet atelier
            $byDateAct    = [];
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
                        else             $hasOk   = true;
                    }
                }
                if ($allFull)     $colorDateAct[$date] = 'full';
                elseif ($hasWait) $colorDateAct[$date] = 'wait';
                else              $colorDateAct[$date] = 'ok';
            }
            $dates    = array_keys($byDateAct); sort($dates);
            $firstD   = $dates[0] ?? null;
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

            <!-- Calendrier des créneaux -->
            <?php if ($firstD): ?>
            <div class="admin-cal-section">
                <h4>Calendrier des créneaux</h4>
                <div class="legend" style="margin-bottom:10px;">
                    <span><span class="legend-dot dot-ok"></span> Disponible</span>
                    <span><span class="legend-dot dot-wait"></span> Presque complet</span>
                    <span><span class="legend-dot dot-full"></span> Complet</span>
                </div>
                <?php
                $calParams = [
                    'idx'        => 'admin_' . $nomSlug,
                    'byDate'     => $byDateAct,
                    'colorByDate'=> $colorDateAct,
                    'initY'      => $initYAct,
                    'initM'      => $initMAct,
                ];
                include __DIR__ . '/php/calendrier.php';
                ?>
                <div id="admin-slots-<?= $nomSlug ?>" style="margin-top:10px; font-size:.85rem; color:#555;"></div>
                <script>
                (function(){
                    const byDate_adm = <?= json_encode($byDateAct) ?>;
                    const cap_adm    = <?= (int)$act['capacite'] ?>;
                    function pad2(n){ return n<10?'0'+n:''+n; }
                    function formatHeure(t){ return t ? t.substring(0,5) : ''; }
                    function formatDateFr(ds){
                        const [y,m,d] = ds.split('-');
                        return pad2(parseInt(d)) + '/' + pad2(parseInt(m)) + '/' + y;
                    }
                    window['calSelectDate_admin_<?= $nomSlug ?>'] = function(date){
                        const crs  = byDate_adm[date] || [];
                        const box  = document.getElementById('admin-slots-<?= $nomSlug ?>');
                        if (!crs.length){ box.innerHTML = '<em>Aucun créneau ce jour.</em>'; return; }
                        box.innerHTML = crs.map(cr => {
                            const nb  = parseInt(cr.nb);
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
            </div>
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
                            // Date en JJ/MM/AAAA
                            $dateAff = date('d/m/Y', strtotime($cr['date']));
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
                                <div class="cr-edit-form" id="cr-edit-<?= $cr['id'] ?>">
                                    <form method="POST">
                                        <input type="hidden" name="action"     value="modifier_creneau">
                                        <input type="hidden" name="id_creneau" value="<?= $cr['id'] ?>">
                                        <div class="cr-edit-form-row">
                                            <div>
                                                <label>Date</label>
                                                <input type="date" name="nouvelle_date"
                                                       value="<?= $cr['date'] ?>" required>
                                            </div>
                                            <div>
                                                <label>Début</label>
                                                <input type="time" name="nouveau_debut"
                                                       value="<?= substr($cr['debut'],0,5) ?>" required>
                                            </div>
                                            <div>
                                                <label>Fin</label>
                                                <input type="time" name="nouveau_fin"
                                                       value="<?= substr($cr['fin'],0,5) ?>" required>
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
                                        <p style="font-size:.75rem; color:#0369a1; margin:8px 0 0;">
                                            ℹ Les enfants déjà inscrits restent inscrits. Les parents seront notifiés.
                                        </p>
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
            <form method="POST" class="creneau-add-form">
                <input type="hidden" name="action"       value="ajouter_creneau">
                <input type="hidden" name="nom_activite" value="<?= htmlspecialchars($act['nom']) ?>">
                <div class="form-group"><label>Date</label><input type="date" name="date" required></div>
                <div class="form-group"><label>Début</label><input type="time" name="debut" required></div>
                <div class="form-group"><label>Fin</label><input type="time" name="fin" required></div>
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
                <button type="submit" class="btn btn-small btn-primary">+ Ajouter créneau</button>
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

<script>
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