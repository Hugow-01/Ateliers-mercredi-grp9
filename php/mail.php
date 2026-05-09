<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

// URL de base du projet
define('BASE_URL', 'http://51.68.91.213/info9/Ateliers-mercredi-grp9');

function notifierFamille(
    PDO    $db,
    string $loginFamille,
    int    $idEnfant,
    int    $idCreneau,
    string $type,
    string $msgTexte
): void {
    // On insere la notif en base
    $db->prepare("
        INSERT INTO Notification (login_famille, id_enfant, id_creneau, type, message)
        VALUES (?, ?, ?, ?, ?)
    ")->execute([$loginFamille, $idEnfant, $idCreneau, $type, $msgTexte]);

    // Ensuite on envoie le mail
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ranjatinasoa@gmail.com';
        $mail->Password   = 'timkmklfgvegnwec';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('ranjatinasoa@gmail.com', 'Ateliers du Mercredi');
        $mail->addAddress($loginFamille);
        $mail->isHTML(true);

        // Sujet selon le type
        if ($type === 'accepte') {
            $sujet = 'Place confirmee - Ateliers du Mercredi';
        } elseif ($type === 'annulation') {
            $sujet = 'Activite annulee - Ateliers du Mercredi';
        } elseif ($type === 'modification') {
            $sujet = 'Modification d activite - Ateliers du Mercredi';
        } else {
            $sujet = 'Liste d attente - Ateliers du Mercredi';
        }

        $mail->Subject = $sujet;

        // Corps du mail en HTML simple
        $lien = BASE_URL . '/activites.php';
        $lienParent = BASE_URL . '/parent-enfants.php';

        $corps = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: #fdd835; padding: 20px; text-align: center;'>
                <h1 style='margin: 0; color: #3e2723; font-size: 1.5rem;'>Ateliers du Mercredi</h1>
            </div>
            <div style='padding: 30px; background: #fff;'>
                <p>Bonjour,</p>
                <p>" . nl2br(htmlspecialchars($msgTexte)) . "</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . $lien . "' style='background: #ff5e78; color: white; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: bold;'>
                        Voir les activites
                    </a>
                    &nbsp;&nbsp;
                    <a href='" . $lienParent . "' style='background: #1a1a2e; color: white; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: bold;'>
                        Mon espace parent
                    </a>
                </div>
                <p style='color: #888; font-size: 0.85rem;'>Cordialement,<br>Les Ateliers du Mercredi</p>
            </div>
            <div style='background: #f5f5f5; padding: 15px; text-align: center; font-size: 0.8rem; color: #888;'>
                Ateliers du Mercredi - 5 avenue Jean-Cocteau, 31400 Toulouse
            </div>
        </div>";

        $mail->Body    = $corps;
        $mail->AltBody = "Bonjour,\n\n" . $msgTexte . "\n\nVoir les activites : " . $lien . "\nMon espace : " . $lienParent . "\n\nCordialement,\nLes Ateliers du Mercredi";

        $mail->send();
    } catch (Exception $e) {
        error_log("Erreur envoi mail : " . $mail->ErrorInfo);
    }
}

// Envoyer un mail a tous les inscrits d'un creneau (annulation ou modif)
function notifierCreneauModifie(
    PDO    $db,
    int    $idCreneau,
    string $type, // 'annulation' ou 'modification'
    string $msgTexte
): void {
    // Recuperer tous les inscrits confirmes
    $stmt = $db->prepare("
        SELECT DISTINCT e.login_famille, e.id AS id_enfant
        FROM Enfant_Creneau ec
        JOIN Enfant e ON e.id = ec.id_enfant
        WHERE ec.id_creneau = ?
    ");
    $stmt->execute([$idCreneau]);
    $inscrits = $stmt->fetchAll();

    // Recuperer aussi les gens en liste d'attente
    $stmt2 = $db->prepare("
        SELECT DISTINCT e.login_famille, e.id AS id_enfant
        FROM ListeAttente la
        JOIN Enfant e ON e.id = la.id_enfant
        WHERE la.id_creneau = ?
    ");
    $stmt2->execute([$idCreneau]);
    $attente = $stmt2->fetchAll();

    $tous = array_merge($inscrits, $attente);

    foreach ($tous as $p) {
        notifierFamille($db, $p['login_famille'], $p['id_enfant'], $idCreneau, $type, $msgTexte);
    }
}
