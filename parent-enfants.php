<?php
/**
 * parent-enfants.php — Espace personnel du parent
 *
 * Affiche les cartes de chaque enfant avec ses activités en cours,
 * les notifications non lues envoyées par les admins,
 * et les boutons de gestion (modifier, supprimer, inscrire, désinscrire).
 */

require_once 'php/parent-enfants.php'; // Récupère $enfants, $notifications, $moisFR, $deleted

$pageTitle  = 'Espace Parent - Ateliers du Mercredi';
$activePage = 'espace';
$extraCSS   = ['parent.css'];

require_once 'includes/header.php';

// Compter le total d'inscriptions pour la barre de notification
$nbIns = 0;
foreach ($enfants as $e) {
    if ($e['activites_raw']) $nbIns += count(explode(';;', $e['activites_raw']));
}
?>

<!-- Barre de notification si l'enfant a des inscriptions -->
<?php if ($nbIns > 0): ?>
<div class="notification-bar" id="notif-bar">
    <span>Bienvenue, <?= htmlspecialchars($_SESSION['nom']) ?> &mdash; <?= $nbIns ?> inscription(s) enregistrée(s).</span>
    <span class="close-notif" onclick="document.getElementById('notif-bar').style.display='none'">x</span>
</div>
<?php endif; ?>

<!-- Message de confirmation suppression enfant -->
<?php if ($deleted): ?>
<div style="max-width:700px; margin:12px auto; padding:0 20px;">
    <div class="alert alert-success">L'enfant a bien été supprimé.</div>
</div>
<?php endif; ?>

<!-- ══ Notifications envoyées par les admins (place confirmée, annulation…) ══ -->
<?php if (!empty($notifications)): ?>
<div class="notif-admin-wrapper">
    <h3 style="font-family:'Baloo 2'; font-size:1.2rem; margin-bottom:10px; color:#1a5fb4;">
        Nouvelles notifications
        <span class="notif-badge-new"><?= count($notifications) ?> nouvelle(s)</span>
    </h3>
    <?php foreach ($notifications as $notif):
        $isAccepte     = ($notif['type'] === 'accepte');
        $isAnnulation  = ($notif['type'] === 'annulation');
        $isModification= ($notif['type'] === 'modification');
        $dateF         = date('d/m/Y à H:i', strtotime($notif['date_creation']));
        $cssClass      = $isAccepte ? 'accepte' : ($isAnnulation ? 'annulation' : ($isModification ? 'modification' : 'attente'));
    ?>
    <div class="notif-admin-card <?= $cssClass ?>">
        <div class="notif-body">
            <div class="notif-title">
                <?php if($isAccepte): ?>
                    Place confirmée - <?= htmlspecialchars($notif['prenom'].' '.$notif['nom_enfant']) ?>
                <?php elseif($isAnnulation): ?>
                    Activité annulée - <?= htmlspecialchars($notif['prenom'].' '.$notif['nom_enfant']) ?>
                <?php elseif($isModification): ?>
                    Activité modifiée - <?= htmlspecialchars($notif['prenom'].' '.$notif['nom_enfant']) ?>
                <?php else: ?>
                    Mise en liste d'attente - <?= htmlspecialchars($notif['prenom'].' '.$notif['nom_enfant']) ?>
                <?php endif; ?>
            </div>
            <div class="notif-msg"><?= nl2br(htmlspecialchars($notif['message'])) ?></div>
            <div class="notif-date">Notifié le <?= $dateF ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ══ Grille des cartes enfants ══ -->
