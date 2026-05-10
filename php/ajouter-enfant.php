<?php
require_once __DIR__ . '/config.php';
requireParent();

$error   = '';
$success = '';
$db      = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom    = trim($_POST['nom']    ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $age    = intval($_POST['age']  ?? 0);

    if (!$nom || !$prenom || $age < 1) {
        $error = "Veuillez remplir tous les champs correctement.";
    } else {
        $idFamille = getIdFamille($db, $_SESSION['user']);
        try {
            $db->prepare("INSERT INTO Enfant (nom, prenom, age, id_famille) VALUES (?, ?, ?, ?)")
               ->execute([$nom, $prenom, $age, $idFamille]);
            header("Location: parent-enfants.php");
            exit;
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}