<?php
require_once __DIR__ . '/config.php';
requireParent();

$db = getDB();
$login = $_SESSION['user'];

$error = '';

$success = '';

// Récupérer les infos actuelles de la famille
$stmt = $db->prepare("SELECT * FROM Famille WHERE login = ?");
$stmt->execute([$login]);
$famille = $stmt->fetch();

if (!$famille) {
    header("Location: parent-enfants.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $nouvelEmail  = trim(strtolower($_POST['nouvel_email'] ?? ''));
    $mdpActuel = $_POST['mdp_actuel'] ?? '';
    $nouveauMdp = $_POST['nouveau_mdp'] ?? '';
    $confirmerMdp = $_POST['confirmer_mdp'] ?? '';
    if (!$nom) {
        $error = "Le nom de famille est obligatoire.";
    } elseif (!password_verify($mdpActuel, $famille['mdp'])) {
        $error = "Mot de passe actuel incorrect.";
    } else {
        $emailChange = false;
        if ($nouvelEmail && $nouvelEmail !== strtolower($famille['login'])) {
            if (!filter_var($nouvelEmail, FILTER_VALIDATE_EMAIL)) {
                $error = "L'adresse email n'est pas valide.";
                goto fin_traitement;
            }
            // Vérifier disponibilité (exclure le compte actuel via id)
            $chk = $db->prepare("SELECT id FROM Famille WHERE login = ? AND id != ?");
            $chk->execute([$nouvelEmail, $famille['id']]);
            if ($chk->fetch()) {
                $error = "Cet email est déjà utilisé par un autre compte.";
                goto fin_traitement;
            }
            $emailChange = true;
        }

        if ($nouveauMdp !== '') {
            if (strlen($nouveauMdp) < 6) {
                $error = "Le nouveau mot de passe doit faire au moins 6 caractères.";
                goto fin_traitement;
            } elseif ($nouveauMdp !== $confirmerMdp) {
                $error = "Les deux nouveaux mots de passe ne correspondent pas.";
                goto fin_traitement;
            }
            $hash = password_hash($nouveauMdp, PASSWORD_DEFAULT);
        } else {
            $hash = $famille['mdp'];
        }

        if ($emailChange) {
            $db->prepare("UPDATE Famille SET login = ?, nom = ?, mdp = ? WHERE id = ?")
               ->execute([$nouvelEmail, $nom, $hash, $famille['id']]);
            $_SESSION['user'] = $nouvelEmail;
            $_SESSION['nom']  = $nom;
            $login = $nouvelEmail;
            $success = "Compte mis à jour avec succès (email, nom" . ($nouveauMdp ? " et mot de passe" : "") . ").";
        } else {
            $db->prepare("UPDATE Famille SET nom = ?, mdp = ? WHERE id = ?")
               ->execute([$nom, $hash, $famille['id']]);
            $_SESSION['nom'] = $nom;
            $success = "Compte mis à jour avec succès.";
        }

        // Recharger
        $stmt->execute([$login]);
        $famille = $stmt->fetch();
    }
    fin_traitement:
}