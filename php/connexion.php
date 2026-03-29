<?php
require_once __DIR__ . '/config.php';

// Redirection si déjà connecté
if (isLoggedIn()) {
    header("Location: " . (isAdmin() ? 'admin-dashboard.php' : 'parent-enfants.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $mdp   = $_POST['mdp']   ?? '';
    $role  = $_POST['role']  ?? 'famille';

    if (!$login || !$mdp) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        try {
            $db    = getDB();
            $table = ($role === 'responsable') ? 'Responsable' : 'Famille';
            $stmt  = $db->prepare("SELECT * FROM `$table` WHERE login = ?");
            $stmt->execute([$login]);
            $user  = $stmt->fetch();

            if ($user && password_verify($mdp, $user['mdp'])) {
                $_SESSION['user'] = $user['login'];
                $_SESSION['nom']  = $user['nom'];
                $_SESSION['role'] = $role;
                header("Location: " . ($role === 'responsable' ? 'admin-dashboard.php' : 'parent-enfants.php'));
                exit;
            } else {
                $error = "Identifiant ou mot de passe incorrect.";
            }
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
