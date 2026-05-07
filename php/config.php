<?php
// ── Configuration base de données ──────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); 
define('DB_PASS', '');    
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

function isAdmin() {
    return isset($_SESSION['type_compte']) &&
           $_SESSION['type_compte'] === 'responsable';
}

function isSuperAdmin() {
    return isset($_SESSION['admin_role']) &&
           $_SESSION['admin_role'] === 'super_admin';
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


function creneauxRecommandes(PDO $db, int $id_creneau_plein, int $age_enfant = 0): array
{
    // ── Récupérer le créneau de référence ──────────────────────
    $ref = $db->prepare("
        SELECT c.id, c.date, c.debut, c.fin, c.nom_activite,
               a.capacite, a.theme, a.tranche_age
        FROM Creneau c
        JOIN Activité a ON a.nom = c.nom_activite
        WHERE c.id = ?
    ");
    $ref->execute([$id_creneau_plein]);
    $refRow = $ref->fetch();
    if (!$refRow) return [];

    $refDebut    = strtotime('1970-01-01 ' . $refRow['debut']);
    $refActivite = $refRow['nom_activite'];
    $refTheme    = strtolower(trim($refRow['theme'] ?? ''));

    // ── Candidats : tous créneaux non complets sauf celui-ci ───
    $stmt = $db->prepare("
        SELECT c.id, c.date, c.debut, c.fin, c.id_salle, c.nom_activite,
               a.capacite, a.theme, a.tranche_age, a.syllabus,
               COUNT(DISTINCT ec.id_enfant) AS nb_inscrits,
               COUNT(DISTINCT la.id_enfant) AS nb_attente
        FROM Creneau c
        JOIN Activité a ON a.nom = c.nom_activite
        LEFT JOIN Enfant_Creneau ec ON ec.id_creneau = c.id
        LEFT JOIN ListeAttente   la ON la.id_creneau  = c.id
        WHERE c.id <> ?
          AND c.date >= CURDATE()
        GROUP BY c.id
        HAVING nb_inscrits < a.capacite
        ORDER BY c.date ASC
    ");
    $stmt->execute([$id_creneau_plein]);
    $candidats = $stmt->fetchAll();

    // ── Scorer chaque candidat ──────────────────────────────────
    $scored = [];
    foreach ($candidats as $cand) {
        $score  = 0;
        $raisons = [];

        // Même activité, date différente → +40
        if ($cand['nom_activite'] === $refActivite) {
            $score += 40;
            $raisons[] = 'same_activity';
        }

        // Thème similaire → +25
        $candTheme = strtolower(trim($cand['theme'] ?? ''));
        if ($refTheme && $candTheme && $refTheme === $candTheme) {
            $score += 25;
            $raisons[] = 'same_theme';
        } elseif ($refTheme && $candTheme) {
            // Correspondance partielle par mots-clés extraits des noms
            $refWords  = preg_split('/\W+/', strtolower($refActivite));
            $candWords = preg_split('/\W+/', strtolower($cand['nom_activite']));
            $common    = array_intersect($refWords, $candWords);
            if (count($common) >= 2) {
                $score += 12;
                $raisons[] = 'similar_name';
            }
        }

        // Tranche d'âge → +20
        if ($age_enfant > 0 && !empty($cand['tranche_age'])) {
            // Format attendu : "5-8" ou "6-12"
            if (preg_match('/(\d+)-(\d+)/', $cand['tranche_age'], $m)) {
                if ($age_enfant >= (int)$m[1] && $age_enfant <= (int)$m[2]) {
                    $score += 20;
                    $raisons[] = 'age_match';
                }
            }
        }

        // Horaire proche (± 2h) → +10
        $candDebut = strtotime('1970-01-01 ' . $cand['debut']);
        if (abs($candDebut - $refDebut) <= 7200) {
            $score += 10;
            $raisons[] = 'close_time';
        }

        // Taux de remplissage faible (<50%) → +5 bonus
        $taux = $cand['capacite'] > 0 ? $cand['nb_inscrits'] / $cand['capacite'] : 1;
        if ($taux < 0.5) {
            $score += 5;
            $raisons[] = 'low_fill';
        }

        if ($score > 0) {
            $placesRestantes = $cand['capacite'] - $cand['nb_inscrits'];
            $scored[] = array_merge($cand, [
                'score'           => $score,
                'raisons'         => $raisons,
                'places_restantes'=> $placesRestantes,
                'taux_remplissage'=> round($taux * 100),
            ]);
        }
    }

    // Trier par score décroissant, puis par date
    usort($scored, function ($a, $b) {
        if ($b['score'] !== $a['score']) return $b['score'] - $a['score'];
        return strcmp($a['date'], $b['date']);
    });

    return array_slice($scored, 0, 6);
}

// Compatibilité descendante : l'ancienne fonction pointe vers le nouveau moteur
function creneauxAlternatifs(PDO $db, int $id_creneau_plein): array
{
    return creneauxRecommandes($db, $id_creneau_plein, 0);
}