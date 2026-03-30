<?php require_once 'php/admin-liste-enfants.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des enfants - Admin</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* ── Badges statut ── */
        .badge-accepte {
            background: #d4edda; color: #155724;
            padding: 4px 12px; border-radius: 8px;
            font-weight: bold; font-size: .82rem;
            white-space: nowrap;
        }
        .badge-attente {
            background: #ffe0e0; color: #c0392b;
            padding: 4px 12px; border-radius: 8px;
            font-weight: bold; font-size: .82rem;
            white-space: nowrap;
        }
        .badge-pos {
            background: #fff3cd; color: #856404;
            padding: 2px 8px; border-radius: 6px;
            font-size: .75rem; margin-left: 6px;
        }

        /* ── Bouton bascule ── */
        .btn-toggle {
            border: none; border-radius: 8px;
            padding: 5px 13px; font-size: .78rem;
            font-weight: bold; cursor: pointer;
            transition: opacity .15s;
            white-space: nowrap;
        }
        .btn-toggle:hover { opacity: .8; }
        .btn-to-attente {
            background: #ffc107; color: #212529;
        }
        .btn-to-accepte {
            background: #28a745; color: #fff;
        }

        /* ── Filtre statut ── */
        .filtre-statut-btns {
            display: flex; gap: 8px; align-items: center; flex-wrap: wrap;
        }
        .fs-btn {
            padding: 7px 18px; border-radius: 20px; border: 2px solid #ddd;
            background: white; font-weight: bold; font-size: .88rem;
            cursor: pointer; transition: .2s;
        }
        .fs-btn.active-tous     { border-color: #1a5fb4; background:#e8f0fe; color:#1a5fb4; }
        .fs-btn.active-accepte  { border-color: #28a745; background:#d4edda; color:#155724; }
        .fs-btn.active-attente  { border-color: #c0392b; background:#ffe0e0; color:#c0392b; }

        /* ── Compteurs ── */
        .count-bar {
            display: flex; gap: 20px; margin-bottom: 14px; flex-wrap: wrap; font-size: .88rem;
        }
        .count-chip {
            padding: 4px 14px; border-radius: 12px; font-weight: bold;
        }
        .chip-total   { background: #e8f0fe; color: #1a5fb4; }
        .chip-accepte { background: #d4edda; color: #155724; }
        .chip-attente { background: #ffe0e0; color: #c0392b; }

        /* ── Colonne actions ── */
        td.action-cell { white-space: nowrap; }

        /* ── Légende ── */
        .legend-inline { font-size: .78rem; color: #888; margin-left: 10px; }
    </style>
</head>
<body>

<header class="admin-header">
    <h1>Espace administrateur</h1>
    <nav>
        <a href="admin-dashboard.php">tableau de bord</a>
        <a href="admin-liste-enfants.php" style="text-decoration:underline;">liste des enfants</a>
        <a href="admin-activites.php">activités</a>
        <a href="admin-inscription-parents.php">inscriptions</a>
        <a href="deconnexion.php" style="color:#c0392b;">se déconnecter</a>
    </nav>
</header>

<div class="container" style="padding-top:30px; padding-bottom:60px;">
    <h2 style="font-family:'Baloo 2'; font-size:2rem; margin-bottom:20px;">
        Liste des enfants par activité
    </h2>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
    <?php endif; ?>

    <!-- ══ Filtres ══ -->
    <form method="GET" style="display:flex; gap:15px; align-items:flex-end; margin-bottom:20px; flex-wrap:wrap;">
        <div class="form-group" style="flex:1; min-width:200px; margin-bottom:0;">
            <label>Activité</label>
            <select name="activite">
                <option value="">Toutes les activités</option>
                <?php foreach ($activites as $a): ?>
                <option value="<?= htmlspecialchars($a['nom']) ?>"
                        <?= $filtreActivite === $a['nom'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['nom']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="flex:1; min-width:160px; margin-bottom:0;">
            <label>Date</label>
            <input type="date" name="date" value="<?= htmlspecialchars($filtreDate) ?>">
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label>Statut</label>
            <select name="statut">
                <option value=""       <?= $filtreStatut === ''        ? 'selected':'' ?>>Tous</option>
                <option value="accepte"<?= $filtreStatut === 'accepte' ? 'selected':'' ?>>Accepté uniquement</option>
                <option value="attente"<?= $filtreStatut === 'attente' ? 'selected':'' ?>>Liste d'attente uniquement</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-small">Filtrer</button>
        <a href="admin-liste-enfants.php" class="btn btn-small" style="background:#eee; color:#333;">Réinitialiser</a>
    </form>

    <!-- ══ Compteurs ══ -->
    <?php
        $nbTotal   = count($inscriptions);
        $nbAccepte = count(array_filter($inscriptions, fn($r) => $r['statut_type'] === 'accepte'));
        $nbAttente = count(array_filter($inscriptions, fn($r) => $r['statut_type'] === 'attente'));
    ?>
    <div class="count-bar">
        <span class="count-chip chip-total">Total affiché : <?= $nbTotal ?></span>
        <span class="count-chip chip-accepte">✔ Acceptés : <?= $nbAccepte ?></span>
        <span class="count-chip chip-attente"> En attente : <?= $nbAttente ?></span>
    </div>

    <!-- ══ Tableau ══ -->
    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Âge</th>
                        <th>Famille (login)</th>
                        <th>Activité</th>
                        <th>Date</th>
                        <th>Horaire</th>
                        <th>Statut</th>
                        <th>Action admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($inscriptions)): ?>
                    <tr>
                        <td colspan="9" style="text-align:center; color:#aaa; padding:30px;">
                            Aucun résultat
                        </td>
                    </tr>
                    <?php endif; ?>

                    <?php foreach ($inscriptions as $ins):
                        $isAccepte = ($ins['statut_type'] === 'accepte');
                        $isAttente = ($ins['statut_type'] === 'attente');
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($ins['nom']) ?></td>
                        <td><?= htmlspecialchars($ins['prenom']) ?></td>
                        <td><?= htmlspecialchars($ins['age']) ?> ans</td>
                        <td style="font-size:.82rem; color:#555;"><?= htmlspecialchars($ins['login_famille']) ?></td>
                        <td><?= htmlspecialchars($ins['activite']) ?></td>
                        <td><?= htmlspecialchars($ins['date']) ?></td>
                        <td><?= substr($ins['debut'],0,5) ?> – <?= substr($ins['fin'],0,5) ?></td>

                        <!-- Colonne Statut -->
                        <td>
                            <?php if ($isAccepte): ?>
                                <span class="badge-accepte"> Accepté</span>
                            <?php else: ?>
                                <span class="badge-attente"> Liste d'attente</span>
                                <span class="badge-pos">#<?= htmlspecialchars($ins['position']) ?></span>
                            <?php endif; ?>
                        </td>

                        <!-- Colonne Action -->
                        <td class="action-cell">
                            <?php if ($isAccepte): ?>
                                <!-- Bouton : passer en liste d'attente -->
                                <form method="POST"
                                      onsubmit="return confirm('Déplacer <?= htmlspecialchars(addslashes($ins['prenom'].' '.$ins['nom'])) ?> en liste d\'attente ?\nLa famille sera notifiée par email.')">
                                    <input type="hidden" name="action"     value="basculer_statut">
                                    <input type="hidden" name="id_enfant"  value="<?= $ins['id_enfant'] ?>">
                                    <input type="hidden" name="id_creneau" value="<?= $ins['id_creneau'] ?>">
                                    <input type="hidden" name="direction"  value="vers_attente">
                                    <?php
                                        // Conserver les filtres actifs après POST
                                        foreach (['activite'=>$filtreActivite,'date'=>$filtreDate,'statut'=>$filtreStatut] as $k=>$v):
                                            if ($v): ?>
                                    <input type="hidden" name="<?= $k ?>" value="<?= htmlspecialchars($v) ?>">
                                    <?php endif; endforeach; ?>
                                    <button type="submit" class="btn-toggle btn-to-attente">
                                        Mettre en attente
                                    </button>
                                </form>
                            <?php else: ?>
                                <!-- Bouton : confirmer la place -->
                                <form method="POST"
                                      onsubmit="return confirm('Confirmer la place de <?= htmlspecialchars(addslashes($ins['prenom'].' '.$ins['nom'])) ?> ?\nLa famille sera notifiée par email.')">
                                    <input type="hidden" name="action"     value="basculer_statut">
                                    <input type="hidden" name="id_enfant"  value="<?= $ins['id_enfant'] ?>">
                                    <input type="hidden" name="id_creneau" value="<?= $ins['id_creneau'] ?>">
                                    <input type="hidden" name="direction"  value="vers_accepte">
                                    <?php foreach (['activite'=>$filtreActivite,'date'=>$filtreDate,'statut'=>$filtreStatut] as $k=>$v): if ($v): ?>
                                    <input type="hidden" name="<?= $k ?>" value="<?= htmlspecialchars($v) ?>">
                                    <?php endif; endforeach; ?>
                                    <button type="submit" class="btn-toggle btn-to-accepte">
                                         Confirmer la place
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <p class="legend-inline" style="margin-top:12px;">
            Cliquer sur le bouton d'action envoie automatiquement une notification dans l'espace parent
            et un email à la famille concernée.
        </p>
    </div>
</div>

</body>
</html>