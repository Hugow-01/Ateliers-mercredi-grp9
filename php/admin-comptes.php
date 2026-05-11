<?php
require_once __DIR__ . '/config.php';
require_once 'mail.php';
requireAdmin();

$db = getDB();
$message = '';
$messageType = '';

// ── ACTIONS SUR LES FAMILLES ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Créer un compte parent
    if ($action === 'creer_famille') {
        $nom = trim($_POST['nom']   ?? '');
        $login = trim($_POST['login'] ?? '');
        $mdp = $_POST['mdp']  ?? '';
        $mdp2  = $_POST['mdp2'] ?? '';

        if (!$nom || !$login || !$mdp) {
            $message = 'Tous les champs sont obligatoires.';
            $messageType = 'error';
        } elseif (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $message = 'Email invalide.';
            $messageType = 'error';
        } elseif ($mdp !== $mdp2) {
            $message = 'Les mots de passe ne correspondent pas.';
            $messageType = 'error';
        } elseif (strlen($mdp) < 6) {
            $message = 'Mot de passe trop court (min. 6 caractères).';
            $messageType = 'error';
        } else {
            $chk = $db->prepare("SELECT id FROM Famille WHERE login = ?");
            $chk->execute([$login]);
            if ($chk->fetch()) {
                $message = 'Cet email est déjà utilisé.';
                $messageType = 'error';
            } else {
                $hash = password_hash($mdp, PASSWORD_DEFAULT);
                $db->prepare("INSERT INTO Famille (login, mdp, nom) VALUES (?, ?, ?)")
                   ->execute([$login, $hash, $nom]);
                $message = "Compte famille \"$nom\" créé.";
                $messageType = 'success';
            }
        }
    }

    // Modifier un compte parent
    if ($action === 'modifier_famille') {
        $idFam = intval($_POST['id_famille'] ?? 0);
        $nom = trim($_POST['nom']   ?? '');
        $login = trim($_POST['login'] ?? '');
        $newMdp = $_POST['new_mdp'] ?? '';

        if (!$idFam || !$nom || !$login) {
            $message = 'Données invalides.';
            $messageType = 'error';
        } elseif (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $message = 'Email invalide.';
            $messageType = 'error';
        } else {
            // Vérifier unicité email (exclure soi-même)
            $chk = $db->prepare("SELECT id FROM Famille WHERE login = ? AND id != ?");
            $chk->execute([$login, $idFam]);
            if ($chk->fetch()) {
                $message = 'Cet email est déjà utilisé par un autre compte.';
                $messageType = 'error';
            } else {
                if ($newMdp && strlen($newMdp) >= 6) {
                    $hash = password_hash($newMdp, PASSWORD_DEFAULT);
                    $db->prepare("UPDATE Famille SET nom = ?, login = ?, mdp = ? WHERE id = ?")
                       ->execute([$nom, $login, $hash, $idFam]);
                } else {
                    $db->prepare("UPDATE Famille SET nom = ?, login = ? WHERE id = ?")
                       ->execute([$nom, $login, $idFam]);
                }
                $message = "Compte famille mis à jour.";
                $messageType = 'success';
            }
        }
    }

    // Supprimer un compte parent (et ses enfants)
    if ($action === 'supprimer_famille') {
        $idFam = intval($_POST['id_famille'] ?? 0);
        if ($idFam) {
            // Récupérer tous les enfants
            $enfants = $db->prepare("SELECT id FROM Enfant WHERE id_famille = ?");
            $enfants->execute([$idFam]);
            foreach ($enfants->fetchAll() as $enf) {
                $idEnf = (int)$enf['id'];
                // Promouvoir liste attente pour chaque créneau confirmé
                $crens = $db->prepare("SELECT id_creneau FROM Enfant_Creneau WHERE id_enfant = ?");
                $crens->execute([$idEnf]);
                foreach ($crens->fetchAll() as $cr) {
                    $idCr = (int)$cr['id_creneau'];
                    $db->prepare("DELETE FROM Enfant_Creneau WHERE id_enfant = ? AND id_creneau = ?")
                       ->execute([$idEnf, $idCr]);
                    $premier = $db->prepare("SELECT id_enfant FROM ListeAttente WHERE id_creneau = ? ORDER BY position ASC LIMIT 1");
                    $premier->execute([$idCr]);
                    $promo = $premier->fetchColumn();
                    if ($promo) {
                        $db->prepare("INSERT IGNORE INTO Enfant_Creneau (id_enfant, id_creneau) VALUES (?,?)")
                           ->execute([$promo, $idCr]);
                        $db->prepare("DELETE FROM ListeAttente WHERE id_enfant = ? AND id_creneau = ?")
                           ->execute([$promo, $idCr]);
                    }
                }
                $db->prepare("DELETE FROM ListeAttente WHERE id_enfant = ?")->execute([$idEnf]);
                $db->prepare("DELETE FROM Enfant WHERE id = ?")->execute([$idEnf]);
            }
            $db->prepare("DELETE FROM Notification WHERE id_famille = ?")->execute([$idFam]);
            $db->prepare("DELETE FROM Famille WHERE id = ?")->execute([$idFam]);
            $message = "Compte famille et tous ses enfants supprimés.";
            $messageType = 'success';
        }
    }

    // Créer un enfant pour une famille
    if ($action === 'creer_enfant') {
        $idFam  = intval($_POST['id_famille'] ?? 0);
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $age = intval($_POST['age']  ?? 0);
        if (!$idFam || !$nom || !$prenom || $age < 1 || $age > 17) {
            $message = 'Données invalides (âge entre 1 et 17).';
            $messageType = 'error';
        } else {
            $db->prepare("INSERT INTO Enfant (nom, prenom, age, id_famille) VALUES (?, ?, ?, ?)")
               ->execute([$nom, $prenom, $age, $idFam]);
            $message = "Enfant $prenom $nom ajouté.";
            $messageType = 'success';
        }
    }

    // Modifier un enfant
    if ($action === 'modifier_enfant') {
        $idEnf  = intval($_POST['id_enfant'] ?? 0);
        $nom = trim($_POST['nom']    ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $age = intval($_POST['age']  ?? 0);
        if (!$idEnf || !$nom || !$prenom || $age < 1 || $age > 17) {
            $message = 'Données invalides.';
            $messageType = 'error';
        } else {
            $db->prepare("UPDATE Enfant SET nom = ?, prenom = ?, age = ? WHERE id = ?")
               ->execute([$nom, $prenom, $age, $idEnf]);
            $message = "Enfant mis à jour.";
            $messageType = 'success';
        }
    }

    // Supprimer un enfant
    if ($action === 'supprimer_enfant') {
        $idEnf = intval($_POST['id_enfant'] ?? 0);
        if ($idEnf) {
            $crens = $db->prepare("SELECT id_creneau FROM Enfant_Creneau WHERE id_enfant = ?");
            $crens->execute([$idEnf]);
            foreach ($crens->fetchAll() as $cr) {
                $idCr = (int)$cr['id_creneau'];
                $db->prepare("DELETE FROM Enfant_Creneau WHERE id_enfant = ? AND id_creneau = ?")
                   ->execute([$idEnf, $idCr]);
                $premier = $db->prepare("SELECT id_enfant FROM ListeAttente WHERE id_creneau = ? ORDER BY position ASC LIMIT 1");
                $premier->execute([$idCr]);
                $promo = $premier->fetchColumn();
                if ($promo) {
                    $db->prepare("INSERT IGNORE INTO Enfant_Creneau (id_enfant, id_creneau) VALUES (?,?)")
                       ->execute([$promo, $idCr]);
                    $db->prepare("DELETE FROM ListeAttente WHERE id_enfant = ? AND id_creneau = ?")
                       ->execute([$promo, $idCr]);
                }
            }
            $db->prepare("DELETE FROM ListeAttente WHERE id_enfant = ?")->execute([$idEnf]);
            $db->prepare("DELETE FROM Enfant WHERE id = ?")->execute([$idEnf]);
            $message = "Enfant supprimé.";
            $messageType = 'success';
        }
    }
}

// Chargement des familles avec leurs enfants
$recherche = trim($_GET['q'] ?? '');
$whereF = "WHERE 1=1";
$paramsF = [];
if ($recherche) {
    $whereF .= " AND (f.nom LIKE ? OR f.login LIKE ?)";
    $paramsF[] = "%$recherche%";
    $paramsF[] = "%$recherche%";
}

$stmtF = $db->prepare("SELECT f.* FROM Famille f $whereF ORDER BY f.nom");
$stmtF->execute($paramsF);
$familles = $stmtF->fetchAll();

// Pour chaque famille, récupérer ses enfants
$famillesAvecEnfants = [];
foreach ($familles as $fam) {
    $stmtE = $db->prepare("SELECT * FROM Enfant WHERE id_famille = ? ORDER BY prenom");
    $stmtE->execute([$fam['id']]);
    $fam['enfants'] = $stmtE->fetchAll();
    $famillesAvecEnfants[] = $fam;
}