<?php
require_once __DIR__ . '/config.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom   = trim($_POST['nom']   ?? '');
    $login = trim($_POST['email'] ?? '');
    $mdp   = $_POST['mdp']  ?? '';
    $mdp2  = $_POST['mdp2'] ?? '';

    if (!$nom || !$login || !$mdp || !$mdp2) {
        $error = "Veuillez remplir tous les champs.";
    } elseif (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } elseif (strlen($mdp) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caracteres.";
    } elseif ($mdp !== $mdp2) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        try {
            $db   = getDB();
            $stmt = $db->prepare("SELECT login FROM Famille WHERE login = ?");
            $stmt->execute([$login]);

            if ($stmt->fetch()) {
                $error = "Cet email est deja utilise. <a href='connexion.php'>Se connecter</a>";
            } else {
                $hash = password_hash($mdp, PASSWORD_DEFAULT);
                $db->prepare("INSERT INTO Famille (login, mdp, nom) VALUES (?, ?, ?)")
                   ->execute([$login, $hash, $nom]);

                $_SESSION['user']        = $login;
                $_SESSION['nom']         = $nom;
                // important : les deux cles pour que isParent() et isAdmin() fonctionnent
                $_SESSION['role']        = 'famille';
                $_SESSION['type_compte'] = 'famille';

                header("Location: parent-enfants.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = "Erreur base de donnees : " . $e->getMessage();
        }
    }
}