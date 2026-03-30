<?php require_once 'php/admin-activites.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Activités - Admin</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
/* ── Badges thème ── */
.theme-badge {
    display: inline-block;
    padding: 3px 11px;
    border-radius: 20px;
    font-size: .75rem;
    font-weight: 700;
    background: #e0e7ff;
    color: #3730a3;
    margin-left: 8px;
    vertical-align: middle;
}
.badge-age {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: .75rem;
    font-weight: 700;
    background: #fce7f3;
    color: #9d174d;
    margin-left: 6px;
    vertical-align: middle;
}

/* ── En-tête carte activité ── */
.act-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 14px;
}
.act-card-title { font-family:'Baloo 2'; color:#1a5fb4; margin: 0; font-size: 1.2rem; }
.act-card-actions { display: flex; gap: 8px; flex-shrink: 0; }

/* ── Syllabus affiché ── */
.syllabus-preview {
    background: #f8fafc;
    border-left: 3px solid #e2e8f0;
    padding: 10px 14px;
    border-radius: 0 8px 8px 0;
    font-size: .85rem;
    color: #475569;
    line-height: 1.6;
    margin-bottom: 14px;
    white-space: pre-wrap;
}

/* ── Métadonnées activité ── */
.act-meta-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 14px;
    font-size: .82rem;
}
.meta-chip {
    padding: 4px 12px;
    border-radius: 8px;
    font-weight: 600;
}
.chip-cap    { background: #dbeafe; color: #1d4ed8; }
.chip-theme  { background: #e0e7ff; color: #3730a3; }
.chip-age    { background: #fce7f3; color: #9d174d; }

/* ── Formulaire d'édition inline ── */
.edit-form {
    display: none;
    background: #f0f4ff;
    border: 1.5px solid #c7d2fe;
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 14px;
    animation: editSlide .2s ease;
}
@keyframes editSlide {
    from { opacity:0; transform:translateY(-6px); }
    to   { opacity:1; transform:translateY(0); }
}
.edit-form.open { display: block; }

.edit-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 14px;
}
.edit-grid .span2 { grid-column: span 2; }
.edit-form label {
    display: block;
    font-size: .8rem;
    font-weight: 700;
    color: #475569;
    margin-bottom: 4px;
}
.edit-form input,
.edit-form select,
.edit-form textarea {
    background: #fff;
    border: 1px solid #c7d2fe;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: .88rem;
    width: 100%;
}
.edit-form textarea { min-height: 90px; resize: vertical; }
.edit-form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

/* ── Boutons petits ── */
.btn-edit    { background: #6366f1; color: #fff; }
.btn-delete  { background: #ef4444; color: #fff; }

/* ── Ligne créneau ── */
.cr-row td { vertical-align: middle; }
.cr-fill-bar {
    width: 80px;
    height: 6px;
    background: #e2e8f0;
    border-radius: 99px;
    overflow: hidden;
    display: inline-block;
    vertical-align: middle;
    margin-left: 6px;
}
.cr-fill-inner { height: 100%; border-radius: 99px; }
.cr-fill-low  { background: #22c55e; }
.cr-fill-mid  { background: #f59e0b; }
.cr-fill-high { background: #ef4444; }

/* ── Formulaire ajout créneau ── */
.creneau-add-form {
    display: flex;
    gap: 10px;
    margin-top: 14px;
    background: #f8fafc;
    padding: 12px;
    border-radius: 12px;
    flex-wrap: wrap;
    align-items: flex-end;
    border: 1px dashed #cbd5e1;
}
.creneau-add-form .form-group { margin: 0; }
.creneau-add-form label { font-size: .78rem; font-weight: 700; color: #64748b; display:block; margin-bottom:3px; }
.creneau-add-form input,
.creneau-add-form select { width: auto; padding: 7px 10px; font-size: .85rem; }

@media (max-width: 700px) {
    .edit-grid { grid-template-columns: 1fr; }
    .edit-grid .span2 { grid-column: span 1; }
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

    <!-- ══ LISTE DES ACTIVITÉS ══ -->
    <div style="margin-top:10px;">
        <?php foreach ($activites as $act):
            $nomSlug   = preg_replace('/[^a-z0-9]/i', '_', $act['nom']);
            $nbCreneaux= count($crByAct[$act['nom']] ?? []);
            $taux      = $act['capacite'] > 0 ? round(
                collect_avg_fill($db ?? null, $crByAct[$act['nom']] ?? [], $act['capacite'])
            ) : 0;
        ?>
        <div class="card" style="margin-bottom:20px;" id="card-<?= $nomSlug ?>">

            <!-- En-tête de la carte -->
            <div class="act-card-header">
                <div>
                    <h3 class="act-card-title">
                        <?= htmlspecialchars($act['nom']) ?>
                    </h3>
                    <div style="margin-top:6px;">
                        <span class="meta-chip chip-cap"> <?= $act['capacite'] ?> places/créneau</span>
                        <?php if ($act['theme']): ?>
                        <span class="meta-chip chip-theme">🏷 <?= htmlspecialchars($act['theme']) ?></span>
                        <?php endif; ?>
                        <?php if ($act['tranche_age']): ?>
                        <span class="meta-chip chip-age"> <?= htmlspecialchars($act['tranche_age']) ?> ans</span>
                        <?php endif; ?>
                        <span style="font-size:.78rem; color:#94a3b8; margin-left:8px;"><?= $nbCreneaux ?> créneau(x)</span>
                    </div>
                </div>
                <div class="act-card-actions">
                    <button class="btn btn-small btn-edit"
                            onclick="toggleEdit('<?= $nomSlug ?>')">
                         Modifier
                    </button>
                    <form method="POST"
                          onsubmit="return confirm('Supprimer l\'activité « <?= htmlspecialchars(addslashes($act['nom'])) ?> » ?\nAttention : tous ses créneaux doivent être supprimés au préalable.')">
                        <input type="hidden" name="action"       value="supprimer_activite">
                        <input type="hidden" name="nom_activite" value="<?= htmlspecialchars($act['nom']) ?>">
                        <button type="submit" class="btn btn-small btn-danger">🗑 Supprimer</button>
                    </form>
                </div>
            </div>

            <!-- Description -->
            <?php if ($act['syllabus']): ?>
            <div class="syllabus-preview"><?= htmlspecialchars($act['syllabus']) ?></div>
            <?php endif; ?>

            <!-- ── Formulaire d'édition inline ── -->
            <div class="edit-form" id="edit-<?= $nomSlug ?>">
                <h4 style="margin:0 0 14px; font-family:'Baloo 2'; color:#3730a3; font-size:1rem;">
                     Modifier « <?= htmlspecialchars($act['nom']) ?> »
                </h4>
                <form method="POST">
                    <input type="hidden" name="action"       value="modifier_activite">
                    <input type="hidden" name="nom_original" value="<?= htmlspecialchars($act['nom']) ?>">

                    <div class="edit-grid">
                        <!-- Nom -->
                        <div>
                            <label>Nom de l'activité *</label>
                            <input type="text" name="nouveau_nom"
                                   value="<?= htmlspecialchars($act['nom']) ?>" required>
                        </div>

                        <!-- Capacité -->
                        <div>
                            <label>Capacité (enfants/créneau) *</label>
                            <input type="number" name="capacite" min="1" max="200"
                                   value="<?= $act['capacite'] ?>" required>
                        </div>

                        <!-- Thème -->
                        <div>
                            <label>Thème</label>
                            <select name="theme">
                                <option value="">— Choisir un thème —</option>
                                <?php foreach (THEMES as $th): ?>
                                <option value="<?= htmlspecialchars($th) ?>"
                                        <?= $act['theme'] === $th ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($th) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Tranche d'âge -->
                        <div>
                            <label>Tranche d'âge (ex : 5-12)</label>
                            <input type="text" name="tranche_age"
                                   placeholder="ex : 6-12"
                                   pattern="^\d{1,2}-\d{1,2}$"
                                   title="Format attendu : min-max, ex: 6-12"
                                   value="<?= htmlspecialchars($act['tranche_age']) ?>">
                        </div>

                        <!-- Syllabus -->
                        <div class="span2">
                            <label>Description / Syllabus</label>
                            <textarea name="syllabus"><?= htmlspecialchars($act['syllabus']) ?></textarea>
                        </div>
                    </div>

                    <div class="edit-form-actions">
                        <button type="button" class="btn btn-small"
                                style="background:#e2e8f0; color:#334155;"
                                onclick="toggleEdit('<?= $nomSlug ?>')">
                            Annuler
                        </button>
                        <button type="submit" class="btn btn-small btn-primary">
                             Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>

            <!-- ── Tableau des créneaux ── -->
            <div class="table-wrapper" style="margin-top:6px;">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Horaire</th>
                            <th>Salle</th>
                            <th>Inscrits</th>
                            <th>Remplissage</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($crByAct[$act['nom']] ?? []) as $cr):
                            $taux_cr = $act['capacite'] > 0 ? round($cr['nb'] / $act['capacite'] * 100) : 0;
                            $fillCls = $taux_cr < 40 ? 'cr-fill-low' : ($taux_cr < 75 ? 'cr-fill-mid' : 'cr-fill-high');
                        ?>
                        <tr class="cr-row">
                            <td><?= htmlspecialchars($cr['date']) ?></td>
                            <td><?= substr($cr['debut'],0,5) ?> – <?= substr($cr['fin'],0,5) ?></td>
                            <td><?= $cr['id_salle'] ? htmlspecialchars($cr['id_salle']) : '<span style="color:#ccc">—</span>' ?></td>
                            <td><?= $cr['nb'] ?> / <?= $act['capacite'] ?></td>
                            <td>
                                <?= $taux_cr ?>%
                                <span class="cr-fill-bar">
                                    <span class="cr-fill-inner <?= $fillCls ?>"
                                          style="width:<?= $taux_cr ?>%"></span>
                                </span>
                            </td>
                            <td>
                                <form method="POST"
                                      onsubmit="return confirm('Supprimer ce créneau ?')">
                                    <input type="hidden" name="action"     value="suppr_creneau">
                                    <input type="hidden" name="id_creneau" value="<?= $cr['id'] ?>">
                                    <button type="submit" class="btn btn-small btn-danger">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($crByAct[$act['nom']])): ?>
                        <tr>
                            <td colspan="6" style="text-align:center; color:#aaa; padding:12px;">
                                Aucun créneau pour le moment
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ── Ajout d'un créneau ── -->
            <form method="POST" class="creneau-add-form">
                <input type="hidden" name="action"       value="ajouter_creneau">
                <input type="hidden" name="nom_activite" value="<?= htmlspecialchars($act['nom']) ?>">

                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="date" required>
                </div>
                <div class="form-group">
                    <label>Début</label>
                    <input type="time" name="debut" required>
                </div>
                <div class="form-group">
                    <label>Fin</label>
                    <input type="time" name="fin" required>
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
                <button type="submit" class="btn btn-small btn-primary">+ Ajouter créneau</button>
            </form>

        </div>
        <?php endforeach; ?>

        <?php if (empty($activites)): ?>
        <div style="text-align:center; padding:40px; color:#aaa;">
            Aucune activité. Créez-en une !
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ══ MODAL : Nouvelle activité ══ -->
<div class="modal-bg" id="modal-add">
    <div class="modal" style="max-width:540px;">
        <span class="close-modal"
              onclick="document.getElementById('modal-add').classList.remove('active')">✕</span>
        <h3 style="font-family:'Baloo 2'; margin-bottom:20px;">Nouvelle Activité</h3>
        <form method="POST">
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
                    <input type="text" name="tranche_age" placeholder="ex : 6-12"
                           pattern="^\d{1,2}-\d{1,2}$" title="Format : min-max">
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
                    <label>Description</label>
                    <textarea name="syllabus" rows="4"
                              placeholder="Décrivez l'activité, le matériel fourni…"></textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; font-size:1.05rem;">
                Créer l'activité
            </button>
        </form>
    </div>
</div>

<script>
/* ── Toggle formulaire d'édition inline ── */
function toggleEdit(slug) {
    const form = document.getElementById('edit-' + slug);
    form.classList.toggle('open');
    // Scroll vers le formulaire si on l'ouvre
    if (form.classList.contains('open')) {
        setTimeout(() => form.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 60);
    }
}

/* ── Fermer le modal en cliquant hors de lui ── */
document.querySelectorAll('.modal-bg').forEach(m =>
    m.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    })
);
</script>
</body>
</html>

<?php
// ── Helper taux moyen de remplissage (utilisé dans la vue) ──
function collect_avg_fill($db, array $creneaux, int $capacite): float {
    if (!$creneaux || !$capacite) return 0;
    $total = array_sum(array_column($creneaux, 'nb'));
    return ($total / (count($creneaux) * $capacite)) * 100;
}
?>
