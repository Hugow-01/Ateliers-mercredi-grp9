<?php require_once 'php/admin-comptes.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comptes parents - Admin</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<header class="admin-header">
    <h1>Espace administrateur</h1>
    <nav>
        <a href="admin-dashboard.php">Tableau de bord</a>
        <a href="admin-liste-enfants.php">Liste des enfants</a>
        <a href="admin-activites.php">Activités</a>
        <a href="admin-comptes.php" style="text-decoration:underline;">Comptes parents</a>
        <a href="admin-inscription-parents.php">Inscriptions</a>
        <a href="deconnexion.php" style="color:#c0392b;">Se déconnecter</a>
    </nav>
</header>

<div class="container" style="padding-top:30px; padding-bottom:60px;">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-family:'Baloo 2'; font-size:2rem; margin:0;">Gestion des comptes parents</h2>
        <button onclick="document.getElementById('modal-creer-famille').classList.add('active')"
                class="btn btn-primary">+ Nouveau compte parent</button>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
    <?php endif; ?>

    <!-- Recherche -->
    <form method="GET" style="display:flex; gap:10px; margin-bottom:20px; align-items:flex-end; flex-wrap:wrap;">
        <div class="form-group" style="flex:1; min-width:220px; margin-bottom:0;">
            <label>Rechercher (nom famille ou email)</label>
            <input type="text" name="q" value="<?= htmlspecialchars($recherche) ?>"
                   placeholder="ex: Dupont ou dupont@mail.fr">
        </div>
        <button type="submit" class="btn btn-primary btn-small">Chercher</button>
        <a href="admin-comptes.php" class="btn btn-small" style="background:#eee; color:#333;">Réinitialiser</a>
    </form>

    <p style="color:#888; font-size:.88rem; margin-bottom:16px;">
        <?= count($famillesAvecEnfants) ?> famille(s) trouvée(s).
    </p>

    <?php foreach ($famillesAvecEnfants as $fam): ?>
    <div class="famille-card">
        <div class="famille-header">
            <div>
                <div class="famille-title"><?= htmlspecialchars($fam['nom']) ?></div>
                <div class="famille-email"><?= htmlspecialchars($fam['login']) ?> — <?= count($fam['enfants']) ?> enfant(s)</div>
            </div>
            <div class="famille-actions">
                <button class="btn-sm-edit"
                        onclick="toggleFamilleEdit('edit-fam-<?= $fam['id'] ?>')">
                    ✏ Modifier
                </button>
                <form method="POST" onsubmit="return confirm('Supprimer la famille <?= htmlspecialchars(addslashes($fam['nom'])) ?> et tous ses enfants ?')">
                    <input type="hidden" name="action" value="supprimer_famille">
                    <input type="hidden" name="id_famille" value="<?= $fam['id'] ?>">
                    <button type="submit" class="btn-sm-delete">✕ Supprimer</button>
                </form>
            </div>
        </div>

        <!-- Formulaire modification famille -->
        <div class="edit-form-inline" id="edit-fam-<?= $fam['id'] ?>">
            <form method="POST" style="margin:0;">
                <input type="hidden" name="action" value="modifier_famille">
                <input type="hidden" name="id_famille" value="<?= $fam['id'] ?>">
                <div class="edit-grid-2">
                    <div>
                        <label>Nom de la famille</label>
                        <input type="text" name="nom" value="<?= htmlspecialchars($fam['nom']) ?>" required>
                    </div>
                    <div>
                        <label>Email (identifiant)</label>
                        <input type="email" name="login" value="<?= htmlspecialchars($fam['login']) ?>" required>
                    </div>
                    <div>
                        <label>Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                        <input type="password" name="new_mdp" placeholder="Minimum 6 caractères">
                    </div>
                </div>
                <div style="display:flex; gap:8px; justify-content:flex-end;">
                    <button type="button" class="btn btn-small" style="background:#eee; color:#333;"
                            onclick="toggleFamilleEdit('edit-fam-<?= $fam['id'] ?>')">Annuler</button>
                    <button type="submit" class="btn btn-small btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>

        <div class="famille-body">
            <!-- Liste des enfants -->
            <?php if (empty($fam['enfants'])): ?>
            <div class="no-enfants">Aucun enfant enregistré.</div>
            <?php else: ?>
            <?php foreach ($fam['enfants'] as $enf): ?>
            <div class="enfant-row" id="enf-row-<?= $enf['id'] ?>">
                <div class="enfant-info">
                    <strong><?= htmlspecialchars($enf['prenom'] . ' ' . $enf['nom']) ?></strong>
                    — <?= $enf['age'] ?> ans
                </div>
                <div class="enfant-actions">
                    <button class="btn-sm-edit"
                            onclick="toggleEnfantEdit('edit-enf-<?= $enf['id'] ?>')">✏</button>
                    <form method="POST" onsubmit="return confirm('Supprimer <?= htmlspecialchars(addslashes($enf['prenom'])) ?> ?')">
                        <input type="hidden" name="action" value="supprimer_enfant">
                        <input type="hidden" name="id_enfant" value="<?= $enf['id'] ?>">
                        <button type="submit" class="btn-sm-delete">✕</button>
                    </form>
                </div>
            </div>
            <!-- Form modifier enfant -->
            <div class="edit-form-inline" id="edit-enf-<?= $enf['id'] ?>">
                <form method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="modifier_enfant">
                    <input type="hidden" name="id_enfant" value="<?= $enf['id'] ?>">
                    <div class="edit-grid-3">
                        <div>
                            <label>Nom</label>
                            <input type="text" name="nom" value="<?= htmlspecialchars($enf['nom']) ?>" required>
                        </div>
                        <div>
                            <label>Prénom</label>
                            <input type="text" name="prenom" value="<?= htmlspecialchars($enf['prenom']) ?>" required>
                        </div>
                        <div>
                            <label>Âge</label>
                            <input type="number" name="age" min="1" max="17" value="<?= $enf['age'] ?>" required>
                        </div>
                    </div>
                    <div style="display:flex; gap:8px; justify-content:flex-end;">
                        <button type="button" class="btn btn-small" style="background:#eee;color:#333;"
                                onclick="toggleEnfantEdit('edit-enf-<?= $enf['id'] ?>')">Annuler</button>
                        <button type="submit" class="btn btn-small btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>

            <!-- Ajouter un enfant -->
            <button class="toggle-add-btn"
                    onclick="toggleEnfantEdit('add-enf-<?= $fam['id'] ?>')">
                + Ajouter un enfant
            </button>
            <div class="edit-form-inline add-enfant-form" id="add-enf-<?= $fam['id'] ?>"
                 style="background:#f0fdf4; border-color:#86efac;">
                <form method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="creer_enfant">
                    <input type="hidden" name="id_famille" value="<?= $fam['id'] ?>">
                    <div class="edit-grid-3">
                        <div>
                            <label style="color:#166534;">Nom</label>
                            <input type="text" name="nom" placeholder="ex: Dupont" required
                                   style="border-color:#86efac;">
                        </div>
                        <div>
                            <label style="color:#166534;">Prénom</label>
                            <input type="text" name="prenom" placeholder="ex: Lucas" required
                                   style="border-color:#86efac;">
                        </div>
                        <div>
                            <label style="color:#166534;">Âge (1-17)</label>
                            <input type="number" name="age" min="1" max="17" placeholder="8" required
                                   style="border-color:#86efac;">
                        </div>
                    </div>
                    <div style="display:flex; gap:8px; justify-content:flex-end;">
                        <button type="button" class="btn btn-small" style="background:#eee;color:#333;"
                        onclick="toggleEnfantEdit('add-enf-<?= $fam['id'] ?>')">Annuler</button>
                        <button type="submit" class="btn btn-small btn-primary">Ajouter l'enfant</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($famillesAvecEnfants)): ?>
    <div style="text-align:center; padding:40px; color:#aaa;">Aucun compte trouvé.</div>
    <?php endif; ?>
