<?php
require_once 'config.php';
requireAdmin();

$db = getDB();
$message = ''; $messageType = '';

// ── AJOUTER un responsable ──
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='ajouter') {
    $login = trim($_POST['login']??'');
    $nom   = trim($_POST['nom']??'');
    $mdp   = $_POST['mdp']??'';
    $mdp2  = $_POST['mdp2']??'';
    if (!$login||!$nom||!$mdp) {
        $message='Remplissez tous les champs.'; $messageType='error';
    } elseif ($mdp!==$mdp2) {
        $message='Les mots de passe ne correspondent pas.'; $messageType='error';
    } elseif (strlen($mdp)<6) {
        $message='Mot de passe trop court (min. 6 caractères).'; $messageType='error';
    } else {
        $chk=$db->prepare("SELECT login FROM Responsable WHERE login=?"); $chk->execute([$login]);
        if ($chk->fetch()) { $message='Ce login existe déjà.'; $messageType='error'; }
        else {
            $hash=password_hash($mdp,PASSWORD_DEFAULT);
            $db->prepare("INSERT INTO Responsable (login,mdp,nom) VALUES (?,?,?)")->execute([$login,$hash,$nom]);
            $message="✔ Responsable \"$nom\" ajouté avec succès."; $messageType='success';
        }
    }
}

// ── SUPPRIMER un responsable ──
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='supprimer') {
    $loginSup=$_POST['login_sup']??'';
    if ($loginSup===$_SESSION['user']) {
        $message='Vous ne pouvez pas supprimer votre propre compte.'; $messageType='error';
    } elseif ($loginSup) {
        $db->prepare("DELETE FROM Responsable WHERE login=?")->execute([$loginSup]);
        $message="✔ Responsable supprimé."; $messageType='success';
    }
}

// ── CHANGER MOT DE PASSE ──
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='changer_mdp') {
    $loginMdp=$_POST['login_mdp']??'';
    $newMdp=$_POST['new_mdp']??'';
    if (!$loginMdp||!$newMdp) { $message='Données manquantes.'; $messageType='error'; }
    elseif (strlen($newMdp)<6) { $message='Mot de passe trop court.'; $messageType='error'; }
    else {
        $hash=password_hash($newMdp,PASSWORD_DEFAULT);
        $db->prepare("UPDATE Responsable SET mdp=? WHERE login=?")->execute([$hash,$loginMdp]);
        $message="✔ Mot de passe mis à jour."; $messageType='success';
    }
}

