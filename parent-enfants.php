<?php require_once 'php/parent-enfants.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Parent - Ateliers du Mercredi</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/parent.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* ── Notifications admin ── */
        .notif-admin-wrapper {
            max-width: 900px;
            margin: 18px auto 0;
            padding: 0 20px;
        }
        .notif-admin-card {
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 10px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
            position: relative;
        }
        .notif-admin-card.accepte {
            background: #e8f5e9;
            border-left: 5px solid #28a745;
        }
        .notif-admin-card.attente {
            background: #fff8e1;
            border-left: 5px solid #ffc107;
        }
        .notif-icon  { font-size: 1.5rem; flex-shrink: 0; }
        .notif-body  { flex: 1; }
        .notif-title { font-weight: 800; font-size: .95rem; margin-bottom: 3px; }
        .notif-msg   { font-size: .88rem; color: #444; line-height: 1.5; }
        .notif-date  { font-size: .75rem; color: #999; margin-top: 4px; }
        .notif-badge-new {
            background: #ff5e78; color: white;
            font-size: .68rem; font-weight: bold;
            padding: 2px 8px; border-radius: 10px;
            margin-left: 8px; vertical-align: middle;
        }
    </style>
</head>
<body>

<header style="background:#fdf6d8; padding:12px 50px; display:flex; justify-content:space-between; align-items:center;">
    <h1 style="font-size:2rem; font-weight:900; margin:0;">Espace parent</h1>
    <nav>
        <a href="index.php">Accueil</a>
        <a href="activites.php">nos activités</a>
        <a href="deconnexion.php" style="color:#c0392b;">se déconnecter</a>
    </nav>
</header>

<?php
$nbIns = 0;
foreach ($enfants as $e) {
    if ($e['activites_raw']) {
        $nbIns += count(explode(';;', $e['activites_raw']));
    }
}
if ($nbIns > 0): ?>
<div class="notification-bar" id="notif-bar">
    <span>Bienvenue, <?= htmlspecialchars($_SESSION['nom']) ?> — <?= $nbIns ?> inscription(s) enregistrée(s).</span>
    <span class="close-notif" onclick="document.getElementById('notif-bar').style.display='none'">✕</span>
</div>
<?php endif; ?>

<?php if ($deleted): ?>
    <div style="max-width:700px; margin:12px auto; padding:0 20px;">
        <div class="alert alert-success">L'enfant a bien été supprimé.</div>
    </div>
<?php endif; ?>


<!-- ══ Notifications administrateur (non lues) ══ -->
<?php if (!empty($notifications)): ?>
<div class="notif-admin-wrapper">
    <h3 style="font-family:'Baloo 2'; font-size:1.2rem; margin-bottom:10px; color:#1a5fb4;">
        Nouvelles notifications
        <span class="notif-badge-new"><?= count($notifications) ?> nouvelle(s)</span>
    </h3>
    <?php foreach ($notifications as $notif):
        $isAccepte = ($notif['type'] === 'accepte');
        $dateF     = date('d/m/Y à H:i', strtotime($notif['date_creation']));
    ?>
    <div class="notif-admin-card <?= $isAccepte ? 'accepte' : 'attente' ?>">
        
        <div class="notif-body">
            <div class="notif-title">
                <?= $isAccepte ? 'Place confirmée' : 'Mise en liste d\'attente' ?>
                — <?= htmlspecialchars($notif['prenom'] . ' ' . $notif['nom_enfant']) ?>
            </div>
            <div class="notif-msg"><?= htmlspecialchars($notif['message']) ?></div>
            <div class="notif-date">Notifié le <?= $dateF ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<main class="children-grid" style="margin-top:30px;">

    <?php if (empty($enfants)): ?>
        <div style="text-align:center; padding:40px; color:#888; font-size:1.2rem;">
            <p>Aucun enfant enregistré.</p>
            <p>Commencez par ajouter un enfant !</p>
        </div>
    <?php endif; ?>

    <?php foreach ($enfants as $i => $enfant):
        $ordinal = ['1er', '2ème', '3ème', '4ème', '5ème'][$i] ?? ($i + 1) . 'ème';
        $activitesList = [];

        if ($enfant['activites_raw']) {
            foreach (explode(';;', $enfant['activites_raw']) as $act) {
                $parts = explode('|', $act);
                if (count($parts) >= 5 && $parts[0]) {
                    $statut = getStatut($db, (int)$parts[3], (int)$enfant['id'], $parts[0]);
                    $dp     = explode('-', $parts[1]);
                    $dateF  = ltrim($dp[2] ?? '', '0') . ' ' . ($moisFR[$dp[1] ?? ''] ?? '') . ' ' . ($dp[0] ?? '');
                    $activitesList[] = [
                        'nom'        => $parts[0],
                        'date'       => $dateF,
                        'heure'      => substr($parts[2], 0, 5),
                        'id_creneau' => (int)$parts[3],
                        'salle'      => $parts[4],
                        'statut'     => $statut,
                    ];
                }
            }
        }
    ?>
    <div class="child-card">
        <div class="card-top">
            <h2 class="child-title"><?= $ordinal ?> enfant</h2>
            <div class="info-group"><label>Nom :</label><div class="value"><?= htmlspecialchars($enfant['nom']) ?></div></div>
            <div class="info-group"><label>Prénom :</label><div class="value"><?= htmlspecialchars($enfant['prenom']) ?></div></div>
            <div class="info-group"><label>Âge :</label><div class="value"><?= htmlspecialchars($enfant['age']) ?> ans</div></div>
        </div>
    <div class="child-actions">

    <a href="modifier-enfant.php?id=<?= $enfant['id'] ?>" class="btn-edit">
        Modifier les infos
    </a>

</div>

<div class="child-actions">

    <form method="POST" action="php/supprimer-enfant.php"
          onsubmit="return confirm('Supprimer <?= htmlspecialchars(addslashes($enfant['prenom'])) ?> ?\nSes inscriptions seront également supprimées.');">

        <input type="hidden" name="id" value="<?= $enfant['id'] ?>">

        <button type="submit" class="btn-delete">
         supprimer l'enfant
        </button>

    </form>

</div>

        <div class="card-bottom">
            <div class="section-title-card">Activités inscrites :</div>

            <?php if (empty($activitesList)): ?>
                <p style="opacity:.7; font-size:.9rem;">Aucune activité choisie.</p>
            <?php else: ?>
                <?php foreach ($activitesList as $act): ?>
                <div class="activity-item-card">
                    <span class="act-name-card"><?= htmlspecialchars($act['nom']) ?></span>
                    <div class="act-detail">
                        <?= htmlspecialchars($act['date']) ?>
                        &nbsp; <?= htmlspecialchars($act['heure']) ?>
                        <?php if ($act['salle']): ?>
                            &nbsp;<span class="salle-badge">Salle <?= htmlspecialchars($act['salle']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="status-row">
                        <span class="<?= $act['statut'] === 'accepté' ? 'badge-ok' : 'badge-wait' ?>">
                            <?= $act['statut'] === 'accepté' ? 'accepté' : "liste d'attente" ?>
                        </span>
                        <form method="POST" action="activites.php" onsubmit="return confirm('Se désinscrire de cette activité ?')">
                            <input type="hidden" name="action"     value="desinscrire">
                            <input type="hidden" name="id_creneau" value="<?= $act['id_creneau'] ?>">
                            <input type="hidden" name="id_enfant"  value="<?= $enfant['id'] ?>">
                            <button type="submit" class="btn-desins-card">✕ Se désinscrire</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

</main>

<footer class="footer-actions">
    <a href="ajouter-enfant.php">
        <button class="btn btn-yellow" style="padding:12px 35px; font-size:1.1rem; font-weight:bold; border-radius:20px;">+ ajouter un enfant</button>
    </a>
    <a href="activites.php">
        <button class="btn btn-primary btn-big">choisir une activité</button>
    </a>
</footer>

</body>
</html>