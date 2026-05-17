<?php
/**
 * header-admin.php — En-tête commun à toutes les pages de l'espace administrateur
 *
 *   $pageTitle   : titre de l'onglet (défaut : "Administration")
 *   $activeAdminPage : identifiant de la page active pour surligner le bon lien
 *                      Valeurs : 'dashboard', 'activites', 'enfants', 'comptes',
 *                                'responsables', 'inscriptions'
 */

$pageTitle       = $pageTitle       ?? 'Administration - Ateliers du Mercredi';
$activeAdminPage = $activeAdminPage ?? '';

// Utilitaire de lien actif
function adminNavLink(string $href, string $label, string $page, string $active): string {
    $style = ($page === $active) ? 'style="text-decoration:underline;"' : '';
    return '<a href="'.$href.'" '.$style.'>'.$label.'</a>';
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

<!-- ══════════════════════════════════════
     EN-TÊTE ADMIN — fond jaune, liens nav
     ══════════════════════════════════════ -->
<header class="admin-header">
    <!-- Titre / logo de l'espace admin -->
    <h1 style="font-family:'Baloo 2'; font-size:1.8rem; color:var(--text-dark); margin:0;">
        <a href="admin-dashboard.php"
           style="color:inherit; text-decoration:none;">Administration</a>
    </h1>

    <!-- Navigation admin complète -->
    <nav>
        <?= adminNavLink('index.php',                   'Accueil',          '',              $activeAdminPage) ?>
        <?= adminNavLink('admin-dashboard.php',         'Tableau de bord',  'dashboard',     $activeAdminPage) ?>
        <?= adminNavLink('admin-activites.php',         'Activités',        'activites',     $activeAdminPage) ?>
        <?= adminNavLink('admin-liste-enfants.php',     'Liste enfants',    'enfants',       $activeAdminPage) ?>
        <?= adminNavLink('admin-comptes.php',           'Comptes parents',  'comptes',       $activeAdminPage) ?>
        <?= adminNavLink('admin-responsables.php',      'Responsables',     'responsables',  $activeAdminPage) ?>
        <?= adminNavLink('admin-inscription-parents.php','Inscriptions',    'inscriptions',  $activeAdminPage) ?>
        <a href="deconnexion.php" style="color:#c0392b; font-weight:bold; margin-left:20px;">
            Déconnexion
        </a>
    </nav>
</header>
<!-- ══ Fin header admin ══ -->