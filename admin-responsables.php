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

<?php require_once 'includes/header-admin.php'; ?>

<div class="container" style="padding-top:30px; padding-bottom:60px;">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-family:'Baloo 2'; font-size:2rem; margin:0;">Gestion des responsables</h2>
        <?php if (isSuperAdmin()): ?>
        <button class="btn btn-primary"
                onclick="document.getElementById('modal-add').classList.add('active')">
            + Nouveau responsable
        </button>
        <?php endif; ?>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
    <?php endif; ?>

    <?php if (!isSuperAdmin()): ?>
    <div class="alert alert-info">
        Vous avez un accès administrateur standard. Seul un super-administrateur peut ajouter, modifier ou supprimer des responsables.
    </div>
    <?php endif; ?>

    <div class="card">
        <p style="color:#888; margin-bottom:18px; font-size:.9rem;">
            <?= count($responsables) ?> responsable(s) enregistré(s).
            Connecté en tant que <strong><?= htmlspecialchars($_SESSION['nom']) ?></strong>
            <?php if (isSuperAdmin()): ?>
            <span class="role-badge-super">Super Admin</span>
            <?php else: ?>
            <span class="role-badge-admin">Admin</span>
            <?php endif; ?>
        </p>

        <?php foreach ($responsables as $r):
            $isMe = ($r['login'] === $_SESSION['user']);
        ?>
        <div class="resp-card" style="margin-bottom:10px;">
            <div class="infos">
                <strong><?= htmlspecialchars($r['nom']) ?></strong>
                <?php if ($isMe): ?><span class="you-badge">vous</span><?php endif; ?>
                <?php if ($r['role'] === 'super_admin'): ?>
                <span class="role-badge-super">Super Admin</span>
                <?php else: ?>
                <span class="role-badge-admin">Admin</span>
                <?php endif; ?>
                <small><?= htmlspecialchars($r['login']) ?></small>
            </div>
            <div class="actions">
                <?php if (isSuperAdmin()): ?>
                    <button class="btn-sm btn-info"
                            onclick="openMdp('<?= htmlspecialchars($r['login']) ?>', '<?= htmlspecialchars($r['nom']) ?>')">
                     Mot de passe
                    </button>
                    <?php if (!$isMe): ?>
                    <button class="btn-sm" style="background:#8b5cf6; color:white;"
                            onclick="toggleRoleEdit('role-<?= md5($r['login']) ?>')">
                        Modifier le rôle
                    </button>
                    <form method="POST"
                          onsubmit="return confirm('Supprimer <?= htmlspecialchars($r['nom']) ?> ?')">
                        <input type="hidden" name="action" value="supprimer">
                        <input type="hidden" name="login_sup" value="<?= htmlspecialchars($r['login']) ?>">
                        <button type="submit" class="btn-sm btn-danger">✕ Supprimer</button>
                    </form>
                    <?php else: ?>
                    <span style="font-size:.8rem; color:#aaa; padding:6px;">compte actif</span>
                    <?php endif; ?>
                <?php else: ?>
                    <span style="font-size:.8rem; color:#aaa; padding:6px;">Lecture seule</span>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isSuperAdmin() && !$isMe): ?>
        <!-- Form modifier rôle -->
        <div class="role-edit-form" id="role-<?= md5($r['login']) ?>">
            <form method="POST" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <input type="hidden" name="action" value="modifier_role">
                <input type="hidden" name="login_role" value="<?= htmlspecialchars($r['login']) ?>">
                <label style="font-weight:700; font-size:.85rem; color:#3730a3;">Nouveau rôle :</label>
                <select name="nouveau_role" style="padding:6px 10px; border-radius:8px; border:1px solid #c7d2fe;">
                    <option value="admin" <?= $r['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="super_admin" <?= $r['role'] === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                </select>
                <button type="submit" class="btn btn-small btn-primary">Enregistrer</button>
                <button type="button" class="btn btn-small"
                        style="background:#eee; color:#333;"
                        onclick="toggleRoleEdit('role-<?= md5($r['login']) ?>')">Annuler</button>
            </form>
        </div>
        <?php endif; ?>

        <?php endforeach; ?>
    </div>
</div>

<?php if (isSuperAdmin()): ?>
<!-- Modal ajouter responsable -->
<div class="modal-bg" id="modal-add">
    <div class="modal">
        <span class="close-modal"
              onclick="document.getElementById('modal-add').classList.remove('active')">✕</span>
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
            <button type="submit" class="btn btn-primary"
                    style="width:100%; padding:12px; font-size:1.1rem;">
                Créer le compte
            </button>
        </form>
    </div>
</div>

<!-- Modal changer mot de passe -->
<div class="modal-bg" id="modal-mdp">
    <div class="modal">
        <span class="close-modal"
              onclick="document.getElementById('modal-mdp').classList.remove('active')">✕</span>
        <h3>Changer le mot de passe</h3>
        <p id="mdp-label" style="color:#888; margin-bottom:15px;"></p>
        <form method="POST">
            <input type="hidden" name="action" value="changer_mdp">
            <input type="hidden" name="login_mdp" id="mdp-login">
            <div class="form-group">
                <label>Nouveau mot de passe</label>
                <input type="password" name="new_mdp" placeholder="Minimum 6 caractères" required>
            </div>
            <button type="submit" class="btn btn-primary"
                    style="width:100%; padding:12px; font-size:1.1rem;">
                Enregistrer
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function openMdp(login, nom) {
    document.getElementById('mdp-login').value = login;
    document.getElementById('mdp-label').textContent = 'Modifier le mot de passe de : ' + nom;
    document.getElementById('modal-mdp').classList.add('active');
}
function toggleRoleEdit(id) {
    const el = document.getElementById(id);
    if (el) el.classList.toggle('open');
}
document.querySelectorAll('.modal-bg').forEach(m =>
    m.addEventListener('click', e => { if (e.target === m) m.classList.remove('active'); })
);
</script>
</body>
</html>