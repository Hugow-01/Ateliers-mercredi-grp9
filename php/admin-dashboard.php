<?php
require_once __DIR__ . '/config.php';
requireAdmin();

$db = getDB();

$nbActivites = $db->query("SELECT COUNT(*) FROM Activite")->fetchColumn();
$nbEnfants = $db->query("SELECT COUNT(*) FROM Enfant")->fetchColumn();
$nbFamilles = $db->query("SELECT COUNT(*) FROM Famille")->fetchColumn();
$nbCreneaux = $db->query("SELECT COUNT(*) FROM Creneau")->fetchColumn();

$recents = $db->query("
    SELECT e.nom, e.prenom, a.nom AS activite, c.date, c.debut
    FROM Enfant_Creneau ec
    JOIN Enfant e ON e.id = ec.id_enfant
    JOIN Creneau c ON c.id = ec.id_creneau
    JOIN Activite a ON a.nom = c.nom_activite
    ORDER BY c.date DESC
    LIMIT 10
")->fetchAll();
