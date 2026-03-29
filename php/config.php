<?php
// ── Configuration base de données ──────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');    // À modifier selon votre config
define('DB_PASS', '');        // À modifier selon votre config
define('DB_NAME', 'info9');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
    return $pdo;
}

// ── Session ─────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Helpers d'authentification ──────────────────────────────
function isLoggedIn(): bool {
    return isset($_SESSION['user']);
}

function isParent(): bool {
    return isLoggedIn() && $_SESSION['role'] === 'famille';
}

function isAdmin(): bool {
    return isLoggedIn() && $_SESSION['role'] === 'responsable';
}

function requireLogin(string $redirect = 'connexion.php'): void {
    if (!isLoggedIn()) {
        header("Location: $redirect");
        exit;
    }
}

function requireParent(): void {
    requireLogin();
    if (!isParent()) {
        header("Location: connexion.php");
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        header("Location: connexion.php");
        exit;
    }
}

// ── Helpers créneaux ────────────────────────────────────────
function nbInscrits(PDO $db, int $id_creneau): int {
    $s = $db->prepare("SELECT COUNT(*) FROM Enfant_Creneau WHERE id_creneau = ?");
    $s->execute([$id_creneau]);
    return (int) $s->fetchColumn();
}

function capaciteCreneau(PDO $db, int $id_creneau): int {
    $s = $db->prepare(
        "SELECT a.capacite FROM Creneau c
         JOIN Activité a ON a.nom = c.nom_activite
         WHERE c.id = ?"
    );
    $s->execute([$id_creneau]);
    return (int) $s->fetchColumn();
}

function prochainePosition(PDO $db, int $id_creneau): int {
    $s = $db->prepare("SELECT COALESCE(MAX(position), 0) + 1 FROM ListeAttente WHERE id_creneau = ?");
    $s->execute([$id_creneau]);
    return (int) $s->fetchColumn();
}

function creneauxAlternatifs(PDO $db, int $id_creneau_plein): array {
    $s = $db->prepare(
        "SELECT c.id, c.date, c.debut, c.fin, c.id_salle,
                COUNT(ec.id_enfant) AS nb_ins, a.capacite
         FROM Creneau c
         JOIN Activité a ON a.nom = c.nom_activite
         LEFT JOIN Enfant_Creneau ec ON ec.id_creneau = c.id
         WHERE c.nom_activite = (SELECT nom_activite FROM Creneau WHERE id = ?)
           AND c.id <> ?
         GROUP BY c.id
         HAVING nb_ins < a.capacite
         ORDER BY ABS(DATEDIFF(c.date, (SELECT date FROM Creneau WHERE id = ?))), c.date
         LIMIT 3"
    );
    $s->execute([$id_creneau_plein, $id_creneau_plein, $id_creneau_plein]);
    return $s->fetchAll();
}
