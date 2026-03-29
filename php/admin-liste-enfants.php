<?php
require_once __DIR__ . '/config.php';
requireAdmin();

$db = getDB();

$filtreActivite = $_GET['activite'] ?? '';
$filtreDate     = $_GET['date']     ?? '';

$where  = "WHERE 1=1";
$params = [];
if ($filtreActivite) { $where .= " AND a.nom LIKE ?"; $params[] = "%$filtreActivite%"; }
if ($filtreDate)     { $where .= " AND c.date = ?";   $params[] = $filtreDate; }

$stmt = $db->prepare("
    SELECT e.nom, e.prenom, e.age, e.login_famille,
           a.nom AS activite, a.capacite,
           c.date, c.debut, c.fin, c.id AS id_creneau
    FROM Enfant_Creneau ec
    JOIN Enfant e    ON e.id = ec.id_enfant
    JOIN Creneau c   ON c.id = ec.id_creneau
    JOIN Activité a  ON a.nom = c.nom_activite
    $where
    ORDER BY a.nom, c.date, c.debut, e.nom
");
$stmt->execute($params);
$inscriptions = $stmt->fetchAll();

$activites = $db->query("SELECT nom FROM Activité ORDER BY nom")->fetchAll();

// Rang de chaque enfant dans son créneau (pour le statut)
$enfantRang = [];
