<?php
require_once __DIR__ . '/config.php';
requireParent();

$db    = getDB();
$login = $_SESSION['user'];
$error   = '';
$success = '';

// On recupere les infos actuelles de la famille
$stmt = $db->prepare("SELECT * FROM Famille WHERE login = ?");
$stmt->execute([$login]);
$famille = $stmt->fetch();

if (!$famille) {
    header("Location: parent-enfants.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom      = trim($_POST['nom']       ?? '');
    $mdpActuel = $_POST['mdp_actuel']    ?? '';
    $nouveauMdp = $_POST['nouveau_mdp']  ?? '';
    $confirmerMdp = $_POST['confirmer_mdp'] ?? '';

    if (!$nom) {
        $error = "Le nom de famille est obligatoire.";
    } elseif (!password_verify($mdpActuel, $famille['mdp'])) {
        $error = "Mot de passe actuel incorrect.";
    } else {
        // Si l'utilisateur veut changer son mdp
        if ($nouveauMdp !== '') {
            if (strlen($nouveauMdp) < 6) {
                $error = "Le nouveau mot de passe doit faire au moins 6 caracteres.";
            } elseif ($nouveauMdp !== $confirmerMdp) {
                $error = "Les deux nouveaux mots de passe ne correspondent pas.";
            } else {
                $hash = password_hash($nouveauMdp, PASSWORD_DEFAULT);
                $db->prepare("UPDATE Famille SET nom = ?, mdp = ? WHERE login = ?")
                   ->execute([$nom, $hash, $login]);
                $_SESSION['nom'] = $nom;
                $success = "Compte mis a jour avec succes (nom et mot de passe).";
                // Recharger les infos
                $stmt->execute([$login]);
                $famille = $stmt->fetch();
            }
        } else {
            // Juste le nom
            $db->prepare("UPDATE Famille SET nom = ? WHERE login = ?")
               ->execute([$nom, $login]);
            $_SESSION['nom'] = $nom;
            $success = "Nom mis a jour avec succes.";
            $stmt->execute([$login]);
            $famille = $stmt->fetch();
        }
    }
}
