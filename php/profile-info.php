<?php
require_once __DIR__ . '/config.php';
// requireAdmin();

$db          = getDB();
$message     = 'titi'; // . $_POST['action'];
$messageType = 'toto';

function verifier_login_existant(PDO $db, string $nouv_login): bool {
    $stmt = $db->prepare("SELECT login FROM Famille WHERE login = ?");
    $stmt->execute([$nouv_login]);

    if ($stmt->fetch()) {
        return FALSE;
    }
    return TRUE;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


$action = $_POST['action'] ?? '';

if ($action === 'changement_profile') {
    $nouveau_nom = $_POST['nouveau_nom'] ?? '';
    $nouveau_login  = $_POST['nouveau_login'];
    $nouveau_mdp  = $_POST['nouveau_mdp'];
    $nouveau_mdp2  = $_POST['nouveau_mdp2'];
    //$message = 'success ' . $nouveau_nom . ' ' . $nouveau_login . ' ' . $nouveau_mdp . ' | ' . $_SESSION['user'];
    //$message     = 'momo' . ' x '. $action . ' y ' . $nouveau_nom;
    //$messageType = 'success';

    if($nouveau_mdp != $nouveau_mdp2) {
        $messageType = 'error';
        $message = "Error: Les mots de passes ne correspondent pas";
    }
    elseif ((strlen($nouveau_mdp) > 0) && strlen($nouveau_mdp) < 6) {
        $message = 'Mot de passe trop court.';
        $messageType = 'error';
    }
    elseif ((strlen($nouveau_mdp) > 0) && strlen($nouveau_mdp) < 6) {
        $message = 'Mot de passe trop court.';
        $messageType = 'error';
    }
    elseif (strlen($nouveau_login) > 0 && !filter_var($nouveau_login, FILTER_VALIDATE_EMAIL)) {
        $message = "L'adresse email n'est pas valide.";
        $messageType = 'error';
    }
    elseif (($nouveau_login !== $_SESSION['user']) && !verifier_login_existant($db, $nouveau_login)) {
        $message = "Cet email est déjà utilisé.";
        $messageType = 'error';
    }
    else {
        if(strlen($nouveau_nom) > 0) {
            $stmt = $db->prepare("UPDATE famille SET nom = :nouveau_nom WHERE login = :ancien_login");
            $stmt->execute([
                ':nouveau_nom' => $nouveau_nom,
                ':ancien_login'  => $_SESSION['user']
            ]);
            $_SESSION['nom'] = $nouveau_nom;
        }
        if(strlen($nouveau_mdp) > 0) {
            $stmt = $db->prepare("UPDATE famille SET mdp = :nouveau_mdp WHERE login = :ancien_login");
            $hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
            $stmt->execute([
                ':nouveau_mdp' => $hash,
                ':ancien_login'  => $_SESSION['user']
            ]);
        }
        if ((strlen($nouveau_login) > 0) && ($nouveau_login !== $_SESSION['user'])) {
            $stmt = $db->prepare("UPDATE famille SET login = :nouveau_login WHERE login = :ancien_login");
            $stmt->execute([
                ':nouveau_login' => $nouveau_login,
                ':ancien_login'  => $_SESSION['user']
            ]);
            $_SESSION['user'] = $nouveau_login;
        }
        $message = 'Le profile a été modifié.';
        $messageType = 'success';
    }

    $_SESSION['message'] = $message;
    $_SESSION['messageType'] = $messageType;
    header("Location: ../profile.php");
}
}
