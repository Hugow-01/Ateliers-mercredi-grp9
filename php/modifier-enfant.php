<?php
require_once __DIR__ . '/config.php';
requireParent();

$db    = getDB();
$login = $_SESSION['user'];
$error   = '';
$success = '';

// Récupérer l'ID de l'enfant depuis GET ou POST
$id = intval($_GET['id'] ?? $_POST['id'] ?? 0);

if (!$id) {
    header("Location: parent-enfants.php");
    exit;
}

// Vérifier que l'enfant appartient bien à cette famille
$stmt = $db->prepare("SELECT * FROM Enfant WHERE id = ? AND login_famille = ?");
$stmt->execute([$id, $login]);
$enfant = $stmt->fetch();

if (!$enfant) {
    header("Location: parent-enfants.php");
    exit;
}

// ── TRAITEMENT DU FORMULAIRE ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom    = trim($_POST['nom']    ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $age    = intval($_POST['age']  ?? 0);

    if (!$nom || !$prenom || $age < 1 || $age > 17) {
        $error = "Veuillez remplir tous les champs correctement (âge entre 1 et 17 ans).";
    } else {
        try {
            $db->prepare("UPDATE Enfant SET nom = ?, prenom = ?, age = ? WHERE id = ? AND login_famille = ?")
               ->execute([$nom, $prenom, $age, $id, $login]);
            // Rafraîchir les données de l'enfant
            $stmt->execute([$id, $login]);
            $enfant = $stmt->fetch();
            $success = "Les informations ont bien été mises à jour.";
        } catch (PDOException $e) {
            $error = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
}
