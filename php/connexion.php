<?php
require_once __DIR__ . '/config.php';

// Si deja connecte, on redirige direct
if (isLoggedIn()) {
    header("Location: " . (isAdmin() ? 'admin-dashboard.php' : 'parent-enfants.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $mdp   = $_POST['mdp'] ?? '';
    $role  = $_POST['role'] ?? 'famille';

    if (!$login || !$mdp) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        try {
            $db = getDB();

            $table = ($role === 'responsable') ? 'Responsable' : 'Famille';

            $stmt = $db->prepare("SELECT * FROM `$table` WHERE login = ?");
            $stmt->execute([$login]);
            $user = $stmt->fetch();

            if ($user && password_verify($mdp, $user['mdp'])) {
                $_SESSION['user'] = $user['login'];
                $_SESSION['nom']  = $user['nom'];

                if ($role === 'responsable') {
                    // Compte admin
                    $_SESSION['type_compte'] = 'responsable';
                    $_SESSION['role']        = 'responsable'; // pour isAdmin()
                    $_SESSION['admin_role']  = $user['role']; // super_admin ou admin
                    header("Location: admin-dashboard.php");
                } else {
                    // Compte famille - isParent() cherche $_SESSION['role'] === 'famille'
                    $_SESSION['role']        = 'famille';
                    $_SESSION['type_compte'] = 'famille';
                    header("Location: parent-enfants.php");
                }
                exit;

            } else {
                $error = "Identifiant ou mot de passe incorrect.";
            }

        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}