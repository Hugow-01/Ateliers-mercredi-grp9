<?php
require_once __DIR__ . '/config.php';
requireAdmin();
$db = getDB();
$message  = '';
$messageType = '';

// ── AJOUTER (super admin uniquement) ────────────────────────
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['action'] ?? '') === 'ajouter'
    && isSuperAdmin()
) {
    $login = trim($_POST['login'] ?? '');
    $nom = trim($_POST['nom']   ?? '');
    $mdp = $_POST['mdp']  ?? '';
    $mdp2 = $_POST['mdp2'] ?? '';
    $role = in_array($_POST['role'] ?? '', ['admin','super_admin']) ? $_POST['role'] : 'admin';

    if (!$login || !$nom || !$mdp) {
        $message = 'Remplissez tous les champs.';
        $messageType = 'error';
    } elseif ($mdp !== $mdp2) {
        $message = 'Les mots de passe ne correspondent pas.';
        $messageType = 'error';
    } elseif (strlen($mdp) < 6) {
        $message = 'Mot de passe trop court (min. 6 caractères).';
        $messageType = 'error';
    } else {
        $chk = $db->prepare("SELECT login FROM Responsable WHERE login = ?");
        $chk->execute([$login]);
        if ($chk->fetch()) {
            $message = 'Ce login existe déjà.';
            $messageType = 'error';
        } else {
            $hash = password_hash($mdp, PASSWORD_DEFAULT);
            $db->prepare("INSERT INTO Responsable (login, mdp, nom, role) VALUES (?, ?, ?, ?)")
               ->execute([$login, $hash, $nom, $role]);
            $message = "Responsable \"$nom\" ajouté avec succès.";
            $messageType = 'success';
        }
    }
}

// ── SUPPRIMER (super admin uniquement) ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer' && isSuperAdmin()) {
    $loginSup = $_POST['login_sup'] ?? '';
    if ($loginSup === $_SESSION['user']) {
        $message = 'Vous ne pouvez pas supprimer votre propre compte.';
        $messageType = 'error';
    } elseif ($loginSup) {
        $db->prepare("DELETE FROM Responsable WHERE login = ?")->execute([$loginSup]);
        $message = "Responsable supprimé.";
        $messageType = 'success';
    }
}

// ── CHANGER MOT DE PASSE (super admin uniquement) ────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'changer_mdp' && isSuperAdmin()) {
    $loginMdp = $_POST['login_mdp'] ?? '';
    $newMdp   = $_POST['new_mdp']   ?? '';
    if (!$loginMdp || !$newMdp) {
        $message = 'Données manquantes.';
        $messageType = 'error';
    } elseif (strlen($newMdp) < 6) {
        $message = 'Mot de passe trop court.';
        $messageType = 'error';
    } else {
        $hash = password_hash($newMdp, PASSWORD_DEFAULT);
        $db->prepare("UPDATE Responsable SET mdp = ? WHERE login = ?")->execute([$hash, $loginMdp]);
        $message = "Mot de passe mis à jour.";
        $messageType = 'success';
    }
}

// ── MODIFIER RÔLE (super admin uniquement) ───────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'modifier_role' && isSuperAdmin()) {
    $loginRole = $_POST['login_role'] ?? '';
    $nouveauRole = in_array($_POST['nouveau_role'] ?? '', ['admin','super_admin']) ? $_POST['nouveau_role'] : 'admin';
    if ($loginRole === $_SESSION['user']) {
        $message = 'Vous ne pouvez pas modifier votre propre rôle.';
        $messageType = 'error';
    } elseif ($loginRole) {
        $db->prepare("UPDATE Responsable SET role = ? WHERE login = ?")->execute([$nouveauRole, $loginRole]);
        $message = "Rôle mis à jour.";
        $messageType = 'success';
    }
}

if (isSuperAdmin()) {
    $stmt = $db->query("SELECT login, nom, role FROM Responsable ORDER BY nom");
} else {
    $stmt = $db->prepare("SELECT login, nom, role FROM Responsable WHERE login = ?");
    $stmt->execute([$_SESSION['user']]);
}
$responsables = $stmt->fetchAll();