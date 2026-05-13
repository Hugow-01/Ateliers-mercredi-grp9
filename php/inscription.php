<?php
require_once __DIR__ . '/config.php';
$mailDisponible = file_exists(__DIR__ . '/mail.php');
if ($mailDisponible) {
    require_once __DIR__ . '/mail.php';
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']   ?? '');
    $login = trim($_POST['email'] ?? '');
    $mdp = $_POST['mdp']  ?? '';
    $mdp2 = $_POST['mdp2'] ?? '';

    if (!$nom || !$login || !$mdp || !$mdp2) {
        $error = "Veuillez remplir tous les champs.";
    } elseif (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } elseif (strlen($mdp) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($mdp !== $mdp2) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT login FROM Famille WHERE login = ?");
            $stmt->execute([$login]);
            if ($stmt->fetch()) {
                $error = "Cet email est déjà utilisé. <a href='connexion.php'>Se connecter</a>";
            } else {
                $hash = password_hash($mdp, PASSWORD_DEFAULT);
                $db->prepare("INSERT INTO Famille (login, mdp, nom) VALUES (?, ?, ?)")
                   ->execute([$login, $hash, $nom]);

                $_SESSION['user'] = $login;
                $_SESSION['nom'] = $nom;
                $_SESSION['role'] = 'famille';
                $_SESSION['type_compte'] = 'famille';

                // Récupérer l'id de la nouvelle famille
                $stmtId = $db->prepare("SELECT id FROM Famille WHERE login = ?");
                $stmtId->execute([$login]);
                $newFamille = $stmtId->fetch();

                // Envoyer un mail de bienvenue si le système mail est disponible
                if ($mailDisponible && $newFamille) {
                    try {
                        envoyerMailBienvenue($login, $nom);
                    } catch (Exception $e) {
                        // Échec mail non bloquant
                        error_log("Erreur envoi mail bienvenue: " . $e->getMessage());
                    }
                }

                header("Location: parent-enfants.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = "Erreur base de données : " . $e->getMessage();
        }
    }
}