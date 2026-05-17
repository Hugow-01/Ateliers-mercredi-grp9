<?php
/**
 * header.php — En-tête commun à toutes les pages de l'application
 */
$pageTitle  = $pageTitle  ?? 'Ateliers du Mercredi';
$activePage = $activePage ?? '';

function navLink(string $href, string $label, string $page, string $activePage): string {
    $isActive = ($page === $activePage);
    $style    = $isActive ? 'style="text-decoration:underline; font-weight:900;"' : '';
    return '<a href="' . $href . '" ' . $style . '>' . $label . '</a>';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="icon" type="image/png" href="images/logo.png">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <?php if (!empty($extraCSS)): ?>
        <?php foreach ((array)$extraCSS as $cssFile): ?>
        <link rel="stylesheet" href="css/<?= htmlspecialchars($cssFile) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>

<header class="admin-header">
    <h1>
        <a href="index.php" style="color:inherit; text-decoration:none;">
            Ateliers du Mercredi
        </a>
    </h1>
    <nav>
        <?= navLink('index.php',     'Accueil',       'accueil',   $activePage) ?>
        <?= navLink('activites.php', 'Nos activités', 'activites', $activePage) ?>
        <?php if (isLoggedIn()): ?>
            <?php if (isAdmin()): ?>
                <?= navLink('admin-dashboard.php', 'Tableau de bord', 'dashboard', $activePage) ?>
            <?php else: ?>
                <?= navLink('parent-enfants.php',        'Mon espace', 'espace', $activePage) ?>
                <?= navLink('modifier-compte-parent.php', 'Mon compte', 'compte', $activePage) ?>
            <?php endif; ?>
            <a href="deconnexion.php" style="color:#c0392b; font-weight:bold; margin-left:20px;">
                Se déconnecter
            </a>
        <?php else: ?>
            <?= navLink('connexion.php',   'Se connecter', 'connexion',  $activePage) ?>
            <?= navLink('inscription.php', "S'inscrire",   'inscription', $activePage) ?>
        <?php endif; ?>
    </nav>
</header>