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
        <button onclick="document.getElementById('modal-add').classList.add('active')" class="btn btn-primary">+ Nouvelle Activité</button>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
    <?php endif; ?>

    <div style="margin-top:20px;">
        <?php foreach ($activites as $act): ?>
        <div class="card" style="margin-bottom:20px;">
            <h3 style="font-family:'Baloo 2'; color:#1a5fb4;">
                <?= htmlspecialchars($act['nom']) ?>
                <small style="color:#888; font-size:.8rem;">(Capacité : <?= $act['capacite'] ?>)</small>
            </h3>

            <div class="table-wrapper" style="margin-top:10px;">
                <table>
                    <thead>
                        <tr><th>Date</th><th>Horaire</th><th>Inscrits</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach (($crByAct[$act['nom']] ?? []) as $cr): ?>
                        <tr>
                            <td><?= htmlspecialchars($cr['date']) ?></td>
                            <td><?= substr($cr['debut'], 0, 5) ?> – <?= substr($cr['fin'], 0, 5) ?></td>
                            <td><?= $cr['nb'] ?> / <?= $act['capacite'] ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Supprimer ce créneau ?')">
                                    <input type="hidden" name="action"     value="suppr_creneau">
                                    <input type="hidden" name="id_creneau" value="<?= $cr['id'] ?>">
                                    <button type="submit" class="btn btn-small btn-danger">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($crByAct[$act['nom']])): ?>
                        <tr><td colspan="4" style="text-align:center; color:#aaa;">Aucun créneau</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <form method="POST" style="display:flex; gap:10px; margin-top:15px; background:#f9f9f9; padding:10px; border-radius:10px; flex-wrap:wrap; align-items:flex-end;">
                <input type="hidden" name="action"       value="ajouter_creneau">
                <input type="hidden" name="nom_activite" value="<?= htmlspecialchars($act['nom']) ?>">
                <div class="form-group" style="margin:0;">
                    <label style="font-size:.85rem;">Date</label>
                    <input type="date" name="date" required style="width:auto;">
                </div>
                <div class="form-group" style="margin:0;">
                    <label style="font-size:.85rem;">Début</label>
                    <input type="time" name="debut" required style="width:auto;">
                </div>
                <div class="form-group" style="margin:0;">
                    <label style="font-size:.85rem;">Fin</label>
                    <input type="time" name="fin" required style="width:auto;">
                </div>
                <button type="submit" class="btn btn-small btn-primary">Ajouter créneau</button>
            </form>
        </div>
        <?php endforeach; ?>

        <?php if (empty($activites)): ?>
        <div style="text-align:center; padding:40px; color:#aaa;">Aucune activité. Créez-en une !</div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Ajout Activité -->
<div class="modal-bg" id="modal-add">
    <div class="modal">
        <span class="close-modal" onclick="document.getElementById('modal-add').classList.remove('active')">✕</span>
        <h3>Nouvelle Activité</h3>
        <form method="POST">
            <input type="hidden" name="action" value="ajouter_activite">
            <div class="form-group">
                <label>Nom</label>
                <input type="text" name="nom" required>
            </div>
            <div class="form-group">
                <label>Capacité</label>
                <input type="number" name="capacite" required min="1">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="syllabus" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Créer</button>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.modal-bg').forEach(m =>
    m.addEventListener('click', function(e){ if (e.target === this) this.classList.remove('active'); })
);
</script>
</body>
</html>
