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

function traiterMiseAJourCompte(PDO $db, array &$famille, array $post, string &$error, string &$success)
{
    $nom = trim($post['nom'] ?? '');
    $nouvelEmail = trim(strtolower($post['nouvel_email'] ?? ''));
    $mdpActuel = $post['mdp_actuel'] ?? '';
    $nouveauMdp = $post['nouveau_mdp'] ?? '';
    $confirmerMdp = $post['confirmer_mdp'] ?? '';

    // --- Validations de base ---
    if (!$nom) {
        $error = "Le nom de famille est obligatoire.";
        return;
    }

    if (!password_verify($mdpActuel, $famille['mdp'])) {
        $error = "Mot de passe actuel incorrect.";
        return;
    }

    // --- Validation email ---
    $emailChange = false;
    if ($nouvelEmail && $nouvelEmail !== strtolower($famille['login'])) {
        if (!filter_var($nouvelEmail, FILTER_VALIDATE_EMAIL)) {
            $error = "L'adresse email n'est pas valide.";
            return;
        }

        $chk = $db->prepare("SELECT id FROM Famille WHERE login = ? AND id != ?");
        $chk->execute([$nouvelEmail, $famille['id']]);
        if ($chk->fetch()) {
            $error = "Cet email est déjà utilisé par un autre compte.";
            return;
        }

        $emailChange = true;
    }

    // --- Validation nouveau mot de passe ---
    if ($nouveauMdp !== '') {
        if (strlen($nouveauMdp) < 6) {
            $error = "Le nouveau mot de passe doit faire au moins 6 caractères.";
            return;
        }
        if ($nouveauMdp !== $confirmerMdp) {
            $error = "Les deux nouveaux mots de passe ne correspondent pas.";
            return;
        }
        $hash = password_hash($nouveauMdp, PASSWORD_DEFAULT);
    } else {
        $hash = $famille['mdp'];
    }

    // --- Mise à jour en base ---
    if ($emailChange) {
        $db->prepare("UPDATE Famille SET login = ?, nom = ?, mdp = ? WHERE id = ?")
           ->execute([$nouvelEmail, $nom, $hash, $famille['id']]);
        $_SESSION['user'] = $nouvelEmail;
        $_SESSION['nom'] = $nom;
        $famille['login'] = $nouvelEmail;
        $success = "Compte mis à jour avec succès (email, nom" . ($nouveauMdp ? " et mot de passe" : "") . ").";
    } else {
        $db->prepare("UPDATE Famille SET nom = ?, mdp = ? WHERE id = ?")
           ->execute([$nom, $hash, $famille['id']]);
        $_SESSION['nom'] = $nom;
        $success = "Compte mis à jour avec succès.";
    }

    // --- Recharger les données fraîches ---
    $stmt = $db->prepare("SELECT * FROM Famille WHERE login = ?");
    $stmt->execute([$famille['login']]);
    $famille = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    traiterMiseAJourCompte($db, $famille, $_POST, $error, $success);
}