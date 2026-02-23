<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Les Ateliers du Mercredi</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .hero-bg {
            background: url('atel.jpg') center/cover no-repeat;
            position: relative;
            text-align: center;
            padding: 100px 20px;
            border-radius: 15px;
            margin: 25px 0 40px;
            overflow: hidden;
        }
        .hero-bg::before { content:""; position:absolute; inset:0; background:rgba(0,0,0,0.3); z-index:1; }
        .hero-content { position:relative; z-index:2; color:white; }
        .hero-content h1 { font-family:'Baloo 2'; font-size:3rem; text-shadow:2px 2px 4px rgba(0,0,0,0.5); }
        .hero-actions { margin-top:30px; display:flex; justify-content:center; gap:20px; flex-wrap:wrap; }
        .pillars { display:flex; justify-content:space-around; text-align:center; margin:50px 0; gap:20px; flex-wrap:wrap; }
        .pillar h3 { font-family:'Baloo 2'; font-size:1.4rem; margin-bottom:8px; }
        .pillar hr { width:50px; border:2px solid #333; margin:8px auto 12px; }
        .pillar p { color:#555; font-size:0.9rem; max-width:220px; margin:0 auto; }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="logo">Ateliers du Mercredi</a>
    <nav>
        <a href="index.php">Accueil</a>
        <a href="activites.php">Nos activités</a>
        <?php if (isLoggedIn()): ?>
            <?php if (isAdmin()): ?>
                <a href="admin-dashboard.php">Tableau de bord</a>
            <?php else: ?>
                <a href="parent-enfants.php">Mon espace</a>
            <?php endif; ?>
            <a href="deconnexion.php" style="color:#c0392b;">Se déconnecter</a>
        <?php else: ?>
            <a href="connexion.php">Se connecter</a>
            <a href="inscription.php">S'inscrire</a>
        <?php endif; ?>
    </nav>
</header>

<div class="container">
    <div class="hero-bg">
        <div class="hero-content">
            <h1>Découvrez Les ateliers du mercredi</h1>
            <p style="font-size:1.2rem; margin-top:10px; opacity:0.9;">Des activités créatives et éducatives pour vos enfants</p>
            <div class="hero-actions">
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a href="admin-dashboard.php" class="btn" style="background:#8c9eff; color:white; padding:13px 35px; font-size:1.1rem; border-radius:25px;">Mon tableau de bord</a>
                    <?php else: ?>
                        <a href="parent-enfants.php" class="btn" style="background:#8c9eff; color:white; padding:13px 35px; font-size:1.1rem; border-radius:25px;">Mon espace parent</a>
                        <a href="activites.php" class="btn btn-primary" style="padding:13px 35px; font-size:1.1rem; border-radius:25px;">Voir les activités</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="connexion.php" class="btn" style="background:#8c9eff; color:white; padding:13px 35px; font-size:1.1rem; border-radius:25px;">Se connecter</a>
                    <a href="inscription.php" class="btn btn-primary" style="padding:13px 35px; font-size:1.1rem; border-radius:25px;">S'inscrire</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="pillars">
        <div class="pillar">
            <h3>Chaleureux</h3>
            <hr>
            <p>Le mercredi devient un moment de plaisir, de découverte et d'apprentissage.</p>
        </div>
        <div class="pillar">
            <h3>Créatif</h3>
            <hr>
            <p>Créer, explorer, s'exprimer... chaque mercredi est une nouvelle aventure !</p>
        </div>
        <div class="pillar">
            <h3>Confiance</h3>
            <hr>
            <p>Des ateliers encadrés par des professionnels, dans un environnement sécurisé.</p>
        </div>
    </div>

    <div style="display:flex; align-items:center; gap:40px; margin:60px 0; flex-wrap:wrap;">
        <div style="flex:1; min-width:280px;">
            <img src="acc.jpg" alt="Atelier" style="width:100%; border-radius:20px; box-shadow:0 5px 15px rgba(0,0,0,0.1);" onerror="this.style.display='none'">
        </div>
        <div style="flex:1; min-width:280px;">
            <h2 style="font-family:'Baloo 2'; font-size:1.8rem; margin-bottom:15px;">À propos des ateliers</h2>
            <p style="line-height:1.8; color:#333; margin-bottom:15px;">
                Les Ateliers du Mercredi proposent aux enfants des activités variées, éducatives et ludiques, adaptées à leur âge, dans un cadre sécurisé et bienveillant.
            </p>
            <p style="line-height:1.8; color:#333;">
                Chaque mercredi, les enfants découvrent, apprennent et s'épanouissent tout en prenant plaisir à participer. Les parents disposent d'un espace dédié pour inscrire leurs enfants, choisir les ateliers et les créneaux disponibles en toute simplicité.
            </p>
        </div>
    </div>
</div>

<footer style="background:#54c7ec; color:#000; padding:40px 20px; margin-top:50px;">
    <div style="max-width:1200px; margin:0 auto; display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:30px;">
        <div style="flex:1; min-width:250px;">
            <h3 style="margin-top:0;">Nous trouver :</h3>
            <div style="border-radius:15px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2890.024790716508!2d1.380837957223259!3d43.58519995843656!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x12aeba66a700f077%3A0xe93c8355af0f357c!2s5%20Rue%20Jean%20Cocteau%2C%2031100%20Toulouse!5e0!3m2!1sfr!2sfr!4v1769439204384!5m2!1sfr!2sfr" width="100%" height="220" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
        <div style="flex:1; min-width:250px;">
            <h3 style="margin-top:0;">Nous contacter :</h3>
            <p><strong>Tél :</strong> 05 32 14 67 90</p>
            <p><strong>Email :</strong> j.rostaing@ateliers-mercredi.com</p>
            <p><strong>Adresse :</strong> 5 avenue Jean-Cocteau, 31400 Toulouse</p>
            <p><strong>Réseaux sociaux :</strong> @ateliers.mercredi</p>
        </div>
    </div>
</footer>

</body>
</html>