</div>

<!-- Modal : créer un compte famille -->
<div class="modal-bg" id="modal-creer-famille">
    <div class="modal" style="max-width:500px;">
        <span class="close-modal"
              onclick="document.getElementById('modal-creer-famille').classList.remove('active')">✕</span>
        <h3 style="font-family:'Baloo 2'; margin-bottom:20px;">Nouveau compte parent</h3>
        <form method="POST">
            <input type="hidden" name="action" value="creer_famille">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group" style="grid-column:span 2;">
                    <label>Nom de la famille *</label>
                    <input type="text" name="nom" placeholder="ex: Famille Martin" required>
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label>Email (identifiant) *</label>
                    <input type="email" name="login" placeholder="ex: martin@gmail.com" required>
                </div>
                <div class="form-group">
                    <label>Mot de passe *</label>
                    <input type="password" name="mdp" placeholder="Min. 6 caractères" required>
                </div>
                <div class="form-group">
                    <label>Confirmer le mot de passe *</label>
                    <input type="password" name="mdp2" placeholder="Répétez" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"
                    style="width:100%; padding:12px; font-size:1.05rem; margin-top:8px;">
                Créer le compte
            </button>
        </form>
    </div>
</div>

<script>
function toggleFamilleEdit(id) {
    const el = document.getElementById(id);
    el.classList.toggle('open');
    if (el.classList.contains('open')) {
        setTimeout(() => el.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 60);
    }
}
function toggleEnfantEdit(id) {
    const el = document.getElementById(id);
    el.classList.toggle('open');
}
document.querySelectorAll('.modal-bg').forEach(m =>
    m.addEventListener('click', e => { if (e.target === m) m.classList.remove('active'); })
);
</script>
</body>
</html>