<main class="children-grid" style="margin-top:30px;">

    <?php if (empty($enfants)): ?>
    <div style="text-align:center; padding:40px; color:#888; font-size:1.2rem;">
        <p>Aucun enfant enregistré.</p>
        <p>Commencez par ajouter un enfant !</p>
    </div>
    <?php endif; ?>

    <?php foreach ($enfants as $i => $enfant):
        $ordinal = ['1er','2ème','3ème','4ème','5ème'][$i] ?? ($i+1).'ème';

        // Fusionner inscriptions confirmées et en attente dans un seul tableau
        $toutes = [];
        if (!empty($enfant['activites_raw'])) $toutes = array_merge($toutes, explode(';;', $enfant['activites_raw']));
        if (!empty($enfant['attentes_raw']))  $toutes = array_merge($toutes, explode(';;', $enfant['attentes_raw']));
        $enfant['activites_raw'] = implode(';;', $toutes);

        // Parser le format "pipe-separated" retourné par la requête SQL
        $activitesList = [];
        if ($enfant['activites_raw']) {
            foreach (explode(';;', $enfant['activites_raw']) as $act) {
                $parts = explode('|', $act);
                if (count($parts) >= 8 && $parts[0]) {
                    $dp    = explode('-', $parts[1]);
                    $dateF = ltrim($dp[2]??'','0').' '.($moisFR[$dp[1]??'']??'').' '.($dp[0]??'');
                    $activitesList[] = [
                        'nom'           => $parts[0],
                        'date'          => $dateF,
                        'heure'         => substr($parts[2], 0, 5),
                        'id_creneau'    => (int)$parts[3],
                        'salle'         => $parts[4],
                        'statut'        => $parts[5] === 'inscrit' ? 'accepte' : "liste d'attente",
                        'position'      => (int)$parts[6],
                        'total_attente' => (int)$parts[7],
                    ];
                }
            }
        }
    ?>

    <!-- Carte d'un enfant -->
    <div class="child-card">
        <div class="card-top">
            <h2 class="child-title"><?= $ordinal ?> enfant</h2>
            <div class="info-group"><label>Nom :</label>    <div class="value"><?= htmlspecialchars($enfant['nom']) ?></div></div>
            <div class="info-group"><label>Prénom :</label> <div class="value"><?= htmlspecialchars($enfant['prenom']) ?></div></div>
            <div class="info-group"><label>Âge :</label>    <div class="value"><?= htmlspecialchars($enfant['age']) ?> ans</div></div>
        </div>

        <!-- Boutons de gestion de la fiche enfant -->
        <div class="child-actions">
            <a href="modifier-enfant.php?id=<?= $enfant['id'] ?>" class="btn-edit">Modifier les infos</a>
        </div>
        <div class="child-actions">
            <form method="POST" action="php/supprimer-enfant.php"
                  onsubmit="return confirm('Supprimer <?= htmlspecialchars(addslashes($enfant['prenom'])) ?> ?\nSes inscriptions seront également supprimées.');">
                <input type="hidden" name="id" value="<?= $enfant['id'] ?>">
                <button type="submit" class="btn-delete">Supprimer l'enfant</button>
            </form>
        </div>

        <!-- Partie violette : liste des activités de l'enfant -->
        <div class="card-bottom">
            <div class="section-title-card">Activités inscrites :</div>

            <?php if (empty($activitesList)): ?>
            <p style="opacity:.7; font-size:.9rem;">Aucune activité choisie.</p>
            <?php else: ?>
            <?php foreach ($activitesList as $act): ?>
            <div class="activity-item-card">
                <span class="act-name-card"><?= htmlspecialchars($act['nom']) ?></span>
                <div class="act-detail">
                    <?= htmlspecialchars($act['date']) ?>&nbsp;
                    <?= htmlspecialchars($act['heure']) ?>
                    <?php if ($act['salle']): ?>
                    &nbsp;<span class="salle-badge">Salle <?= htmlspecialchars($act['salle']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="status-row">
                    <span class="<?= $act['statut'] === 'accepte' ? 'badge-ok' : 'badge-wait' ?>">
                        <?= $act['statut'] === 'accepte' ? 'Accepté' : "Liste d'attente" ?>
                    </span>
                    <?php if ($act['position'] !== -1): ?>
                    &nbsp;<span class="salle-badge">
                        Position <?= htmlspecialchars($act['position']) ?>/<?= htmlspecialchars($act['total_attente']) ?>
                    </span>
                    <?php endif; ?>
                    <!-- Bouton désinscrire ou quitter la liste selon le statut -->
                    <form method="POST" action="activites.php"
                          onsubmit="return confirm('Se désinscrire de cette activité ?')">
                        <input type="hidden" name="action"
                               value="<?= $act['statut'] === 'accepte' ? 'desinscrire' : 'quitter_attente' ?>">
                        <input type="hidden" name="id_creneau" value="<?= $act['id_creneau'] ?>">
                        <input type="hidden" name="id_enfant"  value="<?= $enfant['id'] ?>">
                        <button type="submit" class="btn-desins-card">
                            <?= $act['statut'] === 'accepte' ? 'Se désinscrire' : 'Quitter la liste' ?>
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

</main>

<!-- Boutons d'action en bas de page -->
<footer class="footer-actions">
    <a href="ajouter-enfant.php">
        <button class="btn btn-yellow"
                style="padding:12px 35px; font-size:1.1rem; font-weight:bold; border-radius:20px;">
            + Ajouter un enfant
        </button>
    </a>
    <a href="activites.php">
        <button class="btn btn-primary btn-big">Choisir une activité</button>
    </a>
</footer>

</body>
</html>