$responsables=$db->query("SELECT login,nom FROM Responsable ORDER BY nom")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Gestion des responsables</title>
<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
<style>
.modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;}
.modal-bg.active{display:flex;}
.modal{background:white;border-radius:20px;padding:35px;width:90%;max-width:450px;position:relative;}
.modal h3{font-family:'Baloo 2';margin-top:0;}
.close-modal{position:absolute;top:15px;right:20px;cursor:pointer;font-size:1.4rem;color:#aaa;}
.resp-card{display:flex;justify-content:space-between;align-items:center;padding:14px 18px;border:1px solid #eee;border-radius:12px;margin-bottom:10px;background:white;}
.resp-card .infos strong{font-size:1rem;} .resp-card .infos small{color:#888;display:block;}
.btn-sm{padding:6px 14px;border-radius:8px;border:none;cursor:pointer;font-weight:bold;font-size:.85rem;}
.btn-danger{background:#ff7043;color:white;}
.btn-info{background:#00e5ff;color:#000;}
.actions{display:flex;gap:8px;}
.you-badge{background:#fdd835;color:#333;padding:2px 8px;border-radius:6px;font-size:.75rem;font-weight:bold;margin-left:8px;}
</style>
</head>
<body>
<header style="background:#fdd835;padding:15px 30px;display:flex;justify-content:space-between;align-items:center;">
    <h1 style="font-family:'Baloo 2';font-size:1.8rem;color:#3e2723;margin:0;">Espace administrateur</h1>
    <nav>
        <a href="admin-dashboard.php">tableau de bord</a>
        <a href="admin-liste-enfants.php">liste des enfants</a>
        <a href="admin-activites.php">activités</a>
        <a href="admin-responsables.php" style="text-decoration:underline;">responsables</a>
        <a href="deconnexion.php" style="color:#c0392b;">se déconnecter</a>
    </nav>
</header>

<div class="container" style="padding-top:30px;padding-bottom:60px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="font-family:'Baloo 2';font-size:2rem;margin:0;">Gestion des responsables</h2>
        <button class="btn btn-primary" onclick="document.getElementById('modal-add').classList.add('active')">+ Nouveau responsable</button>
    </div>

    <?php if($message):?>
        <div class="alert alert-<?=$messageType?>"><?=$message?></div>
    <?php endif;?>

    <div class="card">
        <p style="color:#888;margin-bottom:18px;font-size:.9rem;">
            <?=count($responsables)?> responsable(s) enregistré(s). Vous êtes connecté en tant que <strong><?=htmlspecialchars($_SESSION['nom'])?></strong>.
        </p>

        <?php foreach($responsables as $r):
            $isMe=($r['login']===$_SESSION['user']);
        ?>
        <div class="resp-card">
            <div class="infos">
                <strong><?=htmlspecialchars($r['nom'])?></strong>
                <?php if($isMe):?><span class="you-badge">vous</span><?php endif;?>
                <small><?=htmlspecialchars($r['login'])?></small>
            </div>
            <div class="actions">
                <!-- Changer mot de passe -->
                <button class="btn-sm btn-info"
                    onclick="openMdp('<?=htmlspecialchars($r['login'])?>','<?=htmlspecialchars($r['nom'])?>')">
                    🔑 Mot de passe
                </button>
                <!-- Supprimer (pas soi-même) -->
                <?php if(!$isMe):?>
                <form method="POST" onsubmit="return confirm('Supprimer <?=htmlspecialchars($r['nom'])?> ?')">
                    <input type="hidden" name="action"    value="supprimer">
                    <input type="hidden" name="login_sup" value="<?=htmlspecialchars($r['login'])?>">
                    <button type="submit" class="btn-sm btn-danger">✕ Supprimer</button>
                </form>
                <?php else:?>
                <span style="font-size:.8rem;color:#aaa;padding:6px;">compte actif</span>
                <?php endif;?>
            </div>
        </div>
        <?php endforeach;?>
    </div>
</div>

<!-- Modal ajouter responsable -->
<div class="modal-bg" id="modal-add">
    <div class="modal">
        <span class="close-modal" onclick="document.getElementById('modal-add').classList.remove('active')">✕</span>
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
            <button type="submit" class="btn btn-primary" style="width:100%;padding:12px;font-size:1.1rem;">Créer le compte</button>
        </form>
    </div>
</div>

<!-- Modal changer mot de passe -->
<div class="modal-bg" id="modal-mdp">
    <div class="modal">
        <span class="close-modal" onclick="document.getElementById('modal-mdp').classList.remove('active')">✕</span>
        <h3>Changer le mot de passe</h3>
        <p id="mdp-label" style="color:#888;margin-bottom:15px;"></p>
        <form method="POST">
            <input type="hidden" name="action"    value="changer_mdp">
            <input type="hidden" name="login_mdp" id="mdp-login">
            <div class="form-group">
                <label>Nouveau mot de passe</label>
                <input type="password" name="new_mdp" placeholder="Minimum 6 caractères" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;padding:12px;font-size:1.1rem;">Enregistrer</button>
        </form>
    </div>
</div>

<script>
function openMdp(login, nom) {
    document.getElementById('mdp-login').value = login;
    document.getElementById('mdp-label').textContent = 'Modifier le mot de passe de : ' + nom;
    document.getElementById('modal-mdp').classList.add('active');
}
// Fermer modals en cliquant dehors
document.querySelectorAll('.modal-bg').forEach(m => {
    m.addEventListener('click', function(e){ if(e.target===this) this.classList.remove('active'); });
});
</script>
</body>
</html>
