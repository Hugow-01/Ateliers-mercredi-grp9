<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'info9');       // À modifier selon votre config
define('DB_PASS', 'A6u');           // À modifier selon votre config
define('DB_NAME', 'info9');

function getDB() {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    return $pdo;
}

// Démarrage de session sécurisé
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fonctions utilitaires
function isLoggedIn() {
    return isset($_SESSION['user']);
}

function isParent() {
    return isLoggedIn() && $_SESSION['role'] === 'famille';
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'responsable';
}

function requireLogin($redirect = 'connexion.php') {
    if (!isLoggedIn()) {
        header("Location: $redirect");
        exit;
    }
}

function requireParent() {
    requireLogin();
    if (!isParent()) {
        header("Location: connexion.php");
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: connexion.php");
        exit;
    }
}
?>
