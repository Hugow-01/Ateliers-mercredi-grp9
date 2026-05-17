<?php
/**
 * admin-liste-enfants.php — Vue d'ensemble des inscriptions par activité
 *
 * Tableau filtrable (activité, date, nom enfant, email parent, statut).
 * Permet aussi de désinscrire un enfant directement depuis cette vue.
 * Une désinscription confirmée promeut automatiquement le 1er de la liste d'attente.
 */

require_once 'php/admin-liste-enfants.php'; // Charge $inscriptions, les filtres actifs et $activites

$pageTitle       = 'Liste des enfants - Admin';
$activeAdminPage = 'enfants';

require_once 'includes/header-admin.php';
?>

<div class="container" style="padding-top:30px; padding-bottom:60px;">
    <h2 style="font-family:'Baloo 2'; font-size:2rem; margin-bottom:20px;">
        Liste des enfants par activité
    </h2>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
    <?php endif; ?>

    <!-- ══ Filtres de recherche ══ -->
    <form method="GET" style="display:flex; gap:12px; align-items:flex-end; margin-bottom:20px; flex-wrap:wrap;">
        <div class="form-group" style="flex:1; min-width:180px; margin-bottom:0;">
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
        <div class="form-group" style="flex:0.8; min-width:140px; margin-bottom:0;">
            <label>Date</label>
            <input type="date" name="date" value="<?= htmlspecialchars($filtreDate) ?>">
        </div>
        <div class="form-group" style="flex:0.8; min-width:140px; margin-bottom:0;">
            <label>Nom de l'enfant</label>
            <input type="text" name="nom_enfant" placeholder="ex: Martin"
                   value="<?= htmlspecialchars($filtreNomEnf) ?>">
        </div>
        <div class="form-group" style="flex:1; min-width:180px; margin-bottom:0;">
            <label>Email du parent</label>
            <input type="email" name="email_parent" placeholder="ex: parent@mail.fr"
                   value="<?= htmlspecialchars($filtreEmail) ?>">
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label>Statut</label>
            <select name="statut">
                <option value=""      <?= $filtreStatut === ''       ? 'selected' : '' ?>>Tous</option>
                <option value="accepte" <?= $filtreStatut === 'accepte' ? 'selected' : '' ?>>Accepté uniquement</option>
                <option value="attente" <?= $filtreStatut === 'attente' ? 'selected' : '' ?>>Liste d'attente uniquement</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-small">Filtrer</button>
        <a href="admin-liste-enfants.php" class="btn btn-small" style="background:#eee; color:#333;">
            Réinitialiser
        </a>
    </form>

    <!-- ══ Compteurs récapitulatifs ══ -->
    <?php
        $nbTotal   = count($inscriptions);
        $nbAccepte = count(array_filter($inscriptions, fn($r) => $r['statut_type'] === 'accepte'));
        $nbAttente = count(array_filter($inscriptions, fn($r) => $r['statut_type'] === 'attente'));
    ?>
    <div class="count-bar">
        <span class="count-chip chip-total">Total affiché : <?= $nbTotal ?></span>
        <span class="count-chip chip-accepte">Acceptés : <?= $nbAccepte ?></span>
        <span class="count-chip chip-attente">En attente : <?= $nbAttente ?></span>
    </div>

    <!-- ══ Tableau des inscriptions ══ -->
    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nom</th><th>Prénom</th><th>Âge</th><th>Email parent</th>
                        <th>Activité</th><th>Date</th><th>Horaire</th><th>Statut</th><th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($inscriptions)): ?>
                    <tr>
                        <td colspan="9" style="text-align:center; color:#aaa; padding:30px;">Aucun résultat</td>
                    </tr>
                    <?php endif; ?>

                    <?php foreach ($inscriptions as $ins):
                        $isAccepte = ($ins['statut_type'] === 'accepte');
                        $dateAff   = date('d/m/Y', strtotime($ins['date']));
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($ins['nom']) ?></td>
                        <td><?= htmlspecialchars($ins['prenom']) ?></td>
                        <td><?= htmlspecialchars($ins['age']) ?> ans</td>
                        <td style="font-size:.82rem; color:#555;"><?= htmlspecialchars($ins['login_famille']) ?></td>
                        <td><?= htmlspecialchars($ins['activite']) ?></td>
                        <td><?= $dateAff ?></td>
                        <td><?= substr($ins['debut'],0,5) ?> – <?= substr($ins['fin'],0,5) ?></td>
                        <td>
                            <?php if ($isAccepte): ?>
                                <span class="badge-accepte">Accepté</span>
                            <?php else: ?>
                                <span class="badge-attente">Liste d'attente</span>
                                <span class="badge-pos">#<?= htmlspecialchars($ins['position']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="action-cell">
                            <!-- Désinscription admin avec confirmation JS -->
                            <form method="POST"
                                  onsubmit="return confirm('Désinscrire <?= htmlspecialchars(addslashes($ins['prenom'].' '.$ins['nom'])) ?> de ce créneau ?\nLa famille sera notifiée.')">
                                <input type="hidden" name="action"      value="desinscrire_admin">
                                <input type="hidden" name="id_enfant"   value="<?= $ins['id_enfant'] ?>">
                                <input type="hidden" name="id_creneau"  value="<?= $ins['id_creneau'] ?>">
                                <input type="hidden" name="statut_type" value="<?= $ins['statut_type'] ?>">
                                <?php /* Repasser les filtres actifs pour rester sur la même vue après action */ ?>
                                <?php foreach (['activite'=>$filtreActivite,'date'=>$filtreDate,'statut'=>$filtreStatut,'nom_enfant'=>$filtreNomEnf,'email_parent'=>$filtreEmail] as $k=>$v): if($v): ?>
                                <input type="hidden" name="<?= $k ?>" value="<?= htmlspecialchars($v) ?>">
                                <?php endif; endforeach; ?>
                                <button type="submit" class="btn-desins-admin">✕ Désinscrire</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p style="font-size:.78rem; color:#999; margin-top:12px;">
            La désinscription envoie automatiquement une notification dans l'espace parent et un email à la famille.
            Si l'enfant était inscrit (confirmé), le premier de la liste d'attente est automatiquement promu.
        </p>
    </div>
</div>

</body>
</html>