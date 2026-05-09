<?php require_once 'php/admin-responsables.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des responsables - Admin</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<header class="admin-header">
    <h1>Espace administrateur</h1>
    <nav>
        <a href="admin-dashboard.php">tableau de bord</a>
        <a href="admin-liste-enfants.php">liste des enfants</a>
        <a href="admin-activites.php">activités</a>
        <a href="admin-responsables.php" style="text-decoration:underline;">responsables</a>
        <a href="deconnexion.php" style="color:#c0392b;">se déconnecter</a>
    </nav>
</header>

<div class="container" style="padding-top:30px; padding-bottom:60px;">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-family:'Baloo 2'; font-size:2rem; margin:0;">Gestion des responsables</h2>
        <button class="btn btn-primary" onclick="document.getElementById('modal-add').classList.add('active')">+ Nouveau responsable</button>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
    <?php endif; ?>

    <div class="card">
        <p style="color:#888; margin-bottom:18px; font-size:.9rem;">
            <?= count($responsables) ?> responsable(s) enregistré(s).
            Connecté en tant que <strong><?= htmlspecialchars($_SESSION['nom']) ?></strong>.
        </p>

        <?php foreach ($responsables as $r):
            $isMe = ($r['login'] === $_SESSION['user']);
        ?>
        <div class="resp-card">
            <div class="infos">
                <strong><?= htmlspecialchars($r['nom']) ?></strong>
                <?php if ($isMe): ?><span class="you-badge">vous</span><?php endif; ?>
                <small><?= htmlspecialchars($r['login']) ?></small>
            </div>
            <div class="actions">
                <button class="btn-sm btn-info"
                        onclick="openMdp('<?= htmlspecialchars($r['login']) ?>', '<?= htmlspecialchars($r['nom']) ?>')">
                     Mot de passe
                </button>
                <?php if (!$isMe): ?>
                <form method="POST" onsubmit="return confirm('Supprimer <?= htmlspecialchars($r['nom']) ?> ?')">
                    <input type="hidden" name="action"    value="supprimer">
                    <input type="hidden" name="login_sup" value="<?= htmlspecialchars($r['login']) ?>">
                    <button type="submit" class="btn-sm btn-danger">✕ Supprimer</button>
                </form>
                <?php else: ?>
                <span style="font-size:.8rem; color:#aaa; padding:6px;">compte actif</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal ajouter responsable -->
<div class="modal-bg" id="modal-add">
    <div class="modal">
        <span class="close-modal" onclick="document.getElementById('modal-add').classList.remove('active')">X</span>
        <h3>Nouveau responsable</h3>
        <form method="POST">
            <input type="hidden" name="action" value="ajouter">
            <div class="form-group">
                <label>Nom complet</label>
                <input type="text" name="nom" placeholder="ex: Claire Martin" required>
            </div>
            <div class="form-group">
                <label>Email / Login</label>
                <input type="email" name="login" placeholder="ex: claire@ateliers.fr" required>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="mdp" placeholder="Minimum 6 caractères" required>
            </div>
            <div class="form-group">
                <label>Confirmer le mot de passe</label>
                <input type="password" name="mdp2" placeholder="Répétez" required>
            </div>
            <div class="form-group">
    <label>Rôle</label>
    <select name="role">
        <option value="admin">Admin</option>
        <option value="super_admin">Super Admin</option>
    </select>
</div>
            <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; font-size:1.1rem;">Créer le compte</button>
        </form>
    </div>
</div>

<!-- Modal changer mot de passe -->
<div class="modal-bg" id="modal-mdp">
    <div class="modal">
        <span class="close-modal" onclick="document.getElementById('modal-mdp').classList.remove('active')">✕</span>
        <h3>Changer le mot de passe</h3>
        <p id="mdp-label" style="color:#888; margin-bottom:15px;"></p>
        <form method="POST">
            <input type="hidden" name="action"    value="changer_mdp">
            <input type="hidden" name="login_mdp" id="mdp-login">
            <div class="form-group">
                <label>Nouveau mot de passe</label>
                <input type="password" name="new_mdp" placeholder="Minimum 6 caractères" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; font-size:1.1rem;">Enregistrer</button>
        </form>
    </div>
</div>

<script>
function openMdp(login, nom) {
    document.getElementById('mdp-login').value      = login;
    document.getElementById('mdp-label').textContent = 'Modifier le mot de passe de : ' + nom;
    document.getElementById('modal-mdp').classList.add('active');
}
document.querySelectorAll('.modal-bg').forEach(m =>
    m.addEventListener('click', function(e){ if (e.target === this) this.classList.remove('active'); })
);
</script>
</body>
</html>
